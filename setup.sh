#!/usr/bin/env bash

set -Eeuo pipefail

fail() {
    printf 'Setup stopped: %s\n' "$1" >&2
    exit 1
}

usage() {
    printf 'Usage: ./setup.sh [--sqlite]\n'
}

sqlite=false

case "${1:-}" in
    '')
        ;;
    --sqlite)
        sqlite=true
        ;;
    --help|-h)
        usage
        exit 0
        ;;
    *)
        usage >&2
        exit 1
        ;;
esac

if [[ $# -gt 1 ]]; then
    usage >&2
    exit 1
fi

for command_name in php composer npm; do
    if ! command -v "$command_name" >/dev/null 2>&1; then
        fail "$command_name is required but was not found. Install it and run this script again."
    fi
done

root_dir="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
cd "$root_dir"

[[ -f .env.example ]] || fail '.env.example is missing.'

if [[ ! -f .env ]]; then
    cp .env.example .env
    printf 'Created .env from .env.example.\n'
fi

env_value() {
    local key="$1"
    local value

    value="$(grep -E "^${key}=" .env | tail -n 1 | cut -d '=' -f 2- || true)"
    value="${value%$'\r'}"

    case "$value" in
        \"*\")
            value="${value:1:${#value}-2}"
            ;;
        \'*\')
            value="${value:1:${#value}-2}"
            ;;
    esac

    printf '%s' "$value"
}

set_env_value() {
    local key="$1"
    local value="$2"

    if grep -qE "^${key}=" .env; then
        sed -i "s|^${key}=.*|${key}=${value}|" .env
    else
        printf '\n%s=%s\n' "$key" "$value" >> .env
    fi
}

composer install

app_key="$(env_value APP_KEY)"
if [[ -z "$app_key" ]]; then
    php artisan key:generate --force
else
    printf 'APP_KEY is already set.\n'
fi

connection="$(env_value DB_CONNECTION)"

if [[ "$sqlite" == true || "$connection" == sqlite ]]; then
    set_env_value DB_CONNECTION sqlite
    set_env_value DB_DATABASE database/database.sqlite
    touch database/database.sqlite
    printf 'Using SQLite at database/database.sqlite.\n'
else
    db_host="$(env_value DB_HOST)"
    db_port="$(env_value DB_PORT)"
    db_name="$(env_value DB_DATABASE)"
    db_user="$(env_value DB_USERNAME)"
    db_password="$(env_value DB_PASSWORD)"

    db_host="${db_host:-127.0.0.1}"
    db_port="${db_port:-3306}"
    [[ -n "$db_name" ]] || fail 'DB_DATABASE is empty in .env.'
    [[ -n "$db_user" ]] || fail 'DB_USERNAME is empty in .env.'

    if ! DB_SETUP_HOST="$db_host" \
        DB_SETUP_PORT="$db_port" \
        DB_SETUP_DATABASE="$db_name" \
        DB_SETUP_USERNAME="$db_user" \
        DB_SETUP_PASSWORD="$db_password" \
        php -r '
            $database = getenv("DB_SETUP_DATABASE");

            if (!preg_match("/^[A-Za-z0-9_$-]+$/", $database)) {
                throw new RuntimeException("DB_DATABASE contains unsupported characters.");
            }

            try {
                $pdo = new PDO(
                    "mysql:host=".getenv("DB_SETUP_HOST").";port=".getenv("DB_SETUP_PORT"),
                    getenv("DB_SETUP_USERNAME"),
                    getenv("DB_SETUP_PASSWORD"),
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]
                );
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `".str_replace("`", "``", $database)."` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            } catch (Throwable $error) {
                fwrite(STDERR, $error->getMessage().PHP_EOL);
                exit(1);
            }
        '; then
        printf '%s\n' \
            'Could not connect to MySQL using the values in .env.' \
            'Fix the DB_* values in .env, or run ./setup.sh --sqlite to try without MySQL.' >&2
        exit 1
    fi

    printf 'MySQL database is ready.\n'
fi

php artisan migrate --seed
npm install
npm run build

printf '\nSetup complete. Start the app with:\n  php artisan serve\n'

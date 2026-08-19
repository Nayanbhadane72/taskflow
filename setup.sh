#!/usr/bin/env bash

set -e

sqlite=false
if [ "${1:-}" = "--sqlite" ]; then
    sqlite=true
elif [ -n "${1:-}" ]; then
    echo "Usage: ./setup.sh [--sqlite]"
    exit 1
fi

for command in php composer npm; do
    if ! command -v "$command" >/dev/null 2>&1; then
        echo "$command is required but was not found. Install it and try again."
        exit 1
    fi
done

cd "$(dirname "$0")"

if [ ! -f .env ]; then
    cp .env.example .env
    echo "Created .env from .env.example."
fi

composer install

if grep -q '^APP_KEY=.' .env; then
    echo "APP_KEY is already set."
else
    php artisan key:generate
fi

if [ "$sqlite" = true ] || grep -q '^DB_CONNECTION=sqlite' .env; then
    sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
    sed -i 's|^DB_DATABASE=.*|DB_DATABASE=database/database.sqlite|' .env
    touch database/database.sqlite
    echo "Using SQLite."
else
    db_host=$(grep '^DB_HOST=' .env | cut -d= -f2-)
    db_port=$(grep '^DB_PORT=' .env | cut -d= -f2-)
    db_name=$(grep '^DB_DATABASE=' .env | cut -d= -f2-)
    db_user=$(grep '^DB_USERNAME=' .env | cut -d= -f2-)
    db_password=$(grep '^DB_PASSWORD=' .env | cut -d= -f2-)

    if command -v mysql >/dev/null 2>&1; then
        if ! MYSQL_PWD="$db_password" mysql \
            -h "${db_host:-127.0.0.1}" \
            -P "${db_port:-3306}" \
            -u "$db_user" \
            -e "CREATE DATABASE IF NOT EXISTS \`$db_name\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"; then
            echo "Could not connect to MySQL using the values in .env."
            echo "Fix the DB_* values in .env, or run ./setup.sh --sqlite."
            exit 1
        fi
        echo "MySQL database is ready."
    else
        echo "mysql client was not found; the MySQL database must already exist."
    fi
fi

php artisan migrate --seed
npm install
npm run build

echo
echo "Setup complete. Start the app with:"
echo "  php artisan serve"

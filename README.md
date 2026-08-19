# Taskflow

Taskflow is a small Laravel task list. Tasks belong to an optional project and have a dense priority order within that project, including a separate order for unassigned tasks. The task page supports project filtering, inline edits, deletion, and native drag-and-drop reordering.

## Requirements

- PHP 8.3 or newer with `mbstring`, `xml`, `curl`, `zip`, `pdo_mysql`, `bcmath`, and `intl`
- Composer
- MySQL 8
- Node.js 20 and npm

## Quick start

```sh
git clone https://github.com/Nayanbhadane72/taskflow.git
cd taskflow
./setup.sh
php artisan serve
```

Open http://127.0.0.1:8000.

On Windows, run `setup.bat` in Command Prompt or PowerShell instead. Git Bash and WSL can use `./setup.sh`.

If MySQL is not available, run `setup.bat --sqlite` on Windows or `./setup.sh --sqlite` elsewhere. MySQL is the normal database for this app and the manual setup below shows how to configure it.

## Manual setup

Install the PHP dependencies and create the environment file:

```sh
composer install
cp .env.example .env
php artisan key:generate
```

Set the MySQL connection in `.env`. For a local install, create the database and user:

```sql
CREATE DATABASE taskflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'taskflow'@'localhost' IDENTIFIED BY 'taskflow';
GRANT ALL PRIVILEGES ON taskflow.* TO 'taskflow'@'localhost';
FLUSH PRIVILEGES;
```

Then run the migrations and seed demo data:

```sh
php artisan migrate --seed
npm install
npm run build
```

Start the application with:

```sh
php artisan serve
```

Open http://127.0.0.1:8000.

## Tests

The test suite uses an in-memory SQLite database:

```sh
php artisan test
./vendor/bin/pint --test
```

## Deployment

Use a production `.env` with `APP_ENV=production`, `APP_DEBUG=false`, a generated `APP_KEY`, and credentials for a dedicated MySQL user. Run `composer install --no-dev --optimize-autoloader`, migrate with `php artisan migrate --force`, and build assets with `npm install && npm run build`.

Cache configuration and routes after environment values are set:

```sh
php artisan config:cache
php artisan route:cache
```

Serve the `public` directory through Nginx or Apache and run PHP-FPM. If queues are added, run a queue worker under a process manager such as Supervisor or systemd and restart workers after deployments.

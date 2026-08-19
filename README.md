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

If MySQL is not available, run `./setup.sh --sqlite`. MySQL is the normal database for this app and the manual setup below shows how to configure it.

## Windows

Install PHP 8.3, Composer and Node.js so that `php`, `composer` and `npm` work in a new terminal. A bundle such as Laragon or XAMPP gives you PHP and MySQL together; if you use one, add its `php` folder to the PATH environment variable. Make sure MySQL is running, then in Command Prompt or PowerShell:

```bat
git clone https://github.com/Nayanbhadane72/taskflow.git
cd taskflow
setup.bat
php artisan serve
```

Open http://127.0.0.1:8000.

Use `setup.bat --sqlite` if you do not have MySQL. If the `mysql` command is not on the PATH, create the `taskflow` database yourself (phpMyAdmin or MySQL Workbench) and run `setup.bat` again. Git Bash and WSL can run `./setup.sh` instead, and the manual steps below work the same on Windows apart from using `copy .env.example .env` in place of `cp`.

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

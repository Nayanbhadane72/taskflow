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

You need PHP 8.3, Composer and Node.js available in the terminal. The quickest way to get all of them plus MySQL is Laragon (https://laragon.org/download/, the full build); its own terminal already has everything on the PATH. Otherwise install PHP from https://windows.php.net/download (the non thread safe x64 zip, extracted to `C:\php` and added to the PATH), Composer from https://getcomposer.org/Composer-Setup.exe and Node 20 from https://nodejs.org.

With MySQL running, in Command Prompt or PowerShell:

```bat
git clone https://github.com/Nayanbhadane72/taskflow.git
cd taskflow
setup.bat
php artisan serve
```

Open http://127.0.0.1:8000.

Use `setup.bat --sqlite` if you do not have MySQL.

### If something goes wrong

- `php` is not recognised: PHP is not on the PATH. Check `php -v` in a new terminal, and remember PATH changes only apply to terminals opened afterwards. With Laragon, use the Laragon terminal.
- `setup.bat` is not recognised in PowerShell: run it as `.\setup.bat`.
- `cd taskflow` fails: you are already in the project folder, so skip that line.
- A PHP extension is missing: in `C:\php\php.ini` remove the `;` in front of `extension=pdo_mysql`, `extension=mbstring`, `extension=curl`, `extension=zip` and `extension=intl`.
- `mysql` is not recognised: create an empty `taskflow` database with phpMyAdmin or MySQL Workbench and run `setup.bat` again, or use `setup.bat --sqlite`.
- `Access denied for user ... (using password: YES)`: the `DB_USERNAME` or `DB_PASSWORD` in `.env` does not match MySQL. With Laragon, use `root` and an empty password, run `php artisan config:clear`, and run setup again.

Git Bash and WSL can run `./setup.sh` instead, and the manual steps below work the same on Windows apart from using `copy .env.example .env` in place of `cp`.

## Manual setup

Install the PHP dependencies and create the environment file:

```sh
composer install
cp .env.example .env
php artisan key:generate
```

Set the MySQL connection in `.env`. The committed `.env.example` uses `root` with an empty password, which matches the usual Laragon and XAMPP setup. If you prefer a dedicated user, create the database and run the optional user setup:

```sql
CREATE DATABASE taskflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```sql
-- Optional dedicated-user setup
CREATE USER 'taskflow'@'localhost' IDENTIFIED BY 'taskflow';
GRANT ALL PRIVILEGES ON taskflow.* TO 'taskflow'@'localhost';
FLUSH PRIVILEGES;
```

Set `DB_USERNAME` and `DB_PASSWORD` in `.env` to match that user.

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

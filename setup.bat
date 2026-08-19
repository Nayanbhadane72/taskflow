@echo off
setlocal EnableExtensions

set "sqlite=false"
if "%~1"=="--sqlite" set "sqlite=true"
if not "%~1"=="" if not "%~1"=="--sqlite" (
    echo Usage: setup.bat [--sqlite]
    exit /b 1
)
if not "%~2"=="" (
    echo Usage: setup.bat [--sqlite]
    exit /b 1
)

for %%C in (php composer npm) do (
    where %%C >nul 2>&1
    if errorlevel 1 (
        echo %%C is required but was not found. Install it and try again.
        exit /b 1
    )
)

cd /d "%~dp0"
if not exist ".env" (
    copy /y ".env.example" ".env" >nul
    if errorlevel 1 exit /b 1
    echo Created .env from .env.example.
)

call composer install
if errorlevel 1 exit /b 1

call :read_env APP_KEY app_key
if defined app_key (
    echo APP_KEY is already set.
) else (
    php artisan key:generate
    if errorlevel 1 exit /b 1
)

call :read_env DB_CONNECTION connection
if "%sqlite%"=="true" goto sqlite
if /i "%connection%"=="sqlite" goto sqlite
goto mysql

:sqlite
call :set_env DB_CONNECTION sqlite
if errorlevel 1 exit /b 1
call :set_env DB_DATABASE database\database.sqlite
if errorlevel 1 exit /b 1
if not exist "database\database.sqlite" type nul > "database\database.sqlite"
echo Using SQLite.
goto migrate

:mysql
where mysql >nul 2>&1
if errorlevel 1 (
    echo mysql client was not found; the MySQL database must already exist.
    goto migrate
)

call :read_env DB_HOST db_host
call :read_env DB_PORT db_port
call :read_env DB_DATABASE db_name
call :read_env DB_USERNAME db_user
call :read_env DB_PASSWORD db_password
if not defined db_host set "db_host=127.0.0.1"
if not defined db_port set "db_port=3306"

set "MYSQL_PWD=%db_password%"
mysql -h "%db_host%" -P "%db_port%" -u "%db_user%" -e "CREATE DATABASE IF NOT EXISTS `%db_name%` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
if errorlevel 1 goto mysql_failed
set "MYSQL_PWD="
echo MySQL database is ready.
goto migrate

:mysql_failed
set "MYSQL_PWD="
echo Could not connect to MySQL using the values in .env.
echo Fix the DB_* values in .env, or run setup.bat --sqlite.
exit /b 1

:migrate
php artisan migrate --seed
if errorlevel 1 exit /b 1
call npm install
if errorlevel 1 exit /b 1
call npm run build
if errorlevel 1 exit /b 1

echo.
echo Setup complete. Start the app with:
echo   php artisan serve
exit /b 0

:read_env
set "%~2="
for /f "tokens=1,* delims==" %%A in ('findstr /b /c:"%~1=" .env') do if "%%A"=="%~1" set "%~2=%%B"
exit /b 0

:set_env
findstr /v /b /c:"%~1=" .env > ".env.tmp"
if errorlevel 2 exit /b 1
>>".env.tmp" echo %~1=%~2
move /y ".env.tmp" ".env" >nul
if errorlevel 1 exit /b 1
exit /b 0

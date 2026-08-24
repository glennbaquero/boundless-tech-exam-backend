# Boundless Tech Exam Backend

Laravel 13 API backend (PHP 8.3+) for booking data.

## Requirements

- PHP >= 8.3
- Composer
- SQLite (default) — or configure another driver in `.env`

## Setup

1. Install PHP dependencies:
   ```bash
   composer install
   ```
   
2. Copy the environment file and generate an app key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. Create the SQLite database file (default connection):
   ```bash
   touch database/database.sqlite
   ```
   > To use MySQL/Postgres instead, update `DB_*` values in `.env` and skip this step.

4. Run migrations:
   ```bash
   php artisan migrate
   ```

## Running the app

Start the Laravel dev server (with queue listener, logs, and Vite):
```bash
composer run dev
```

Or run the API server only:
```bash
php artisan serve
```

The API will be available at `http://localhost:8000`.

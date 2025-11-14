# vue-rest-api

## Local setup (without Docker)

```
npm i
composer install
php artisan serve
npm run dev
```

## Development with Docker

1. Ensure `.env` exists (copy from `.env.example` if necessary) and contains the database credentials defined in `docker-compose.yml`.
2. Build and start the stack:
   ```
   docker compose up --build
   ```
3. Laravel API is available at `http://localhost:8000` and the Vite dev server at `http://localhost:5173`.
4. To run artisan commands inside the container use `docker compose exec app php artisan <command>`; npm commands can be executed with `docker compose exec app npm <command>`.

The first startup installs Composer and npm dependencies inside the container volumes; subsequent runs reuse them automatically.

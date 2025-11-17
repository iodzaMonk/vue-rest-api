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

## Google reCAPTCHA (login/register)

1. Create a reCAPTCHA v2 Checkbox site and secret key at https://www.google.com/recaptcha/.
2. Add the keys to `.env`:
    ```
    RECAPTCHA_SITE_KEY=your-site-key
    RECAPTCHA_SECRET_KEY=your-secret-key
    VITE_RECAPTCHA_SITE_KEY="${RECAPTCHA_SITE_KEY}"
    RECAPTCHA_SCORE=0.5
    ```
3. Rebuild or restart the dev server after changing env vars. The login, register, and create-word forms render the visible checkbox and send the token with API calls.

#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

if [ ! -f vendor/autoload.php ]; then
    echo "Installing composer dependencies..."
    composer install
fi

if [ ! -d node_modules ] || [ -z "$(ls -A node_modules)" ]; then
    echo "Installing npm dependencies..."
    npm install
fi

export LARAVEL_PORT="${LARAVEL_PORT:-8000}"
export VITE_PORT="${VITE_PORT:-5173}"

php artisan serve --host=0.0.0.0 --port="${LARAVEL_PORT}" &
php_pid=$!

npm run dev -- --host 0.0.0.0 --port="${VITE_PORT}" --strictPort &
npm_pid=$!

cleanup() {
    echo "Stopping application processes..."
    kill -TERM "${php_pid}" "${npm_pid}" >/dev/null 2>&1 || true
    wait "${php_pid}" "${npm_pid}" 2>/dev/null || true
}

trap cleanup EXIT

wait -n
exit_code=$?
exit "${exit_code}"

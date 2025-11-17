#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

hash_file() {
    if [ -f "$1" ]; then
        sha256sum "$1" | awk '{print $1}'
    else
        echo ""
    fi
}

ensure_composer_dependencies() {
    local lock_hash
    lock_hash="$(hash_file composer.lock)"
    local stored_hash=""
    if [ -f vendor/.composer-lock.hash ]; then
        stored_hash="$(cat vendor/.composer-lock.hash)"
    fi

    if [ ! -f vendor/autoload.php ] || [ ! -s vendor/.composer-lock.hash ] || [ "$lock_hash" != "$stored_hash" ]; then
        echo "Installing composer dependencies..."
        composer install --no-interaction --prefer-dist
        mkdir -p vendor
        if [ -n "$lock_hash" ]; then
            printf '%s' "$lock_hash" > vendor/.composer-lock.hash
        else
            rm -f vendor/.composer-lock.hash
        fi
    fi
}

ensure_npm_dependencies() {
    local lock_hash
    lock_hash="$(hash_file package-lock.json)"
    local stored_hash=""
    if [ -f node_modules/.package-lock.hash ]; then
        stored_hash="$(cat node_modules/.package-lock.hash)"
    fi

    if [ ! -d node_modules ] || [ ! -s node_modules/.package-lock.hash ] || [ "$lock_hash" != "$stored_hash" ]; then
        echo "Installing npm dependencies..."
        npm install
        mkdir -p node_modules
        if [ -n "$lock_hash" ]; then
            printf '%s' "$lock_hash" > node_modules/.package-lock.hash
        else
            rm -f node_modules/.package-lock.hash
        fi
    fi
}

ensure_composer_dependencies
ensure_npm_dependencies

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

trap cleanup EXIT SIGINT SIGTERM

wait -n
exit_code=$?
exit "${exit_code}"

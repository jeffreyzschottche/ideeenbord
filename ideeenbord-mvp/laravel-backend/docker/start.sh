#!/bin/sh
set -eu

cd /var/www/html

# --- Storage & cache directories --------------------------------------------
mkdir -p \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# --- Ensure an APP_KEY exists ------------------------------------------------
if [ -z "${APP_KEY:-}" ] && ! grep -q "^APP_KEY=base64" .env 2>/dev/null; then
  php artisan key:generate --force --no-interaction || true
fi

# --- Public storage symlink --------------------------------------------------
php artisan storage:link --no-interaction || true

# --- Wait for the database (max ~60s) ---------------------------------------
if [ "${WAIT_FOR_DB:-true}" = "true" ]; then
  echo "Waiting for database..."
  i=0
  until php artisan db:show --no-interaction >/dev/null 2>&1; do
    i=$((i + 1))
    if [ "$i" -ge 30 ]; then
      echo "Database not reachable after 60s, continuing anyway."
      break
    fi
    sleep 2
  done
fi

# --- Migrations (opt-in) -----------------------------------------------------
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  echo "Running migrations..."
  php artisan migrate --force --no-interaction
fi

# --- Optimise (config/route/view/event caches) ------------------------------
php artisan optimize:clear || true
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache || true

# --- Hand over to supervisor (php-fpm + nginx + optional worker/scheduler) ---
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf

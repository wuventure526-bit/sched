#!/usr/bin/env bash
set -euo pipefail

# Railway assigns the port at runtime and expects the container to listen on it.
# Apache's port is baked into config files, so rewrite them before starting.
PORT="${PORT:-8080}"
sed -ri "s/^Listen [0-9]+/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Generate an app key only if the platform did not supply one. Without this a
# missing APP_KEY takes the whole app down with an encryption error.
if [ -z "${APP_KEY:-}" ]; then
    echo "entrypoint: APP_KEY is not set — generating an ephemeral one."
    echo "entrypoint: set APP_KEY in the service variables, or sessions and"
    echo "entrypoint: encrypted values will be invalidated on every deploy."
    export APP_KEY="base64:$(head -c 32 /dev/urandom | base64)"
fi

# Uploaded item photos live on the storage disk and are served from public/.
php artisan storage:link --force || true

# Cache config/routes/views at boot, not at build time: the environment
# variables they bake in only exist now.
#
# A failure here is not fatal -- the app runs fine uncached -- but it must be
# obvious in the logs rather than scrolling past, because route:cache is the
# step that catches things like two routes sharing a name.
php artisan config:cache || echo "entrypoint: WARNING config:cache failed, continuing uncached"
php artisan route:cache  || echo "entrypoint: WARNING route:cache failed, continuing uncached"
php artisan view:cache   || echo "entrypoint: WARNING view:cache failed, continuing uncached"

# Fail loudly here rather than letting apache2-foreground die in a restart loop.
apache2ctl configtest

# Opt-in schema migration. Left off by default so a redeploy can never alter
# the database unless it was asked to.
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "entrypoint: running migrations"
    php artisan migrate --force
fi

exec apache2-foreground

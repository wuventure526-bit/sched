#!/usr/bin/env bash
set -euo pipefail

# Apache aborts with "More than one MPM loaded" if more than one is enabled, and
# mod_php only runs under prefork. The image build already pins this, but repeat
# it here so the container self-heals even when an older or cached image boots --
# a crash loop is a bad way to discover a stale layer.
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* 2>/dev/null || true
if [ ! -e /etc/apache2/mods-enabled/mpm_prefork.load ]; then
    a2enmod mpm_prefork >/dev/null 2>&1 || true
fi
echo "entrypoint: MPM enabled -> $(ls /etc/apache2/mods-enabled/ | grep mpm | tr '\n' ' ')"

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
# The target must exist before the symlink is made: pointing public/storage at a
# missing directory leaves a dangling link, and Apache answers 403 -- not 404 --
# for every file under /storage, which is a confusing way to find that out.
# When a volume is mounted here it starts empty, so this runs on every boot.
mkdir -p storage/app/public
chown -R www-data:www-data storage/app || true
php artisan storage:link --force || true

if [ -e public/storage/. ]; then
    echo "entrypoint: storage link OK ($(find storage/app/public -type f 2>/dev/null | wc -l) file(s) published)"
else
    echo "entrypoint: WARNING public/storage does not resolve -- uploads will 403"
fi

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

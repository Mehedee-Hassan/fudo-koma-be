#!/bin/sh
set -eu
if [ -z "${APP_KEY:-}" ]; then
    echo 'APP_KEY is missing. Run python3 scripts/init-vps-env.py first.' >&2
    exit 1
fi
# These cache files belong to this container, not a shared code bind mount.
php artisan config:cache --quiet
php artisan route:cache --quiet
exec "$@"

#!/bin/bash
set -Eeuo pipefail

cd /var/www

# Render secret mounts may be readable by root but not by www-data.
# Copy only the CA certificate at runtime to an app-readable private location.
if [[ -n "${MYSQL_ATTR_SSL_CA:-}" ]]; then
    if [[ ! -f "$MYSQL_ATTR_SSL_CA" || ! -r "$MYSQL_ATTR_SSL_CA" ]]; then
        echo "Cannot read MySQL CA file. Check Render Secret Files and MYSQL_ATTR_SSL_CA." >&2
        exit 1
    fi
    (
        umask 077
        mkdir -p /run/app-certificates
        chown root:www-data /run/app-certificates
        chmod 750 /run/app-certificates
        cp "$MYSQL_ATTR_SSL_CA" /run/app-certificates/mysql-ca.pem
        chown www-data:www-data /run/app-certificates/mysql-ca.pem
        chmod 400 /run/app-certificates/mysql-ca.pem
    )
    export MYSQL_ATTR_SSL_CA=/run/app-certificates/mysql-ca.pem
    su-exec www-data php docker/check-ca.php
fi

# Allow maintenance commands with: docker run ... IMAGE php artisan ...
if (( $# > 0 )); then
    exec su-exec www-data "$@"
fi

: "${APP_KEY:?Set a persistent APP_KEY before starting the application}"
: "${APP_URL:?Set APP_URL to the public HTTPS address}"

# Persistent sessions survive Render container restarts; sync queues must not block signup.
if [[ "${RENDER:-}" == "true" && "${SESSION_DRIVER:-file}" == "file" ]]; then
    export SESSION_DRIVER=database
fi
export MAIL_QUEUE_CONNECTION="${MAIL_QUEUE_CONNECTION:-database}"
if [[ "$MAIL_QUEUE_CONNECTION" == "sync" ]]; then export MAIL_QUEUE_CONNECTION=database; fi

export PORT="${PORT:-10000}"
if [[ ! "$PORT" =~ ^[0-9]{1,5}$ ]] || (( 10#$PORT < 1 || 10#$PORT > 65535 )); then
    echo "PORT must be an integer between 1 and 65535" >&2
    exit 1
fi

# Substitute PORT only; preserve Nginx variables such as $uri and $query_string. envsubst
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/http.d/default.conf

mkdir -p storage/framework/{cache/data,sessions,views} storage/logs storage/app/public bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
su-exec www-data php artisan storage:link --force || true

su-exec www-data php artisan config:cache
su-exec www-data php artisan tinker --execute="echo '==> Cloudinary: ' . (App\Services\CloudinaryService::isConfigured() ? 'CONFIGURED (cloud: ' . (App\Services\CloudinaryService::getConfig()['cloud_name'] ?? '?') . ')' : 'NOT CONFIGURED (Fallback: MySQL Data URI)') . PHP_EOL;" || true
su-exec www-data php artisan tinker --execute="echo '==> Email Service: ' . (App\Services\EmailApiService::isConfigured() ? 'CONFIGURED (' . strtoupper(App\Services\EmailApiService::getProvider()) . ' HTTPS API)' : 'NOT CONFIGURED (Add RESEND_API_KEY or BREVO_API_KEY on Render)') . PHP_EOL;" || true

case "${RUN_MIGRATIONS:-true}" in
    true) su-exec www-data php artisan migrate --force --no-interaction ;;
    false) ;;
    *) echo "RUN_MIGRATIONS must be true or false" >&2; exit 1 ;;
esac

case "${RUN_SEEDERS:-false}" in
    true)
        if su-exec www-data php artisan db:seed --force --no-interaction; then
            echo "==> Seeding completed successfully."
        else
            echo "==> WARNING: Seeding failed (check SEED_ADMIN_EMAIL / SEED_ADMIN_PASSWORD). App will still start." >&2
        fi
        ;;
    false) ;;
    *) echo "RUN_SEEDERS must be true or false" >&2; exit 1 ;;
esac

su-exec www-data php artisan route:cache
su-exec www-data php artisan view:cache

nginx -t
php-fpm -t

# Stop the whole container if either server exits, and forward stop signals.
# Queue worker failure is non-fatal: log a warning and let the container continue.
server_pids=()
queue_pid=""
mail_pid=""
ai_review_pid=""
cleanup() {
    trap - EXIT TERM INT
    [[ -n "$mail_pid" ]] && kill -TERM "$mail_pid" 2>/dev/null || true
    [[ -n "$ai_review_pid" ]] && kill -TERM "$ai_review_pid" 2>/dev/null || true
    [[ -n "$queue_pid" ]] && kill -QUIT "$queue_pid" 2>/dev/null || true
    if (( ${#server_pids[@]} )); then
        kill -QUIT "${server_pids[@]}" 2>/dev/null || true
        wait "${server_pids[@]}" 2>/dev/null || true
    fi
}
trap cleanup EXIT
trap 'exit 0' TERM INT

php-fpm -F &
server_pids+=("$!")
nginx -g 'daemon off;' &
server_pids+=("$!")

# Dedicated mail worker runs even when the default application queue is sync.
(
    mail_worker=""
    trap '[[ -n "$mail_worker" ]] && kill -TERM "$mail_worker" 2>/dev/null; exit 0' TERM INT
    while true; do
        su-exec www-data php artisan queue:work "$MAIL_QUEUE_CONNECTION" \
            --queue=emails --tries=3 --timeout=30 --sleep=1 --max-time=3600 --no-interaction &
        mail_worker="$!"
        wait "$mail_worker" || true
        sleep 2
    done
) &
mail_pid="$!"

# AI replies have their own worker so slow provider requests never delay verification email.
(
    ai_worker=""
    trap '[[ -n "$ai_worker" ]] && kill -TERM "$ai_worker" 2>/dev/null; exit 0' TERM INT
    while true; do
        su-exec www-data php artisan queue:work "$MAIL_QUEUE_CONNECTION" \
            --queue=ai-reviews --tries=3 --timeout=45 --sleep=1 --max-time=3600 --no-interaction &
        ai_worker="$!"
        wait "$ai_worker" || true
        sleep 2
    done
) &
ai_review_pid="$!"

# Queue worker: xử lý email verification jobs (database queue).
# Restart tự động nếu chết (--tries=3 --sleep=3 --max-time=3600).
if [[ "${QUEUE_CONNECTION:-sync}" != "sync" ]]; then
    (
        while true; do
            su-exec www-data php artisan queue:work \
                --queue=default \
                --tries=3 \
                --sleep=3 \
                --max-time=3600 \
                --no-interaction 2>&1 || true
            echo "Queue worker restarting..." >&2
            sleep 2
        done
    ) &
    queue_pid="$!"
fi

status=0
wait -n "${server_pids[@]}" || status=$?
echo "A web server exited (status $status); stopping container" >&2
exit 1

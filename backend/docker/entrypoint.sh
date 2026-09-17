#!/bin/sh
set -e

cd /var/www/html

if [ -z "$APP_KEY" ]; then
    echo "FATAL: APP_KEY is not set."
    echo "Generate one locally with 'php artisan key:generate --show' and set it"
    echo "as an environment variable (APP_KEY=base64:...) on the backend service."
    exit 1
fi

echo "Waiting for the database at ${DB_HOST:-mysql}:${DB_PORT:-3306}..."
php -r '
    $host = getenv("DB_HOST") ?: "mysql";
    $port = getenv("DB_PORT") ?: "3306";
    $deadline = time() + 60;
    while (time() < $deadline) {
        $conn = @fsockopen($host, (int) $port, $errno, $errstr, 2);
        if ($conn) { fclose($conn); exit(0); }
        sleep(2);
    }
    fwrite(STDERR, "Database did not become reachable in time.\n");
    exit(1);
'

# composer install ran with --no-scripts during the image build (the app
# code isn't copied into that stage, so `artisan package:discover` can't run
# there) — do it now instead, so Sanctum/Dompdf's providers are registered.
php artisan package:discover --ansi

# Config/route/view caches are rebuilt on every boot (not baked into the image)
# so real runtime environment variables from Dokploy are always what's cached.
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force
fi

if [ "${RUN_SEEDERS:-true}" = "true" ]; then
    # DatabaseSeeder only bootstraps real reference/admin data (idempotent
    # updateOrCreate calls) — it never inserts fabricated demo trainees.
    echo "Seeding reference data (admin account, required documents, locations)..."
    php artisan db:seed --force
fi

php artisan storage:link || true

exec "$@"

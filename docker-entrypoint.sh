#!/bin/bash
set -e

echo "Running AssessMe application setup..."

# --- 1. Load environment variables ---
ENV_FILE="/var/www/html/.env"

if [ -f "$ENV_FILE" ]; then
    echo "Loading environment variables from $ENV_FILE..."
    eval "$(sed -r 's/^#.*$//g; /^\s*$/d' "$ENV_FILE")"
    echo "Environment variables loaded."
fi

# Set defaults
DB_DATABASE=${DB_DATABASE:-assessme_db}
DB_USERNAME=${DB_USERNAME:-assessme_user}
DB_HOST=${DB_HOST:-assessme-db}
DB_PORT=${DB_PORT:-5432}

# --- 2. Wait for PostgreSQL ---
echo "Waiting for database ($DB_DATABASE) at $DB_HOST:$DB_PORT..."
while true; do
    export PGPASSWORD="${DB_PASSWORD}"
    pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" -d "$DB_DATABASE" -t 1
    if [ $? -eq 0 ]; then
        break
    fi
    echo "Database not ready, retrying in 2 seconds..."
    sleep 2
done
echo "Database ready."

# --- 3. Wait for Redis ---
echo "Waiting for Redis at ${REDIS_HOST:-assessme-redis}:${REDIS_PORT:-6379}..."
until php -r "
    \$redis = new Redis();
    try {
        \$redis->connect('${REDIS_HOST:-assessme-redis}', ${REDIS_PORT:-6379});
        echo 'Redis ready.' . PHP_EOL;
        exit(0);
    } catch (Exception \$e) {
        exit(1);
    }
"; do
    echo "Redis not ready, retrying in 2 seconds..."
    sleep 2
done

# --- 4. Run migrations ---
echo "Running database migrations..."
php artisan migrate --force --no-interaction
echo "Migrations complete."

# --- 5. Application setup ---

# Remove Vite HMR hot file to force production assets
if [ -f "/var/www/html/public/hot" ]; then
    rm /var/www/html/public/hot
    echo "Removed public/hot to force production asset usage."
fi

# Clear and rebuild caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Optimise for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Filament 5 asset optimisation
php artisan filament:optimize

# Create storage symlink
php artisan storage:link

# --- 6. Seed superuser if no users exist ---
echo "Checking for superuser..."
php artisan db:seed --class=SuperuserSeeder --force --no-interaction
echo "Superuser check complete."

echo "AssessMe setup complete. Starting server..."

# Execute the command passed by docker compose (apache2-foreground or queue:work)
exec "$@"

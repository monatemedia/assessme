#!/bin/bash
# deploy-prod.sh
# Executed on the production server via SSH by the GitHub Actions workflow.
# Pulls the latest image, runs migrations, restarts services.

set -euo pipefail

echo "--- Starting AssessMe Production Deployment ---"
echo "✅ Working directory: $(pwd)"

# --- 1. Pull latest image ---
DEPLOY_TAG="production"
FULL_IMAGE_NAME="${IMAGE_NAME}:${DEPLOY_TAG}"

echo "📥 Pulling latest image: ${FULL_IMAGE_NAME}"
docker pull ${FULL_IMAGE_NAME}

# Update IMAGE_TAG in .env permanently
if grep -q "IMAGE_TAG=" .env; then
    sed -i "s/^IMAGE_TAG=.*/IMAGE_TAG=${DEPLOY_TAG}/" .env
else
    echo "IMAGE_TAG=${DEPLOY_TAG}" >> .env
fi
export IMAGE_TAG=${DEPLOY_TAG}
echo "🏷️ IMAGE_TAG=${IMAGE_TAG}"

# --- 2. Load DB credentials ---
RAW_DB_USER=$(grep "^DB_USERNAME=" .env | cut -d '=' -f 2- | tr -d '\r' | xargs || true)
RAW_DB_NAME=$(grep "^DB_DATABASE=" .env | cut -d '=' -f 2- | tr -d '\r' | xargs || true)
RAW_DB_PASS=$(grep "^DB_PASSWORD=" .env | cut -d '=' -f 2- | tr -d '\r' | xargs || true)

export DB_USERNAME="${RAW_DB_USER:-assessme_user}"
export DB_DATABASE="${RAW_DB_NAME:-assessme_db}"
export DB_PASSWORD="${RAW_DB_PASS:-}"

echo "✅ Credentials loaded (User: ${DB_USERNAME}, DB: ${DB_DATABASE})"

# --- 3. Start DB and Redis first ---
echo "🚀 Starting database and Redis..."
docker compose up -d assessme-db assessme-redis

# --- 4. Wait for PostgreSQL ---
echo "⏳ Waiting for PostgreSQL to be ready..."
MAX_RETRIES=30
COUNT=0
until docker exec assessme-db pg_isready -U "${DB_USERNAME}" -d "${DB_DATABASE}" > /dev/null 2>&1; do
    COUNT=$((COUNT + 1))
    if [ $COUNT -ge $MAX_RETRIES ]; then
        echo "❌ Database was not ready after 60 seconds."
        exit 1
    fi
    echo "Still waiting for DB... ($COUNT/$MAX_RETRIES)"
    sleep 2
done
echo "✅ Database ready."

# --- 5. Wait for Redis ---
echo "⏳ Waiting for Redis to be ready..."
COUNT=0
until docker exec assessme-redis redis-cli ping > /dev/null 2>&1; do
    COUNT=$((COUNT + 1))
    if [ $COUNT -ge $MAX_RETRIES ]; then
        echo "❌ Redis was not ready after 60 seconds."
        exit 1
    fi
    echo "Still waiting for Redis... ($COUNT/$MAX_RETRIES)"
    sleep 2
done
echo "✅ Redis ready."

# --- 6. Run migrations ---
echo "🛠️ Running migrations..."
docker compose run --rm -T \
    --entrypoint="/bin/bash" \
    --no-deps \
    -e IMAGE_TAG=${IMAGE_TAG} \
    -e DB_USERNAME=${DB_USERNAME} \
    -e DB_PASSWORD=${DB_PASSWORD} \
    -e DB_DATABASE=${DB_DATABASE} \
    assessme-web -c "php artisan migrate --force --no-interaction"

if [ $? -ne 0 ]; then
    echo "❌ Migrations failed."
    exit 1
fi
echo "✅ Migrations complete."

# --- 7. Start / restart web and queue ---
echo "🚀 Starting web and queue containers..."
docker compose up -d assessme-web assessme-queue

# --- 8. Fix storage permissions inside container ---
echo "🔐 Fixing storage permissions..."
docker exec assessme-web chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker exec assessme-web chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# --- 9. Gracefully restart queue worker ---
echo "🛎️ Signalling queue worker to gracefully restart..."
docker exec assessme-queue php artisan queue:restart || true

echo "✅ Queue worker restart signalled."
echo "--- AssessMe Production Deployment Complete ---"

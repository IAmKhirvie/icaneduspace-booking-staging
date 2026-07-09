#!/bin/sh

set -eu

PROD_DIR="/Users/icanacademy/icaneduspace-booking-local"
STAGING_DIR="/Users/icanacademy/icaneduspace-booking-staging"

mkdir -p "$STAGING_DIR"

rsync -a \
  --exclude 'storage/logs/*.log' \
  --exclude 'storage/app/backups' \
  --exclude 'storage/app/status' \
  --exclude 'database/*.sqlite' \
  --exclude 'database/*.sqlite-*' \
  "$PROD_DIR/" "$STAGING_DIR/"

rm -f "$STAGING_DIR"/database/*.sqlite "$STAGING_DIR"/database/*.sqlite-*
cp "$PROD_DIR/database/database.sqlite" "$STAGING_DIR/database/staging.sqlite"

LC_ALL=C sed -i '' 's/^APP_ENV=.*/APP_ENV=staging/' "$STAGING_DIR/.env"
LC_ALL=C sed -i '' 's#^APP_URL=.*#APP_URL=http://127.0.0.1:8622#' "$STAGING_DIR/.env"
LC_ALL=C sed -i '' 's#^DB_DATABASE=.*#DB_DATABASE=/app/database/staging.sqlite#' "$STAGING_DIR/.env"

echo "Staging prepared at $STAGING_DIR"
echo "Staging database: $STAGING_DIR/database/staging.sqlite"

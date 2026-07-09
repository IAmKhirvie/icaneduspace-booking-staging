#!/bin/sh

set -eu

PROD_DIR="/Users/icanacademy/icaneduspace-booking-local"
STAGING_DIR="/Users/icanacademy/icaneduspace-booking-staging"
PROD_CONTAINER="${PROD_CONTAINER:-icaneduspace-booking-local}"
CONFIRM="no"
FORCE="no"

for arg in "$@"; do
  case "$arg" in
    --confirm) CONFIRM="yes" ;;
    --force) FORCE="yes" ;;
    *) echo "Unknown option: $arg" >&2; exit 2 ;;
  esac
done

HOUR=$(/bin/date '+%H')
if [ "$FORCE" != "yes" ]; then
  if [ "$HOUR" -lt 21 ] && [ "$HOUR" -ge 5 ]; then
    echo "Refusing promotion outside low-usage window 21:00-05:00."
    echo "Current local hour: $HOUR"
    echo "Use --force only for urgent incidents."
    exit 1
  fi
fi

echo "Production: $PROD_DIR"
echo "Staging:    $STAGING_DIR"

if [ "$CONFIRM" != "yes" ]; then
  echo "Dry run only. Re-run with --confirm during the low-usage window to promote."
  exit 0
fi

"$PROD_DIR/scripts/backup-production.sh" "pre-promote"

rsync -a \
  --exclude '.env' \
  --exclude 'database/*.sqlite' \
  --exclude 'database/*.sqlite-*' \
  --exclude 'storage/app/backups' \
  --exclude 'storage/app/status' \
  --exclude 'storage/logs/*.log' \
  "$STAGING_DIR/" "$PROD_DIR/"

if command -v docker >/dev/null 2>&1 && docker ps --format '{{.Names}}' | grep -qx "$PROD_CONTAINER"; then
  docker exec "$PROD_CONTAINER" php artisan migrate --force
  docker exec "$PROD_CONTAINER" php artisan optimize:clear
fi

"$PROD_DIR/scripts/app-status.sh" event promote "" success "Promoted verified staging copy to production"

echo "Promotion complete."

#!/bin/sh

set -eu

APP_DIR=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
BACKUP_ROOT="$APP_DIR/storage/app/backups/production"
STATUS_SCRIPT="$APP_DIR/scripts/app-status.sh"
SNAPSHOT="${1:-}"
CONFIRM="${2:-}"
LIVE_DB="$APP_DIR/database/database.sqlite"

if [ -z "$SNAPSHOT" ]; then
  echo "Usage: $0 <snapshot-id|latest> [--confirm]" >&2
  "$STATUS_SCRIPT" list || true
  exit 2
fi

if [ "$SNAPSHOT" = "latest" ]; then
  SNAPSHOT=$("$STATUS_SCRIPT" latest)
fi

SNAPSHOT_DIR="$BACKUP_ROOT/$SNAPSHOT"
DATABASE_BACKUP="$SNAPSHOT_DIR/database.sqlite"

if [ ! -f "$DATABASE_BACKUP" ]; then
  echo "Snapshot database not found: $DATABASE_BACKUP" >&2
  exit 1
fi

echo "Snapshot: $SNAPSHOT"
echo "Restore database from: $DATABASE_BACKUP"
echo "Restore database to:   $LIVE_DB"

if [ "$CONFIRM" != "--confirm" ]; then
  echo "Dry run only. Re-run with --confirm to restore."
  exit 0
fi

"$APP_DIR/scripts/backup-production.sh" "pre-restore-$SNAPSHOT"

PRE_RESTORE_COPY="$LIVE_DB.pre-restore-$(/bin/date -u '+%Y%m%dT%H%M%SZ')"
cp -p "$LIVE_DB" "$PRE_RESTORE_COPY"
cp -p "$DATABASE_BACKUP" "$LIVE_DB"

"$STATUS_SCRIPT" event restore "$SNAPSHOT" success "Restored database backup to $LIVE_DB"

echo "Restored database. Previous live database copy: $PRE_RESTORE_COPY"

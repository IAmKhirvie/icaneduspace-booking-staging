#!/bin/sh

set -eu

APP_DIR=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
LABEL="${1:-manual}"
SAFE_LABEL=$(printf "%s" "$LABEL" | tr -cs 'A-Za-z0-9._-' '-' | sed 's/^-//; s/-$//')
TIMESTAMP=$(/bin/date -u '+%Y%m%dT%H%M%SZ')
SNAPSHOT_ID="${TIMESTAMP}-${SAFE_LABEL:-manual}"
BACKUP_ROOT="$APP_DIR/storage/app/backups/production"
BACKUP_DIR="$BACKUP_ROOT/$SNAPSHOT_ID"
STATUS_SCRIPT="$APP_DIR/scripts/app-status.sh"
STATUS_DB="$($STATUS_SCRIPT path)"

read_env_value() {
  key="$1"
  grep -E "^${key}=" "$APP_DIR/.env" | tail -1 | sed -E "s/^${key}=//; s/^\"//; s/\"$//"
}

resolve_host_path() {
  value="$1"
  case "$value" in
    /app/*) printf "%s/%s" "$APP_DIR" "${value#/app/}" ;;
    /*) printf "%s" "$value" ;;
    *) printf "%s/%s" "$APP_DIR" "$value" ;;
  esac
}

DB_CONNECTION=$(read_env_value DB_CONNECTION)
DB_DATABASE=$(read_env_value DB_DATABASE)
APP_URL=$(read_env_value APP_URL || true)
HEALTH_URL="${HEALTH_URL:-http://127.0.0.1:8621}"
DB_PATH=$(resolve_host_path "$DB_DATABASE")
REQUIRE_321="${REQUIRE_321:-no}"
BACKUP_REPLICA_ROOT="${BACKUP_REPLICA_ROOT:-}"
BACKUP_OFFSITE_ROOT="${BACKUP_OFFSITE_ROOT:-}"
REPLICA_BACKUP_DIR=""
OFFSITE_BACKUP_DIR=""
REPLICA_STATUS="not_configured"
OFFSITE_STATUS="not_configured"

prepare_external_root() {
  target_name="$1"
  root="$2"
  app_abs=$(CDPATH= cd -- "$APP_DIR" && pwd -P)

  mkdir -p "$root"
  root_abs=$(CDPATH= cd -- "$root" && pwd -P)

  case "$root_abs" in
    "$app_abs"|"$app_abs"/*)
      echo "Refusing $target_name backup root inside app directory: $root_abs" >&2
      exit 1
      ;;
  esac

  printf "%s" "$root_abs"
}

copy_snapshot_to() {
  destination="$1"

  mkdir -p "$destination"
  if command -v rsync >/dev/null 2>&1; then
    rsync -a "$BACKUP_DIR/" "$destination/"
  else
    cp -pR "$BACKUP_DIR/." "$destination/"
  fi
}

if [ "$DB_CONNECTION" != "sqlite" ]; then
  echo "Refusing backup: DB_CONNECTION is '$DB_CONNECTION', expected 'sqlite'." >&2
  exit 1
fi

if [ ! -f "$DB_PATH" ]; then
  echo "Refusing backup: SQLite database not found at $DB_PATH" >&2
  exit 1
fi

if [ "$REQUIRE_321" = "yes" ]; then
  if [ -z "$BACKUP_REPLICA_ROOT" ] || [ -z "$BACKUP_OFFSITE_ROOT" ]; then
    echo "Refusing backup: REQUIRE_321=yes needs BACKUP_REPLICA_ROOT and BACKUP_OFFSITE_ROOT." >&2
    echo "Use BACKUP_REPLICA_ROOT for the second local medium and BACKUP_OFFSITE_ROOT for an off-device/cloud/network copy." >&2
    exit 1
  fi
fi

if [ -n "$BACKUP_REPLICA_ROOT" ]; then
  REPLICA_ROOT_ABS=$(prepare_external_root "replica" "$BACKUP_REPLICA_ROOT")
  REPLICA_BACKUP_DIR="$REPLICA_ROOT_ABS/$SNAPSHOT_ID"
  REPLICA_STATUS="pending"
fi

if [ -n "$BACKUP_OFFSITE_ROOT" ]; then
  OFFSITE_ROOT_ABS=$(prepare_external_root "offsite" "$BACKUP_OFFSITE_ROOT")
  OFFSITE_BACKUP_DIR="$OFFSITE_ROOT_ABS/$SNAPSHOT_ID"
  OFFSITE_STATUS="pending"
fi

mkdir -p "$BACKUP_DIR"
chmod 700 "$BACKUP_ROOT" "$BACKUP_DIR"

QUICK_CHECK=$(sqlite3 "$DB_PATH" 'PRAGMA quick_check;')
if [ "$QUICK_CHECK" != "ok" ]; then
  echo "Refusing backup: SQLite quick_check returned '$QUICK_CHECK'." >&2
  exit 1
fi

DATABASE_BACKUP="$BACKUP_DIR/database.sqlite"
ENV_BACKUP="$BACKUP_DIR/env.backup"
SOURCE_ARCHIVE="$BACKUP_DIR/source.tar.gz"
MANIFEST="$BACKUP_DIR/manifest.json"
INCLUDE_LIST="$BACKUP_DIR/source-files.txt"

sqlite3 "$DB_PATH" ".backup '$DATABASE_BACKUP'"
cp -p "$APP_DIR/.env" "$ENV_BACKUP"
chmod 600 "$ENV_BACKUP"

: > "$INCLUDE_LIST"
for path in \
  .env.example README.md PRODUCTION_DEPLOYMENT.md docs scripts app bootstrap config \
  database/factories database/migrations database/seeders lang public/build public/media \
  resources routes tests composer.json composer.lock package.json package-lock.json \
  phpunit.xml postcss.config.js tailwind.config.js vite.config.js
do
  if [ -e "$APP_DIR/$path" ]; then
    printf "%s\n" "$path" >> "$INCLUDE_LIST"
  fi
done

(cd "$APP_DIR" && LC_ALL=C /usr/bin/tar -czf "$SOURCE_ARCHIVE" -T "$INCLUDE_LIST")

HEALTH_STATUS="${HEALTH_STATUS_OVERRIDE:-not_checked}"
if [ -z "${HEALTH_STATUS_OVERRIDE:-}" ] && command -v curl >/dev/null 2>&1 && [ -n "$HEALTH_URL" ]; then
  HTTP_CODE=$(curl -sS -L --max-time 20 -o "$BACKUP_DIR/health.html" -w "%{http_code}" "$HEALTH_URL" || true)
  HEALTH_STATUS="http_${HTTP_CODE:-failed}"
fi

write_manifest() {
  cat > "$MANIFEST" <<JSON
{
  "snapshot_id": "$SNAPSHOT_ID",
  "label": "$LABEL",
  "created_at": "$TIMESTAMP",
  "app_dir": "$APP_DIR",
  "app_url": "$APP_URL",
  "health_url": "$HEALTH_URL",
  "db_connection": "$DB_CONNECTION",
  "db_path": "$DB_PATH",
  "database_backup": "$DATABASE_BACKUP",
  "source_archive": "$SOURCE_ARCHIVE",
  "env_backup": "$ENV_BACKUP",
  "sqlite_quick_check": "$QUICK_CHECK",
  "health_status": "$HEALTH_STATUS",
  "require_321": "$REQUIRE_321",
  "replica_backup_dir": "$REPLICA_BACKUP_DIR",
  "replica_status": "$REPLICA_STATUS",
  "offsite_backup_dir": "$OFFSITE_BACKUP_DIR",
  "offsite_status": "$OFFSITE_STATUS"
}
JSON
}

"$STATUS_SCRIPT" record \
  --snapshot-id "$SNAPSHOT_ID" \
  --label "$LABEL" \
  --app-url "$APP_URL" \
  --db-path "$DB_PATH" \
  --backup-dir "$BACKUP_DIR" \
  --source-archive "$SOURCE_ARCHIVE" \
  --database-backup "$DATABASE_BACKUP" \
  --env-backup "$ENV_BACKUP" \
  --health-status "$HEALTH_STATUS" \
  --notes "production backup"

sqlite3 -cmd '.timeout 10000' "$STATUS_DB" 'PRAGMA wal_checkpoint(FULL);' >/dev/null
sqlite3 -cmd '.timeout 10000' "$STATUS_DB" ".backup '$BACKUP_DIR/app_status.sqlite'"

if [ -n "$REPLICA_BACKUP_DIR" ]; then
  copy_snapshot_to "$REPLICA_BACKUP_DIR"
  REPLICA_STATUS="ok"
fi

if [ -n "$OFFSITE_BACKUP_DIR" ]; then
  copy_snapshot_to "$OFFSITE_BACKUP_DIR"
  OFFSITE_STATUS="ok"
fi

if [ "$REQUIRE_321" = "yes" ] && { [ "$REPLICA_STATUS" != "ok" ] || [ "$OFFSITE_STATUS" != "ok" ]; }; then
  "$STATUS_SCRIPT" event backup "$SNAPSHOT_ID" failed "3-2-1 backup failed: replica=$REPLICA_STATUS offsite=$OFFSITE_STATUS"
  echo "Refusing success: 3-2-1 backup incomplete. replica=$REPLICA_STATUS offsite=$OFFSITE_STATUS" >&2
  exit 1
fi

write_manifest

if [ -n "$REPLICA_BACKUP_DIR" ]; then
  cp -p "$MANIFEST" "$REPLICA_BACKUP_DIR/manifest.json"
fi

if [ -n "$OFFSITE_BACKUP_DIR" ]; then
  cp -p "$MANIFEST" "$OFFSITE_BACKUP_DIR/manifest.json"
fi

"$STATUS_SCRIPT" event backup "$SNAPSHOT_ID" success "Backup created at $BACKUP_DIR; replica=$REPLICA_STATUS offsite=$OFFSITE_STATUS"

sqlite3 -cmd '.timeout 10000' "$STATUS_DB" 'PRAGMA wal_checkpoint(FULL);' >/dev/null
sqlite3 -cmd '.timeout 10000' "$STATUS_DB" ".backup '$BACKUP_DIR/app_status.sqlite'"

if [ -n "$REPLICA_BACKUP_DIR" ]; then
  cp -p "$BACKUP_DIR/app_status.sqlite" "$REPLICA_BACKUP_DIR/app_status.sqlite"
fi

if [ -n "$OFFSITE_BACKUP_DIR" ]; then
  cp -p "$BACKUP_DIR/app_status.sqlite" "$OFFSITE_BACKUP_DIR/app_status.sqlite"
fi

echo "Snapshot: $SNAPSHOT_ID"
echo "Backup:   $BACKUP_DIR"
echo "Replica:  $REPLICA_STATUS ${REPLICA_BACKUP_DIR:-}"
echo "Offsite:  $OFFSITE_STATUS ${OFFSITE_BACKUP_DIR:-}"
echo "Health:   $HEALTH_STATUS"

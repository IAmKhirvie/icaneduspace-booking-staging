#!/bin/sh

set -eu

APP_DIR=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
STATUS_DIR="$APP_DIR/storage/app/status"
STATUS_DB="$STATUS_DIR/app_status.sqlite"

now_utc() {
  /bin/date -u '+%Y-%m-%dT%H:%M:%SZ'
}

sql_escape() {
  printf "%s" "$1" | sed "s/'/''/g"
}

init_db() {
  mkdir -p "$STATUS_DIR"
  chmod 700 "$STATUS_DIR"

  sqlite3 "$STATUS_DB" >/dev/null <<'SQL'
PRAGMA busy_timeout=10000;
PRAGMA journal_mode=WAL;
CREATE TABLE IF NOT EXISTS app_snapshots (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  snapshot_id TEXT NOT NULL UNIQUE,
  label TEXT,
  environment TEXT NOT NULL DEFAULT 'production',
  app_url TEXT,
  app_dir TEXT NOT NULL,
  db_path TEXT NOT NULL,
  backup_dir TEXT,
  source_archive TEXT,
  database_backup TEXT,
  env_backup TEXT,
  health_status TEXT,
  notes TEXT,
  created_at TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS app_snapshots_created_at_index ON app_snapshots (created_at);
CREATE INDEX IF NOT EXISTS app_snapshots_health_status_index ON app_snapshots (health_status);

CREATE TABLE IF NOT EXISTS app_events (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  event_type TEXT NOT NULL,
  snapshot_id TEXT,
  status TEXT NOT NULL,
  message TEXT,
  created_at TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS app_events_created_at_index ON app_events (created_at);
CREATE INDEX IF NOT EXISTS app_events_snapshot_id_index ON app_events (snapshot_id);
SQL

  chmod 600 "$STATUS_DB"
}

record_snapshot() {
  init_db

  SNAPSHOT_ID=""
  LABEL=""
  APP_URL=""
  DB_PATH=""
  BACKUP_DIR=""
  SOURCE_ARCHIVE=""
  DATABASE_BACKUP=""
  ENV_BACKUP=""
  HEALTH_STATUS=""
  NOTES=""

  while [ "$#" -gt 0 ]; do
    case "$1" in
      --snapshot-id) SNAPSHOT_ID="$2"; shift 2 ;;
      --label) LABEL="$2"; shift 2 ;;
      --app-url) APP_URL="$2"; shift 2 ;;
      --db-path) DB_PATH="$2"; shift 2 ;;
      --backup-dir) BACKUP_DIR="$2"; shift 2 ;;
      --source-archive) SOURCE_ARCHIVE="$2"; shift 2 ;;
      --database-backup) DATABASE_BACKUP="$2"; shift 2 ;;
      --env-backup) ENV_BACKUP="$2"; shift 2 ;;
      --health-status) HEALTH_STATUS="$2"; shift 2 ;;
      --notes) NOTES="$2"; shift 2 ;;
      *) echo "Unknown option: $1" >&2; exit 2 ;;
    esac
  done

  if [ -z "$SNAPSHOT_ID" ] || [ -z "$DB_PATH" ]; then
    echo "record requires --snapshot-id and --db-path" >&2
    exit 2
  fi

  CREATED_AT=$(now_utc)

  sqlite3 -cmd '.timeout 10000' "$STATUS_DB" <<SQL
BEGIN IMMEDIATE;
INSERT INTO app_snapshots (
  snapshot_id, label, environment, app_url, app_dir, db_path,
  backup_dir, source_archive, database_backup, env_backup,
  health_status, notes, created_at
) VALUES (
  '$(sql_escape "$SNAPSHOT_ID")',
  '$(sql_escape "$LABEL")',
  'production',
  '$(sql_escape "$APP_URL")',
  '$(sql_escape "$APP_DIR")',
  '$(sql_escape "$DB_PATH")',
  '$(sql_escape "$BACKUP_DIR")',
  '$(sql_escape "$SOURCE_ARCHIVE")',
  '$(sql_escape "$DATABASE_BACKUP")',
  '$(sql_escape "$ENV_BACKUP")',
  '$(sql_escape "$HEALTH_STATUS")',
  '$(sql_escape "$NOTES")',
  '$(sql_escape "$CREATED_AT")'
);
COMMIT;
SQL
}

record_event() {
  init_db

  EVENT_TYPE="${1:-event}"
  SNAPSHOT_ID="${2:-}"
  STATUS="${3:-info}"
  MESSAGE="${4:-}"
  CREATED_AT=$(now_utc)

  sqlite3 -cmd '.timeout 10000' "$STATUS_DB" <<SQL
BEGIN IMMEDIATE;
INSERT INTO app_events (event_type, snapshot_id, status, message, created_at)
VALUES (
  '$(sql_escape "$EVENT_TYPE")',
  '$(sql_escape "$SNAPSHOT_ID")',
  '$(sql_escape "$STATUS")',
  '$(sql_escape "$MESSAGE")',
  '$(sql_escape "$CREATED_AT")'
);
COMMIT;
SQL
}

set_health() {
  init_db

  SNAPSHOT_ID="${1:-}"
  HEALTH_STATUS="${2:-}"
  MESSAGE="${3:-}"

  if [ -z "$SNAPSHOT_ID" ] || [ -z "$HEALTH_STATUS" ]; then
    echo "Usage: $0 set-health <snapshot-id> <health-status> [message]" >&2
    exit 2
  fi

  sqlite3 -cmd '.timeout 10000' "$STATUS_DB" <<SQL
BEGIN IMMEDIATE;
UPDATE app_snapshots
SET health_status = '$(sql_escape "$HEALTH_STATUS")'
WHERE snapshot_id = '$(sql_escape "$SNAPSHOT_ID")';
COMMIT;
SQL

  record_event health "$SNAPSHOT_ID" "$HEALTH_STATUS" "$MESSAGE"
}

case "${1:-init}" in
  init)
    init_db
    echo "$STATUS_DB"
    ;;
  record)
    shift
    record_snapshot "$@"
    ;;
  event)
    shift
    record_event "$@"
    ;;
  list)
    init_db
    sqlite3 -cmd '.timeout 10000' -header -column "$STATUS_DB" \
      "SELECT snapshot_id, label, health_status, created_at FROM app_snapshots ORDER BY created_at DESC LIMIT 20;"
    ;;
  latest)
    init_db
    sqlite3 -cmd '.timeout 10000' -noheader "$STATUS_DB" \
      "SELECT snapshot_id FROM app_snapshots ORDER BY created_at DESC LIMIT 1;"
    ;;
  set-health)
    shift
    set_health "$@"
    ;;
  path)
    init_db
    echo "$STATUS_DB"
    ;;
  *)
    echo "Usage: $0 init|record|event|list|latest|set-health|path" >&2
    exit 2
    ;;
esac

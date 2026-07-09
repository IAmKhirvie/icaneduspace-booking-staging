# ICAN Eduspace Production Safety Rules

This app treats `https://booking.icanacademy.work` as production.

## Permanent Rule

Do not apply risky changes directly to the deployed app first.

Use two separate app copies:

- Production: `/Users/icanacademy/icaneduspace-booking-local`
- Staging/copy: `/Users/icanacademy/icaneduspace-booking-staging`

Production must stay live. Staging is where updates, maintenance, bug fixes, database migrations, indexing, pagination changes, and configuration changes are tested first.

## Change Workflow

1. Back up production.
2. Refresh staging from production.
3. Apply the change to staging only.
4. Verify staging with tests, migrations, health checks, and manual review.
5. Promote the verified change to production only during low-usage hours.
6. Back up production again before promotion.
7. Record the backup/promotion in the app status database.
8. Keep rollback backups available.

## Low-Usage Promotion Window

Default production promotion window:

```text
21:00 to 05:00 local machine time
```

This keeps updates in evening/midnight hours when booking usage should be lower. Use `--force` only for urgent incidents.

## Database Choice

Production uses a dedicated SQLite file:

```text
database/database.sqlite
```

Staging uses its own SQLite file:

```text
database/staging.sqlite
```

This avoids conflicts with other local apps because no shared database port is used.

## App Status Database

Operational backup and deployment history is stored in:

```text
storage/app/status/app_status.sqlite
```

This database records snapshots, backup paths, health status, and promotion/restore events.

## Standard Commands

Create or update the staging copy:

```bash
scripts/prepare-staging.sh
```

Start staging locally:

```bash
scripts/start-staging.sh
```

Start staging for review from another device on the same LAN:

```bash
STAGING_BIND=0.0.0.0 STAGING_RECREATE=yes scripts/start-staging.sh
```

The script prints both the local URL and LAN URL. Use LAN access only for staging review, not for production.

Back up production:

```bash
scripts/backup-production.sh "before-change"
```

## 3-2-1 Backup Rule

Production backups must follow 3-2-1 before any production-risk change is promoted:

- 3 copies: live production data, local snapshot, and an external/off-device snapshot.
- 2 media/locations: the local app disk plus a separate mounted drive, network share, or cloud-synced folder.
- 1 offsite/off-device copy: a folder that is not inside the app directory and is not only on the same local app storage.

The backup script always creates the local snapshot in:

```text
storage/app/backups/production/
```

To require 3-2-1 compliance, provide both external targets:

```bash
REQUIRE_321=yes \
BACKUP_REPLICA_ROOT="/Volumes/ICAN-Backups/booking-production" \
BACKUP_OFFSITE_ROOT="/Volumes/CloudBackup/booking-production" \
scripts/backup-production.sh "before-change"
```

If `REQUIRE_321=yes` and either external target is missing, the script refuses to mark the backup as successful.

When you are SSH-ing from another device and want a backup saved on that device, run `scp` from that device after the backup is created:

```bash
scp -r icanacademy@192.168.68.106:/Users/icanacademy/icaneduspace-booking-local/storage/app/backups/production/<snapshot-id> ~/ICAN-booking-backups/
```

Replace `<snapshot-id>` with the snapshot printed by the backup command.

By default, backup health checks use the local production endpoint `http://127.0.0.1:8621` so the check works without external DNS. To check the public URL from an unrestricted shell, run:

```bash
HEALTH_URL=https://booking.icanacademy.work scripts/backup-production.sh "public-health-check"
```

If health was already verified separately, record it in the snapshot:

```bash
HEALTH_STATUS_OVERRIDE=http_200_verified scripts/backup-production.sh "verified-health"
```

List production snapshots:

```bash
scripts/app-status.sh list
```

Dry-run production promotion:

```bash
scripts/promote-from-staging.sh
```

Promote during the low-usage window:

```bash
scripts/promote-from-staging.sh --confirm
```

Urgent promotion outside the window:

```bash
scripts/promote-from-staging.sh --confirm --force
```

Dry-run restore:

```bash
scripts/restore-production.sh latest
```

Confirmed restore:

```bash
scripts/restore-production.sh latest --confirm
```

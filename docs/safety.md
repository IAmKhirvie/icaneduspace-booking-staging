# Loop Safety — ICAN Eduspace Booking Staging

This document adapts the app's production safety rules for loop-engineering tests.

## Mode

- Current loop level: L1 report-only.
- The loop may update `STATE.md` and `loop-run-log.md`.
- The loop must not modify application code unless the user explicitly asks in the current conversation.

## Environments

- Staging: `/Users/icanacademy/icaneduspace-booking-staging`
- Production: `/Users/icanacademy/icaneduspace-booking-local`
- Production URL: `https://booking.icanacademy.work`

Production must stay live. Staging is where updates and maintenance are tested first.

## Denylist

The loop must not edit these without explicit human approval:

- `/Users/icanacademy/icaneduspace-booking-local/**`
- `.env`
- `.env.*`
- `database/*.sqlite`
- `database/*.sqlite-*`
- `storage/app/status/*.sqlite`
- `storage/app/backups/**`
- `storage/logs/**`
- `vendor/**`
- `node_modules/**`
- deployment and rollback scripts under `scripts/`

Do not print secret values from `.env` or backup files.

## Promotion

Never run production promotion from a loop.

Blocked unless explicitly requested by the human in the current conversation:

```bash
scripts/promote-from-staging.sh --confirm
scripts/promote-from-staging.sh --confirm --force
```

The normal promotion window is 21:00-05:00 local time. `--force` is for urgent incidents only.

## Allowed Report-Only Checks

These are acceptable for triage when the user asks for verification:

```bash
composer test
php artisan test
npm run build
scripts/app-status.sh list
```

`phpunit.xml` sets `DB_DATABASE=:memory:`, so test runs should not modify the staging SQLite database.

## Escalation

Escalate to the human instead of acting when findings involve:

- authentication, authorization, roles, or permissions
- booking conflict logic
- database migrations or seed data
- production backup, restore, or promotion
- environment variables, keys, or external service credentials
- repeated test failures after one clear triage pass

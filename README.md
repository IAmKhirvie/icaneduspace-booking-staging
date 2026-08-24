# ICAN Eduspace Booking System

access at: booking.icanacademy.work
Laravel booking system for classroom reservations, staff review, room/package management, customer dashboards, REST API access, Korean/English localization, and a Filament admin panel.

## Production Safety

The deployed app and staging copy are separate:

- Production: `/Users/icanacademy/icaneduspace-booking-local`
- Staging: `/Users/icanacademy/icaneduspace-booking-staging`

Use staging for updates and maintenance first. Promote to production only after verification, preferably during the low-usage evening/midnight window.

See [`docs/PRODUCTION_SAFETY.md`](docs/PRODUCTION_SAFETY.md).

## Stack

- Laravel 13
- Jetstream, Fortify, Livewire, Sanctum
- Filament 4
- Spatie Permission and Activity Log
- Scribe API documentation
- Vite and Tailwind CSS

## Local Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

## Excluded From Git

This repository intentionally does not include local or generated files:

- `.env` and other environment files with secrets
- `vendor/` and `node_modules/`
- local SQLite databases
- generated caches, logs, and compiled views
- generated Filament/Scribe public assets
- proprietary or local media in `public/media/`

After pulling the project, regenerate dependencies and assets with the setup commands above.

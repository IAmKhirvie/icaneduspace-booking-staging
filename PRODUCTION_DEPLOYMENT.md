# ICAN Eduspace Production Deployment Handoff

This file is for the next Codex or engineer setting up the ICAN Eduspace booking reservation system on a new production server.

## Project Summary

This is a Laravel 13 booking reservation system for ICAN Eduspace.

Core behavior that must keep working:

- Customers can register, log in, request bookings, view bookings, and cancel allowed bookings.
- Admin can manage bookings, rooms, packages, and users.
- Staff can manage customer bookings and create bookings for customers.
- Booking requests send email receipts to customers and email alerts to staff/admin.
- Booking approval, rejection, cancellation, updates, and 10-minute reminders notify customers by email.
- Customers can reschedule active upcoming bookings; customer reschedules return to pending for staff review, while staff/admin reschedules notify the customer immediately.
- Exact duplicate booking requests are blocked.
- Additional same-day bookings for a different time slot require confirmation.
- Location/maps must use: `Strata 100, Emerald Ave., Ortigas Center, Pasig City, Philippines`.
- Public mail sender/admin email should be configured in `.env`.
- Booking form includes equipment requests and coffee/snacks requests, including premium beverage.

## Required Server Stack

Recommended production stack:

- Ubuntu 22.04/24.04 or equivalent Linux server
- Nginx or Apache
- PHP 8.3 or newer with PHP-FPM
- Composer 2
- Node.js 22+ and npm
- MySQL 8/MariaDB 10.6+ or PostgreSQL
- Cron enabled
- Supervisor only if queue workers are later used

Required PHP extensions usually include:

```bash
php-cli php-fpm php-mbstring php-xml php-curl php-zip php-sqlite3 php-mysql php-bcmath php-intl
```

Use the matching package names for the server OS and PHP version.

## Deployment Directory

Example production path:

```bash
/var/www/icaneduspace
```

The web server document root must point to:

```bash
/var/www/icaneduspace/public
```

Never point Nginx/Apache at the project root.

## Initial Setup Commands

From the server:

```bash
cd /var/www
git clone <REPOSITORY_URL> icaneduspace
cd /var/www/icaneduspace
composer install --no-dev --optimize-autoloader
npm ci
npm run build
cp .env.example .env
php artisan key:generate
```

Then edit `.env` before migrating.

## Production `.env`

Use real production values. Do not commit `.env`.

Minimum required production settings:

```env
APP_NAME="ICAN Eduspace"
APP_ENV=production
APP_KEY=base64:GENERATED_BY_ARTISAN
APP_DEBUG=false
APP_URL=https://YOUR_DOMAIN_HERE

APP_LOCALE=en
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=icaneduspace
DB_USERNAME=icaneduspace
DB_PASSWORD=REPLACE_WITH_STRONG_DB_PASSWORD

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=REPLACE_WITH_SMTP_USERNAME
MAIL_PASSWORD=REPLACE_WITH_GMAIL_APP_PASSWORD
MAIL_FROM_ADDRESS="REPLACE_WITH_FROM_EMAIL"
MAIL_FROM_NAME="${APP_NAME}"
MAIL_TIMEOUT=10

VITE_APP_NAME="${APP_NAME}"
```

For Gmail SMTP, use a Google App Password, not the normal Gmail login password. Remove spaces from the app password.

If using SQLite instead of MySQL for a small single-server install, create the database file and set:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/icaneduspace/database/database.sqlite
```

Then run:

```bash
touch database/database.sqlite
```

MySQL is preferred for production.

## Database Setup

Create the database and user first if using MySQL:

```sql
CREATE DATABASE icaneduspace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'icaneduspace'@'localhost' IDENTIFIED BY 'REPLACE_WITH_STRONG_DB_PASSWORD';
GRANT ALL PRIVILEGES ON icaneduspace.* TO 'icaneduspace'@'localhost';
FLUSH PRIVILEGES;
```

Run migrations and seed default roles/users/rooms/packages:

```bash
php artisan migrate --force
php artisan db:seed --force
```

Seeded default users:

- Admin: value of `SEED_ADMIN_EMAIL`
- Staff: `staff@icaneduspace.test`
- Customer: `customer@icaneduspace.test`
- Default password from seeder: value of `SEED_*_PASSWORD`

Immediately log in as admin and change/remove default test accounts. Create real staff accounts from:

```text
/dashboard/manage/users
```

## File Permissions

The web server user must be able to write to `storage` and `bootstrap/cache`.

Example for Ubuntu/Nginx:

```bash
sudo chown -R www-data:www-data /var/www/icaneduspace/storage /var/www/icaneduspace/bootstrap/cache
sudo chmod -R ug+rwX /var/www/icaneduspace/storage /var/www/icaneduspace/bootstrap/cache
```

Create the public storage symlink:

```bash
php artisan storage:link
```

## Optimize Laravel

After `.env`, migrations, and build are correct:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

After every deployment that changes config/routes/views:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

## Cron Scheduler

This app depends on Laravel scheduler for 10-minute booking reminder emails.

Install this cron entry for the server user that owns/runs the app, usually `www-data` or the deploy user:

```cron
* * * * * cd /var/www/icaneduspace && /usr/bin/php artisan schedule:run >> /var/www/icaneduspace/storage/logs/scheduler.log 2>&1
```

Confirm PHP path first:

```bash
which php
```

If PHP is not `/usr/bin/php`, update the cron line.

Verify scheduler sees the reminder command:

```bash
php artisan schedule:list
```

Expected scheduled command:

```text
* * * * * php artisan bookings:send-reminders
```

Manual dry check:

```bash
php artisan bookings:send-reminders
```

It should print something like:

```text
Sent 0 booking reminder(s).
```

## Queue Worker

Current email notifications are sent directly and do not require a queue worker.

`QUEUE_CONNECTION=database` is configured for future background jobs. If notifications/jobs are later changed to queue, install Supervisor:

```ini
[program:icaneduspace-worker]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php /var/www/icaneduspace/artisan queue:work database --sleep=3 --tries=3 --timeout=90
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/icaneduspace/storage/logs/worker.log
stopwaitsecs=3600
```

Do not add Supervisor unless queued jobs are actually enabled.

## Nginx Example

Replace `YOUR_DOMAIN_HERE`.

```nginx
server {
    listen 80;
    server_name YOUR_DOMAIN_HERE www.YOUR_DOMAIN_HERE;

    root /var/www/icaneduspace/public;
    index index.php index.html;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Then enable HTTPS with Certbot or the hosting provider's SSL tooling. After SSL is active, make sure `.env` has:

```env
APP_URL=https://YOUR_DOMAIN_HERE
```

## Apache Note

If using Apache, enable rewrite and point the virtual host to `/public`:

```bash
sudo a2enmod rewrite
```

The Laravel `public/.htaccess` file handles front-controller routing.

## Deployment Update Procedure

For future releases:

```bash
cd /var/www/icaneduspace
git pull
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

If using PHP-FPM OPcache, reload PHP-FPM:

```bash
sudo systemctl reload php8.3-fpm
```

Adjust service name if the PHP version differs.

## Post-Deploy Verification

Run:

```bash
php artisan test
php artisan schedule:list
php artisan bookings:send-reminders
```

Then verify these in the browser:

- `/register` loads for customer registration.
- `/login` works for customers.
- `/admin/login` works for admin.
- `/staff/login` works for staff.
- `/bookings/create` has equipment and coffee/snacks fields.
- `/dashboard/manage/bookings` shows bookings for staff/admin.
- `/dashboard/manage/users` is admin-only.
- Map embeds point to Pasig, Philippines, not Korea.

Create a test booking with a real email address and confirm:

- Customer receives booking receipt.
- Staff/admin receives new booking alert.
- Approving/rejecting/cancelling sends the correct customer email.
- Booking reminder command can send for an approved booking starting within 10 minutes.

## Important Routes

```text
/register
/login
/bookings/create
/bookings/{booking}/reschedule
/bookings
/admin/login
/staff/login
/admin/dashboard
/staff/dashboard
/dashboard/manage/bookings
/dashboard/manage/calendar
/dashboard/manage/classrooms
/dashboard/manage/packages
/dashboard/manage/users
```

## Production Safety Checklist

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL` is the real HTTPS domain.
- `.env` is not committed.
- Gmail App Password is used for `MAIL_PASSWORD`.
- `storage` and `bootstrap/cache` are writable.
- Cron scheduler is installed and logging to `storage/logs/scheduler.log`.
- Default seeded password `password` is changed or seeded test accounts are removed.
- Database is backed up regularly.
- HTTPS is enabled.
- `php artisan optimize:*` caches are rebuilt after deploy.

## Troubleshooting

Clear caches after changing `.env`:

```bash
php artisan optimize:clear
php artisan config:cache
```

If email does not send:

```bash
php artisan config:clear
php artisan tinker
```

Then in Tinker:

```php
Mail::raw('SMTP test from ICAN Eduspace production', fn ($m) => $m->to('REPLACE_WITH_TEST_RECIPIENT')->subject('SMTP test'));
```

Check:

- Gmail App Password has no spaces.
- `MAIL_HOST=smtp.gmail.com`
- `MAIL_PORT=587`
- `MAIL_SCHEME=smtp`
- Server allows outbound SMTP on port 587.
- `storage/logs/laravel.log` for errors.

If reminders do not send:

```bash
crontab -l
php artisan schedule:list
tail -n 100 storage/logs/scheduler.log
tail -n 100 storage/logs/laravel.log
```

Reminders only send for approved bookings where `starts_at` is between now and 10 minutes from now, and where `reminder_sent_at` is still empty.

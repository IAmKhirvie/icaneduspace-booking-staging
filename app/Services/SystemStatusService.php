<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingNotification;
use App\Models\Classroom;
use App\Models\ServicePackage;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Throwable;

class SystemStatusService
{
    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $database = $this->databaseStatus();
        $mail = $this->mailStatus();
        $backup = $this->backupStatus();
        $scheduler = $this->schedulerStatus();

        $checks = [
            $database['ok'],
            $mail['ok'],
            $backup['ok'],
            $scheduler['ok'],
        ];

        return [
            'overall' => in_array(false, $checks, true) ? 'attention' : 'ok',
            'generated_at' => now()->toDateTimeString(),
            'app' => [
                'name' => config('app.name'),
                'environment' => app()->environment(),
                'url' => config('app.url'),
                'debug' => (bool) config('app.debug'),
            ],
            'database' => $database,
            'mail' => $mail,
            'backup' => $backup,
            'scheduler' => $scheduler,
            'counts' => $this->counts(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function publicHealth(): array
    {
        $database = $this->databaseStatus();

        return [
            'status' => $database['ok'] ? 'ok' : 'error',
            'database' => $database['ok'] ? 'ok' : 'error',
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function databaseStatus(): array
    {
        $connection = config('database.default');
        $database = (string) config("database.connections.{$connection}.database");
        $resolvedPath = $this->resolveDatabasePath($database);

        try {
            $quickCheck = null;

            if ($connection === 'sqlite') {
                $result = DB::selectOne('PRAGMA quick_check');
                $quickCheck = $result ? (array) $result : [];
                $quickCheck = $quickCheck ? (string) reset($quickCheck) : null;
            } else {
                DB::selectOne('select 1');
            }

            return [
                'ok' => $connection !== 'sqlite' || $quickCheck === 'ok',
                'connection' => $connection,
                'database' => $database,
                'resolved_path' => $resolvedPath,
                'exists' => $resolvedPath ? is_file($resolvedPath) : null,
                'writable' => $resolvedPath ? is_writable($resolvedPath) && is_writable(dirname($resolvedPath)) : null,
                'size' => $resolvedPath && is_file($resolvedPath) ? filesize($resolvedPath) : null,
                'size_label' => $resolvedPath && is_file($resolvedPath) ? $this->formatBytes(filesize($resolvedPath)) : null,
                'quick_check' => $quickCheck,
                'message' => $connection === 'sqlite' ? 'SQLite quick check completed.' : 'Database connection completed.',
            ];
        } catch (Throwable $exception) {
            return [
                'ok' => false,
                'connection' => $connection,
                'database' => $database,
                'resolved_path' => $resolvedPath,
                'exists' => $resolvedPath ? is_file($resolvedPath) : null,
                'writable' => $resolvedPath ? is_writable($resolvedPath) && is_writable(dirname($resolvedPath)) : null,
                'size' => null,
                'size_label' => null,
                'quick_check' => null,
                'message' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function mailStatus(): array
    {
        $mailer = (string) config('mail.default');

        return [
            'ok' => ! in_array($mailer, ['log', 'array'], true),
            'mailer' => $mailer,
            'from' => config('mail.from.address'),
            'message' => in_array($mailer, ['log', 'array'], true)
                ? 'Mail is not configured for real delivery.'
                : 'Mail is configured for outbound delivery.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function backupStatus(): array
    {
        $latest = $this->latestBackupManifest();

        if (! $latest) {
            return [
                'ok' => false,
                'latest' => null,
                'message' => 'No production backup snapshot found in this app copy.',
            ];
        }

        $ageHours = abs((int) now()->diffInHours($latest['created_at_carbon']));

        return [
            'ok' => $ageHours <= 24,
            'latest' => [
                'snapshot_id' => $latest['snapshot_id'] ?? null,
                'label' => $latest['label'] ?? null,
                'created_at' => $latest['created_at_carbon']->toDateTimeString(),
                'age_hours' => $ageHours,
                'health_status' => $latest['health_status'] ?? null,
                'database_backup' => $latest['database_backup'] ?? null,
            ],
            'message' => $ageHours <= 24
                ? 'Latest backup is recent.'
                : 'Latest backup is older than 24 hours.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function schedulerStatus(): array
    {
        $log = storage_path('logs/scheduler.log');

        if (! is_file($log)) {
            return [
                'ok' => false,
                'log_path' => $log,
                'last_run_at' => null,
                'age_minutes' => null,
                'message' => 'Scheduler log not found.',
            ];
        }

        $lastModified = filemtime($log);
        $ageMinutes = $lastModified ? abs((int) now()->diffInMinutes(date('Y-m-d H:i:s', $lastModified))) : null;

        return [
            'ok' => $ageMinutes !== null && $ageMinutes <= 15,
            'log_path' => $log,
            'last_run_at' => $lastModified ? date('Y-m-d H:i:s', $lastModified) : null,
            'age_minutes' => $ageMinutes,
            'message' => $ageMinutes !== null && $ageMinutes <= 15
                ? 'Scheduler ran recently.'
                : 'Scheduler has not run recently.',
        ];
    }

    /**
     * @return array<string, int>
     */
    private function counts(): array
    {
        return [
            'bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', BookingService::STATUS_PENDING)->count(),
            'approved_bookings' => Booking::where('status', BookingService::STATUS_APPROVED)->count(),
            'awaiting_payment' => Booking::whereIn('status', BookingService::ACTIVE_STATUSES)
                ->where(function ($query) {
                    $query
                        ->where(function ($reservation) {
                            $reservation->where('reservation_fee_amount', '>', 0)
                                ->whereNull('reservation_fee_paid_at');
                        })
                        ->orWhere(function ($full) {
                            $full->where('payment_scope', Booking::PAYMENT_SCOPE_FULL)
                                ->whereNull('full_payment_paid_at');
                        });
                })
                ->count(),
            'today_active_bookings' => Booking::whereIn('status', BookingService::ACTIVE_STATUSES)
                ->whereDate('starts_at', now()->toDateString())
                ->count(),
            'notification_failures' => BookingNotification::whereIn('status', [
                BookingNotification::STATUS_FAILED,
                BookingNotification::STATUS_SKIPPED,
            ])->count(),
            'classrooms' => Classroom::count(),
            'packages' => ServicePackage::count(),
            'users' => User::count(),
        ];
    }

    private function resolveDatabasePath(string $database): ?string
    {
        if ($database === ':memory:' || $database === '') {
            return null;
        }

        if (str_starts_with($database, '/app/')) {
            return base_path(substr($database, 5));
        }

        if (str_starts_with($database, '/')) {
            return $database;
        }

        return base_path($database);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function latestBackupManifest(): ?array
    {
        $files = glob(storage_path('app/backups/production/*/manifest.json')) ?: [];

        if ($files === []) {
            return null;
        }

        usort($files, fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));

        foreach ($files as $file) {
            $data = json_decode((string) file_get_contents($file), true);

            if (! is_array($data) || empty($data['created_at'])) {
                continue;
            }

            $data['created_at_carbon'] = CarbonImmutable::createFromFormat('Ymd\THis\Z', $data['created_at'], 'UTC')
                ->setTimezone(config('app.timezone'));

            return $data;
        }

        return null;
    }

    private function formatBytes(int|false $bytes): string
    {
        if ($bytes === false) {
            return 'unknown';
        }

        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1048576) {
            return round($bytes / 1024, 1).' KB';
        }

        return round($bytes / 1048576, 1).' MB';
    }
}

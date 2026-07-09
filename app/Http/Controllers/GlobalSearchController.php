<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingNotification;
use App\Models\Classroom;
use App\Models\ServicePackage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GlobalSearchController extends Controller
{
    private const BOOKING_RESULT_LIMIT = 8;
    private const CATALOG_RESULT_LIMIT = 6;
    private const FUZZY_CANDIDATE_LIMIT = 1000;

    public function __invoke(Request $request): View
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
        ]);

        $q = trim((string) ($data['q'] ?? ''));
        $user = $request->user();
        $canManage = $user?->hasAnyRole(['admin', 'super_admin', 'staff']) ?? false;
        $isAdmin = $user?->hasAnyRole(['admin', 'super_admin']) ?? false;
        $results = $q === ''
            ? $this->emptyResults()
            : $this->resultsFor($q, (int) $user->id, $canManage, $isAdmin);

        return view('search.index', [
            'query' => $q,
            'canManage' => $canManage,
            'correction' => $q === '' ? null : $this->correctionFor($results, $q, $canManage, $isAdmin),
            'results' => $results,
        ]);
    }

    public function suggestions(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
        ]);

        $q = trim((string) ($data['q'] ?? ''));

        if (mb_strlen($q) < 2) {
            return response()->json([
                'query' => $q,
                'correction' => null,
                'sections' => [],
            ]);
        }

        $user = $request->user();
        $canManage = $user?->hasAnyRole(['admin', 'super_admin', 'staff']) ?? false;
        $isAdmin = $user?->hasAnyRole(['admin', 'super_admin']) ?? false;
        $results = $this->resultsFor($q, (int) $user->id, $canManage, $isAdmin);

        return response()->json([
            'query' => $q,
            'correction' => $this->correctionFor($results, $q, $canManage, $isAdmin),
            'sections' => $this->suggestionSections(
                $results,
                $q,
                $canManage,
                $isAdmin,
            ),
        ]);
    }

    /**
     * @return array<string, Collection<int, mixed>>
     */
    private function emptyResults(): array
    {
        return [
            'bookings' => collect(),
            'rooms' => collect(),
            'packages' => collect(),
            'notifications' => collect(),
            'users' => collect(),
        ];
    }

    /**
     * @return array<string, Collection<int, mixed>>
     */
    private function resultsFor(string $q, int $userId, bool $canManage, bool $isAdmin): array
    {
        $id = ltrim($q, '#');
        $prefix = Str::lower($q).'%';
        $contains = '%'.Str::lower($q).'%';

        $bookingScope = Booking::query()
            ->with(['classroom', 'servicePackage'])
            ->when(! $canManage, fn ($query) => $query->where('user_id', $userId));

        $bookings = (clone $bookingScope)
            ->where(function ($query) use ($id, $prefix, $contains) {
                if (ctype_digit($id)) {
                    $query->orWhere('id', (int) $id);
                }

                $query
                    ->orWhereRaw('lower(customer_name) like ?', [$prefix])
                    ->orWhereRaw('lower(contact) like ?', [$prefix])
                    ->orWhereRaw('lower(organization) like ?', [$contains])
                    ->orWhereRaw('lower(purpose) like ?', [$contains])
                    ->orWhereRaw('lower(customer_notes) like ?', [$contains])
                    ->orWhereHas('classroom', fn ($room) => $room->whereRaw('lower(name) like ?', [$contains]))
                    ->orWhereHas('servicePackage', fn ($package) => $package->whereRaw('lower(name) like ?', [$contains]));
            })
            ->latest('id')
            ->limit(self::BOOKING_RESULT_LIMIT)
            ->get();
        $bookings = $this->withFuzzyResults(
            $bookings,
            (clone $bookingScope)->latest('id')->limit(self::FUZZY_CANDIDATE_LIMIT)->get(),
            $q,
            fn (Booking $booking): array => [
                $booking->id,
                $booking->customer_name,
                $booking->contact,
                $booking->organization,
                $booking->purpose,
                $booking->customer_notes,
                $booking->classroom?->name,
                $booking->servicePackage?->name,
            ],
            self::BOOKING_RESULT_LIMIT,
        );

        $roomScope = Classroom::query()
            ->when(! $canManage, fn ($query) => $query->where('is_active', true));

        $rooms = (clone $roomScope)
            ->where(function ($query) use ($prefix, $contains) {
                $query
                    ->whereRaw('lower(name) like ?', [$prefix])
                    ->orWhereRaw('lower(location) like ?', [$contains])
                    ->orWhereRaw('lower(room_number) like ?', [$contains])
                    ->orWhereRaw('lower(floor) like ?', [$contains])
                    ->orWhereRaw('lower(description) like ?', [$contains]);
            })
            ->orderBy('name')
            ->limit(self::CATALOG_RESULT_LIMIT)
            ->get();
        $rooms = $this->withFuzzyResults(
            $rooms,
            (clone $roomScope)->orderBy('name')->limit(self::FUZZY_CANDIDATE_LIMIT)->get(),
            $q,
            fn (Classroom $room): array => [
                $room->name,
                $room->location,
                $room->room_number,
                $room->floor,
                $room->description,
            ],
            self::CATALOG_RESULT_LIMIT,
        );

        $packageScope = ServicePackage::query()
            ->when(! $canManage, fn ($query) => $query->where('is_active', true));

        $packages = (clone $packageScope)
            ->where(function ($query) use ($prefix, $contains) {
                $query
                    ->whereRaw('lower(name) like ?', [$prefix])
                    ->orWhereRaw('lower(description) like ?', [$contains]);
            })
            ->orderBy('base_price')
            ->limit(self::CATALOG_RESULT_LIMIT)
            ->get();
        $packages = $this->withFuzzyResults(
            $packages,
            (clone $packageScope)->orderBy('base_price')->limit(self::FUZZY_CANDIDATE_LIMIT)->get(),
            $q,
            fn (ServicePackage $package): array => [
                $package->name,
                $package->description,
            ],
            self::CATALOG_RESULT_LIMIT,
        );

        $notificationScope = BookingNotification::query()
            ->with('booking.classroom');

        $notifications = $canManage
            ? (clone $notificationScope)
                ->where(function ($query) use ($id, $prefix, $contains) {
                    if (ctype_digit($id)) {
                        $query
                            ->orWhere('id', (int) $id)
                            ->orWhere('booking_id', (int) $id);
                    }

                    $query
                        ->orWhereRaw('lower(recipient) like ?', [$prefix])
                        ->orWhereRaw('lower(subject) like ?', [$contains])
                        ->orWhereRaw('lower(message) like ?', [$contains])
                        ->orWhereRaw('lower(notification_type) like ?', [$contains]);
                })
                ->latest('id')
                ->limit(self::CATALOG_RESULT_LIMIT)
                ->get()
            : collect();
        $notifications = $canManage
            ? $this->withFuzzyResults(
                $notifications,
                (clone $notificationScope)->latest('id')->limit(self::FUZZY_CANDIDATE_LIMIT)->get(),
                $q,
                fn (BookingNotification $notification): array => [
                    $notification->id,
                    $notification->booking_id,
                    $notification->recipient,
                    $notification->subject,
                    $notification->message,
                    $notification->notification_type,
                    $notification->booking?->customer_name,
                    $notification->booking?->classroom?->name,
                ],
                self::CATALOG_RESULT_LIMIT,
            )
            : $notifications;

        $userScope = User::query();

        $users = $isAdmin
            ? (clone $userScope)
                ->where(function ($query) use ($prefix, $contains) {
                    $query
                        ->whereRaw('lower(name) like ?', [$prefix])
                        ->orWhereRaw('lower(email) like ?', [$contains]);
                })
                ->orderBy('name')
                ->limit(self::CATALOG_RESULT_LIMIT)
                ->get()
            : collect();
        $users = $isAdmin
            ? $this->withFuzzyResults(
                $users,
                (clone $userScope)->orderBy('name')->limit(self::FUZZY_CANDIDATE_LIMIT)->get(),
                $q,
                fn (User $resultUser): array => [
                    $resultUser->name,
                    $resultUser->email,
                ],
                self::CATALOG_RESULT_LIMIT,
            )
            : $users;

        return compact('bookings', 'rooms', 'packages', 'notifications', 'users');
    }

    /**
     * @template TModel of object
     *
     * @param  Collection<int, TModel>  $exact
     * @param  Collection<int, TModel>  $candidates
     * @param  callable(TModel): array<int, mixed>  $fields
     * @return Collection<int, TModel>
     */
    private function withFuzzyResults(Collection $exact, Collection $candidates, string $q, callable $fields, int $limit): Collection
    {
        if ($exact->count() >= $limit) {
            return $exact->values();
        }

        $existingIds = $exact
            ->map(fn (object $item): ?string => isset($item->id) ? (string) $item->id : null)
            ->filter()
            ->all();

        $fuzzy = $candidates
            ->reject(fn (object $item): bool => isset($item->id) && in_array((string) $item->id, $existingIds, true))
            ->map(function (object $item) use ($fields, $q): ?array {
                $score = $this->fuzzyScore($q, $fields($item));

                return $score === null ? null : [
                    'item' => $item,
                    'score' => $score,
                ];
            })
            ->filter()
            ->sortBy('score')
            ->map(fn (array $match): object => $match['item'])
            ->take($limit - $exact->count());

        return $exact->concat($fuzzy)->values();
    }

    /**
     * @param  array<int, mixed>  $values
     */
    private function fuzzyScore(string $needle, array $values): ?int
    {
        $queryTokens = collect($this->searchTokens($needle))
            ->filter(fn (string $token): bool => strlen($token) >= 3)
            ->values();

        if ($queryTokens->isEmpty()) {
            return null;
        }

        $candidateTokens = collect($values)
            ->flatMap(fn (mixed $value): array => $this->searchTokens((string) $value))
            ->unique()
            ->values();

        if ($candidateTokens->isEmpty()) {
            return null;
        }

        $score = 0;

        foreach ($queryTokens as $queryToken) {
            $best = null;

            foreach ($candidateTokens as $candidateToken) {
                $distance = $this->tokenDistance($queryToken, $candidateToken);

                if ($distance === null) {
                    continue;
                }

                $best = $best === null ? $distance : min($best, $distance);
            }

            if ($best === null) {
                return null;
            }

            $score += $best;
        }

        return $score;
    }

    /**
     * @return list<string>
     */
    private function searchTokens(string $value): array
    {
        $normalized = Str::lower(Str::ascii($value));

        return collect(preg_split('/[^a-z0-9]+/', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [])
            ->filter(fn (string $token): bool => strlen($token) >= 2)
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private function displayTokens(string $value): array
    {
        $normalized = Str::ascii($value);

        return collect(preg_split('/[^A-Za-z0-9]+/', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [])
            ->filter(fn (string $token): bool => strlen($token) >= 2)
            ->values()
            ->all();
    }

    private function tokenDistance(string $queryToken, string $candidateToken): ?int
    {
        if (ctype_digit($queryToken) || ctype_digit($candidateToken)) {
            return $queryToken === $candidateToken ? 0 : null;
        }

        if ($candidateToken === $queryToken || str_contains($candidateToken, $queryToken)) {
            return 0;
        }

        $limit = $this->distanceLimit($queryToken);

        if ($limit === 0 || abs(strlen($candidateToken) - strlen($queryToken)) > $limit) {
            $relaxedLimit = $this->relaxedNameDistanceLimit($queryToken, $candidateToken);

            if ($relaxedLimit === null || abs(strlen($candidateToken) - strlen($queryToken)) > $relaxedLimit) {
                return null;
            }
        }

        $distance = levenshtein($queryToken, $candidateToken);

        if ($distance <= $limit) {
            return $distance;
        }

        $relaxedLimit = $this->relaxedNameDistanceLimit($queryToken, $candidateToken);

        return $relaxedLimit !== null && $distance <= $relaxedLimit
            ? $distance + 1
            : null;
    }

    private function distanceLimit(string $token): int
    {
        $length = strlen($token);

        return match (true) {
            $length <= 2 => 0,
            $length <= 4 => 1,
            $length <= 8 => 2,
            default => 3,
        };
    }

    private function relaxedNameDistanceLimit(string $queryToken, string $candidateToken): ?int
    {
        $length = max(strlen($queryToken), strlen($candidateToken));

        if ($length < 6 || $length > 12) {
            return null;
        }

        if ($this->commonPrefixLength($queryToken, $candidateToken) < 2) {
            return null;
        }

        return $length <= 8 ? 4 : 5;
    }

    private function commonPrefixLength(string $left, string $right): int
    {
        $length = min(strlen($left), strlen($right));
        $count = 0;

        for ($index = 0; $index < $length; $index += 1) {
            if ($left[$index] !== $right[$index]) {
                break;
            }

            $count += 1;
        }

        return $count;
    }

    /**
     * @param  array<string, Collection<int, mixed>>  $results
     * @return array{query: string, label: string, url: string}|null
     */
    private function correctionFor(array $results, string $q, bool $canManage, bool $isAdmin): ?array
    {
        $queryTokens = collect($this->displayTokens($q))
            ->filter(fn (string $token): bool => strlen($token) >= 3)
            ->values();

        if ($queryTokens->isEmpty()) {
            return null;
        }

        $candidateTokens = collect($this->correctionSources($results, $canManage, $isAdmin))
            ->flatMap(function (array $source): Collection {
                return collect($source['values'])
                    ->flatMap(fn (mixed $value): array => $this->displayTokens((string) $value))
                    ->map(fn (string $token): array => [
                        'display' => $token,
                        'normalized' => Str::lower(Str::ascii($token)),
                        'label' => $source['label'],
                    ]);
            })
            ->filter(fn (array $candidate): bool => strlen($candidate['normalized']) >= 3)
            ->unique(fn (array $candidate): string => $candidate['normalized'])
            ->values();

        if ($candidateTokens->isEmpty()) {
            return null;
        }

        $correctedTokens = [];
        $changed = false;
        $label = null;

        foreach ($queryTokens as $queryToken) {
            $normalizedQueryToken = Str::lower(Str::ascii($queryToken));

            if ($candidateTokens->contains(fn (array $candidate): bool => $candidate['normalized'] === $normalizedQueryToken || str_contains($candidate['normalized'], $normalizedQueryToken))) {
                $correctedTokens[] = $queryToken;
                continue;
            }

            $best = $candidateTokens
                ->map(function (array $candidate) use ($normalizedQueryToken): ?array {
                    $distance = $this->tokenDistance($normalizedQueryToken, $candidate['normalized']);

                    if ($distance === null || $distance === 0) {
                        return null;
                    }

                    return [
                        'candidate' => $candidate,
                        'distance' => $distance,
                    ];
                })
                ->filter()
                ->sortBy('distance')
                ->first();

            if (! $best) {
                return null;
            }

            $correctedTokens[] = $best['candidate']['display'];
            $label ??= $best['candidate']['label'];
            $changed = true;
        }

        if (! $changed) {
            return null;
        }

        $correctedQuery = implode(' ', $correctedTokens);

        return [
            'query' => $correctedQuery,
            'label' => $label ?: $correctedQuery,
            'url' => route('search.index', ['q' => $correctedQuery]),
        ];
    }

    /**
     * @param  array<string, Collection<int, mixed>>  $results
     * @return list<array{label: string, values: array<int, mixed>}>
     */
    private function correctionSources(array $results, bool $canManage, bool $isAdmin): array
    {
        $sources = [];

        foreach ($results['bookings'] as $booking) {
            $sources[] = [
                'label' => $booking->customer_name,
                'values' => [
                    $booking->customer_name,
                    $booking->contact,
                    $booking->organization,
                    $booking->purpose,
                    $booking->classroom?->name,
                    $booking->servicePackage?->name,
                ],
            ];
        }

        foreach ($results['rooms'] as $room) {
            $sources[] = [
                'label' => $room->name,
                'values' => [
                    $room->name,
                    $room->location,
                    $room->room_number,
                    $room->floor,
                ],
            ];
        }

        foreach ($results['packages'] as $package) {
            $sources[] = [
                'label' => $package->name,
                'values' => [
                    $package->name,
                    $package->description,
                ],
            ];
        }

        if ($canManage) {
            foreach ($results['notifications'] as $notification) {
                $sources[] = [
                    'label' => $notification->subject ?: __('Booking notification'),
                    'values' => [
                        $notification->recipient,
                        $notification->subject,
                        $notification->message,
                        $notification->notification_type,
                        $notification->booking?->customer_name,
                        $notification->booking?->classroom?->name,
                    ],
                ];
            }
        }

        if ($isAdmin) {
            foreach ($results['users'] as $resultUser) {
                $sources[] = [
                    'label' => $resultUser->name,
                    'values' => [
                        $resultUser->name,
                        $resultUser->email,
                    ],
                ];
            }
        }

        return $sources;
    }

    /**
     * @param  array<string, Collection<int, mixed>>  $results
     * @return list<array{key: string, title: string, items: list<array{id: string, label: string, meta: string, url: string}>}>
     */
    private function suggestionSections(array $results, string $q, bool $canManage, bool $isAdmin): array
    {
        $sections = [
            [
                'key' => 'bookings',
                'title' => __('Bookings'),
                'items' => $results['bookings']->take(4)->map(fn (Booking $booking): array => [
                    'id' => 'booking-'.$booking->id,
                    'label' => '#'.$booking->id.' '.$booking->customer_name,
                    'meta' => collect([
                        $booking->classroom?->name ?? __('No room'),
                        optional($booking->starts_at)->format('M d, Y H:i'),
                    ])->filter()->implode(' · '),
                    'url' => route('bookings.show', ['booking' => $booking, 'return' => route('search.index', ['q' => $q])]),
                ])->values()->all(),
            ],
            [
                'key' => 'rooms',
                'title' => __('Rooms'),
                'items' => $results['rooms']->take(4)->map(fn (Classroom $room): array => [
                    'id' => 'room-'.$room->id,
                    'label' => $room->name,
                    'meta' => collect([
                        $room->location,
                        $room->capacity ? __(':count seats', ['count' => $room->capacity]) : null,
                    ])->filter()->implode(' · '),
                    'url' => route('rooms.show', $room->slug),
                ])->values()->all(),
            ],
            [
                'key' => 'packages',
                'title' => __('Packages'),
                'items' => $results['packages']->take(4)->map(fn (ServicePackage $package): array => [
                    'id' => 'package-'.$package->id,
                    'label' => $package->name,
                    'meta' => Str::limit((string) $package->description, 72),
                    'url' => url('/#packages'),
                ])->values()->all(),
            ],
        ];

        if ($canManage) {
            $sections[] = [
                'key' => 'notifications',
                'title' => __('Notifications'),
                'items' => $results['notifications']->take(4)->map(fn (BookingNotification $notification): array => [
                    'id' => 'notification-'.$notification->id,
                    'label' => $notification->subject ?: __('Booking notification'),
                    'meta' => Str::limit((string) ($notification->message ?: class_basename($notification->notification_type)), 72),
                    'url' => route('manage.notifications.index', ['q' => $notification->booking_id ? '#'.$notification->booking_id : '#'.$notification->id]),
                ])->values()->all(),
            ];
        }

        if ($isAdmin) {
            $sections[] = [
                'key' => 'users',
                'title' => __('Users'),
                'items' => $results['users']->take(4)->map(fn (User $resultUser): array => [
                    'id' => 'user-'.$resultUser->id,
                    'label' => $resultUser->name,
                    'meta' => $resultUser->email,
                    'url' => route('manage.users.edit', $resultUser),
                ])->values()->all(),
            ];
        }

        return collect($sections)
            ->filter(fn (array $section): bool => count($section['items']) > 0)
            ->values()
            ->all();
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Laravel\Jetstream\Agent;
use Spatie\Activitylog\Models\Activity;

class SettingsController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        return view('profile.show', [
            'request' => $request,
            'user' => $user,
            'accountActivity' => $this->accountActivity($user),
            'accountSessions' => $this->accountSessions($request),
        ]);
    }

    private function accountActivity(User $user)
    {
        $bookingIds = Booking::where('user_id', $user->id)->pluck('id');

        return Activity::query()
            ->where(function ($query) use ($user, $bookingIds) {
                $query
                    ->where(function ($query) use ($user) {
                        $query
                            ->where('causer_type', User::class)
                            ->where('causer_id', $user->id);
                    })
                    ->orWhere(function ($query) use ($user) {
                        $query
                            ->where('subject_type', User::class)
                            ->where('subject_id', $user->id);
                    })
                    ->when($bookingIds->isNotEmpty(), function ($query) use ($bookingIds) {
                        $query->orWhere(function ($query) use ($bookingIds) {
                            $query
                                ->where('subject_type', Booking::class)
                                ->whereIn('subject_id', $bookingIds);
                        });
                    });
            })
            ->latest()
            ->limit(20)
            ->get();
    }

    private function accountSessions(Request $request)
    {
        if (config('session.driver') !== 'database') {
            return collect();
        }

        return collect(
            DB::connection(config('session.connection'))
                ->table(config('session.table', 'sessions'))
                ->where('user_id', $request->user()->getAuthIdentifier())
                ->orderByDesc('last_activity')
                ->limit(20)
                ->get()
        )->map(function ($session) use ($request) {
            $agent = tap(new Agent(), fn (Agent $agent) => $agent->setUserAgent($session->user_agent));
            $lastActiveAt = Carbon::createFromTimestamp($session->last_activity);

            return (object) [
                'agent' => $agent,
                'ip_address' => $session->ip_address,
                'is_current_device' => $session->id === $request->session()->getId(),
                'last_active' => $lastActiveAt->diffForHumans(),
                'last_active_at' => $lastActiveAt,
            ];
        });
    }
}

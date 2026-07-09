<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Booking');
    }

    public function view(AuthUser $authUser, Booking $booking): bool
    {
        if ($booking->user_id === $authUser->id) {
            return true;
        }

        if (method_exists($authUser, 'hasAnyRole') && $authUser->hasAnyRole(['super_admin', 'admin', 'staff'])) {
            return true;
        }

        return $authUser->can('View:Booking');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Booking');
    }

    public function update(AuthUser $authUser, Booking $booking): bool
    {
        return $authUser->can('Update:Booking');
    }

    public function delete(AuthUser $authUser, Booking $booking): bool
    {
        return $authUser->can('Delete:Booking');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Booking');
    }

    public function restore(AuthUser $authUser, Booking $booking): bool
    {
        return $authUser->can('Restore:Booking');
    }

    public function forceDelete(AuthUser $authUser, Booking $booking): bool
    {
        return $authUser->can('ForceDelete:Booking');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Booking');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Booking');
    }

    public function replicate(AuthUser $authUser, Booking $booking): bool
    {
        return $authUser->can('Replicate:Booking');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Booking');
    }

    /* ---------------- custom domain transitions ---------------- */

    public function cancel(AuthUser $user, Booking $booking): bool
    {
        if ($booking->isCancelled() || $booking->isRejected()) {
            return false;
        }

        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['super_admin', 'admin', 'staff'])) {
            return true;
        }

        return $booking->user_id === $user->id
            && in_array($booking->status, [BookingService::STATUS_PENDING, BookingService::STATUS_APPROVED], true);
    }

    public function approve(AuthUser $user, Booking $booking): bool
    {
        return method_exists($user, 'hasAnyRole')
            && $user->hasAnyRole(['super_admin', 'admin', 'staff'])
            && $booking->isPending();
    }

    public function reject(AuthUser $user, Booking $booking): bool
    {
        return method_exists($user, 'hasAnyRole')
            && $user->hasAnyRole(['super_admin', 'admin', 'staff'])
            && $booking->isPending();
    }

    public function reschedule(AuthUser $user, Booking $booking): bool
    {
        if ($booking->isCancelled() || $booking->isRejected()) {
            return false;
        }

        if ($booking->ends_at && $booking->ends_at->isPast()) {
            return false;
        }

        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['super_admin', 'admin', 'staff'])) {
            return true;
        }

        return $booking->user_id === $user->id
            && in_array($booking->status, [BookingService::STATUS_PENDING, BookingService::STATUS_APPROVED], true);
    }

    public function payment(AuthUser $user, Booking $booking): bool
    {
        if (! in_array($booking->status, BookingService::ACTIVE_STATUSES, true)) {
            return false;
        }

        if ($booking->reservation_fee_paid_at || $booking->full_payment_paid_at) {
            return false;
        }

        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['super_admin', 'admin', 'staff'])) {
            return true;
        }

        return $booking->user_id === $user->id;
    }
}

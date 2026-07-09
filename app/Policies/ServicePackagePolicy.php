<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ServicePackage;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServicePackagePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ServicePackage');
    }

    public function view(AuthUser $authUser, ServicePackage $servicePackage): bool
    {
        return $authUser->can('View:ServicePackage');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ServicePackage');
    }

    public function update(AuthUser $authUser, ServicePackage $servicePackage): bool
    {
        return $authUser->can('Update:ServicePackage');
    }

    public function delete(AuthUser $authUser, ServicePackage $servicePackage): bool
    {
        return $authUser->can('Delete:ServicePackage');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ServicePackage');
    }

    public function restore(AuthUser $authUser, ServicePackage $servicePackage): bool
    {
        return $authUser->can('Restore:ServicePackage');
    }

    public function forceDelete(AuthUser $authUser, ServicePackage $servicePackage): bool
    {
        return $authUser->can('ForceDelete:ServicePackage');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ServicePackage');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ServicePackage');
    }

    public function replicate(AuthUser $authUser, ServicePackage $servicePackage): bool
    {
        return $authUser->can('Replicate:ServicePackage');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ServicePackage');
    }

}
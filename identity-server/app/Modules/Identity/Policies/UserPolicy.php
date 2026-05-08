<?php

namespace App\Modules\Identity\Policies;

use App\Models\User;

/**
 * UserPolicy
 *
 * [FIX - Admin Bypass Removed]
 * Method before() yang sebelumnya memberi bypass total untuk admin dihapus.
 * Kini setiap action diperiksa melalui permission granular via role/permission system.
 *
 * Keuntungan:
 * - Admin hanya dapat melakukan action yang permission-nya memang diberikan di role mereka
 * - Permission granular menjadi bermakna dan dapat diaudit
 * - Konsisten dengan prinsip Least Privilege
 */
class UserPolicy
{
    /**
     * Determine whether the user can manage users (generic check for controller-level gate).
     */
    public function manage(User $user): bool
    {
        return $user->hasPermission('users.view')
            || $user->hasPermission('users.create')
            || $user->hasPermission('users.edit')
            || $user->hasPermission('users.delete');
    }

    /**
     * Determine whether the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('users.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->hasPermission('users.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('users.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->hasPermission('users.edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->hasPermission('users.delete');
    }
}

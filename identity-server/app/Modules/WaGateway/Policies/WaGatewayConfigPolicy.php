<?php

namespace App\Modules\WaGateway\Policies;

use App\Models\User;
use App\Modules\WaGateway\Models\WaGatewayConfig;
use Illuminate\Auth\Access\HandlesAuthorization;

class WaGatewayConfigPolicy
{
    use HandlesAuthorization;

    /*
    |--------------------------------------------------------------------------
    | WaGatewayConfig Policy
    |--------------------------------------------------------------------------
    | Super-admin: akses penuh ke semua config.
    | Admin / security-officer: hanya config milik sendiri (user_id == auth user).
    |
    | [FIX] Semua panggilan hasPermissionTo() diganti hasPermission()
    | karena User model hanya mengimplementasikan hasPermission().
    | hasPermissionTo() tidak ada dan akan menyebabkan error 500.
    */

    /**
     * Super-admin meng-override semua policy check.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('wa-gateway.view');
    }

    public function view(User $user, WaGatewayConfig $config): bool
    {
        return $user->hasPermission('wa-gateway.view')
            && $config->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('wa-gateway.create');
    }

    public function update(User $user, WaGatewayConfig $config): bool
    {
        return $user->hasPermission('wa-gateway.update')
            && $config->user_id === $user->id;
    }

    public function delete(User $user, WaGatewayConfig $config): bool
    {
        return $user->hasPermission('wa-gateway.delete')
            && $config->user_id === $user->id;
    }
}

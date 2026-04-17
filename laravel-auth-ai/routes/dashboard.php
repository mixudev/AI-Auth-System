<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Dashboard\DashboardController;
use App\Http\Controllers\Admin\Dashboard\UserManagementController;
use App\Http\Controllers\Admin\Dashboard\NotificationController;
use App\Http\Controllers\Admin\Dashboard\RoleManagementController;
use App\Http\Controllers\Admin\Dashboard\PermissionManagementController;

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'ensure.session.version', 'verify.fingerprint'])
    ->prefix('dashboard')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard Home
        |--------------------------------------------------------------------------
        */
        Route::get('/', [DashboardController::class, 'index'])
            ->middleware('permission:dashboard.view')
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Dashboard - User Management
        |--------------------------------------------------------------------------
        */
        Route::name('dashboard.users.')
            ->prefix('users')
            ->controller(UserManagementController::class)
            ->middleware('permission:users.view')
            ->group(function () {

                // Index
                Route::get('/', 'index')->name('index');

                // CRUD
                Route::post('/', 'store')->name('store')->middleware('permission:users.create');
                Route::put('/{user}', 'update')->name('update')->middleware('permission:users.edit');
                Route::delete('/{user}', 'destroy')->name('destroy')->middleware('permission:users.delete');

                // Account Controls
                Route::post('/{user}/block', 'block')->name('block')->middleware('permission:users.edit');
                Route::post('/{user}/unblock', 'unblock')->name('unblock')->middleware('permission:users.edit');
                Route::post('/{user}/reset-password', 'resetPassword')
                    ->name('reset-password')->middleware('permission:users.edit');

                // Bulk
                Route::post('/bulk', 'bulkAction')->name('bulk')->middleware('permission:users.edit');
            });

        /*
        |--------------------------------------------------------------------------
        | Dashboard - Notifications API
        |--------------------------------------------------------------------------
        */
        Route::name('dashboard.notifications.')
            ->prefix('api/notifications')
            ->controller(NotificationController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/read-all', 'markAsRead')->name('read-all');
                Route::post('/{notification}/read', 'markOneRead')->name('mark-read');
                Route::delete('/{notification}', 'delete')->name('delete');
            });

        Route::get('/notifications', [NotificationController::class, 'all'])
            ->name('dashboard.notifications.all');

        /*
        |--------------------------------------------------------------------------
        | Dashboard - Role Management
        |--------------------------------------------------------------------------
        */
        Route::name('dashboard.roles.')
            ->prefix('roles')
            ->controller(RoleManagementController::class)
            ->middleware('permission:roles.view')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create')->middleware('permission:roles.create');
                Route::post('/', 'store')->name('store')->middleware('permission:roles.create');
                Route::get('/{role}/edit', 'edit')->name('edit')->middleware('permission:roles.edit');
                Route::put('/{role}', 'update')->name('update')->middleware('permission:roles.edit');
                Route::delete('/{role}', 'destroy')->name('destroy')->middleware('permission:roles.delete');
                Route::get('/api/permissions', 'getPermissions')->name('api.permissions');
            });

        /*
        |--------------------------------------------------------------------------
        | Dashboard - Permission Management
        |--------------------------------------------------------------------------
        */
        Route::name('dashboard.permissions.')
            ->prefix('permissions')
            ->controller(PermissionManagementController::class)
            ->middleware('permission:permissions.view')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create')->middleware('permission:permissions.create');
                Route::post('/', 'store')->name('store')->middleware('permission:permissions.create');
                Route::get('/{permission}/edit', 'edit')->name('edit')->middleware('permission:permissions.edit');
                Route::put('/{permission}', 'update')->name('update')->middleware('permission:permissions.edit');
                Route::delete('/{permission}', 'destroy')->name('destroy')->middleware('permission:permissions.delete');
            });

        /*
        |--------------------------------------------------------------------------
        | Dashboard - Profile
        |--------------------------------------------------------------------------
        */
        Route::controller(\App\Http\Controllers\Admin\Dashboard\ProfileController::class)
            ->prefix('profile')
            ->name('dashboard.profile.')
            ->group(function () {
                // Satu route untuk semua panel (panel via ?panel=xxx)
                Route::get('/', 'show')->name('show');

                // Aksi form
                Route::post('/update', 'update')->name('update');
                Route::post('/password', 'updatePassword')->name('password');
                Route::post('/password/reset', 'requestPasswordReset')->name('password.reset_request');
                Route::post('/preferences', 'updatePreferences')->name('preferences.update');
                Route::delete('/devices/{device}', 'revokeDevice')->name('devices.revoke');

                // MFA (JSON API)
                Route::get('/mfa/setup', 'setupMfa')->name('mfa.setup');
                Route::post('/mfa/confirm', 'confirmMfa')->name('mfa.confirm');
                Route::post('/mfa/disable', 'disableMfa')->name('mfa.disable');
            });
    });
<?php

use App\Http\Controllers\Admin\Dashboard\DashboardController;
use App\Http\Controllers\Admin\Dashboard\NotificationController;
use App\Http\Controllers\Admin\Dashboard\ProfileController;
use App\Http\Controllers\Admin\Dashboard\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'ensure.session.version', 'verify.fingerprint', 'role:super-admin,admin'])
    ->prefix('dashboard')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])
            ->middleware('permission:dashboard.view')
            ->name('dashboard');

        Route::name('dashboard.users.')
            ->prefix('users')
            ->controller(UserManagementController::class)
            ->middleware('permission:users.view')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store')->middleware('permission:users.create');
                Route::put('/{user}', 'update')->name('update')->middleware('permission:users.edit');
                Route::delete('/{user}', 'destroy')->name('destroy')->middleware('permission:users.delete');
                Route::post('/{user}/block', 'block')->name('block')->middleware('permission:users.edit');
                Route::post('/{user}/unblock', 'unblock')->name('unblock')->middleware('permission:users.edit');
                Route::post('/{user}/reset-password', 'resetPassword')->name('reset-password')->middleware('permission:users.edit');
                Route::post('/bulk', 'bulkAction')->name('bulk')->middleware('permission:users.edit');
            });

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

        Route::controller(ProfileController::class)
            ->prefix('profile')
            ->name('dashboard.profile.')
            ->group(function () {
                Route::get('/', 'show')->name('show');
                Route::post('/update', 'update')->name('update');
                Route::post('/password', 'updatePassword')->name('password');
                Route::post('/password/reset', 'requestPasswordReset')->name('password.reset_request');
                Route::post('/preferences', 'updatePreferences')->name('preferences.update');
                Route::delete('/devices/{device}', 'revokeDevice')->name('devices.revoke');
                Route::get('/mfa/setup', 'setupMfa')->name('mfa.setup');
                Route::post('/mfa/confirm', 'confirmMfa')->name('mfa.confirm');
                Route::post('/mfa/disable', 'disableMfa')->name('mfa.disable');
            });
    });

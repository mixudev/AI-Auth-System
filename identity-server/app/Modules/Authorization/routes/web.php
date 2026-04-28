<?php

use App\Modules\Authorization\Controllers\AccessManagementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authorization Module Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'ensure.session.version', 'verify.fingerprint', 'role:admin,super-admin'])->group(function () {
    
    Route::prefix('dashboard')->group(function () {
        
        // Access Management (Unified)
        Route::name('dashboard.access-management.')
            ->prefix('access-management')
            ->controller(AccessManagementController::class)
            ->middleware('permission:roles.view')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/users/search', 'searchUsers')->name('users.search');
                Route::post('/assign', 'assignRoles')->name('assign');
                Route::get('/roles/create', 'createRole')->name('roles.create')->middleware('permission:roles.create');
                Route::post('/roles', 'storeRole')->name('roles.store')->middleware('permission:roles.create');
                Route::get('/roles/{role}/edit', 'editRole')->name('roles.edit')->middleware('permission:roles.edit');
                Route::put('/roles/{role}', 'updateRole')->name('roles.update')->middleware('permission:roles.edit');
                Route::delete('/roles/{role}', 'destroyRole')->name('roles.destroy')->middleware('permission:roles.delete');
            });
    });
});

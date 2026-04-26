<?php

use App\Modules\Settings\Controllers\Admin\ConfigurationController;
use Illuminate\Support\Facades\Route;

/**
 * Settings Module Web Routes
 */
Route::middleware(['web', 'auth'])->prefix('admin/settings')->name('settings.')->group(function () {
    
    // Consolidated System Configurations
    Route::get('/configurations', [ConfigurationController::class, 'index'])->name('configurations.index');
    Route::post('/configurations', [ConfigurationController::class, 'update'])->name('configurations.update');
    Route::post('/configurations/test-mail', [ConfigurationController::class, 'testMail'])->name('configurations.test-mail');
});

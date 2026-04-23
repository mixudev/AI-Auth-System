<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Entry Point
|--------------------------------------------------------------------------
|
| File ini hanya sebagai entry point yang me-load routes dari masing-masing
| modul. Logika routing sesungguhnya ada di dalam modul.
|
*/

// ── Authentication Module API Routes ────────────────────────────────────
require app_path('Modules/Authentication/routes/api.php');

// WhatsApp Gateway Routes
use App\Http\Controllers\WhatsAppController;
Route::post('/whatsapp/send', [WhatsAppController::class, 'send']);



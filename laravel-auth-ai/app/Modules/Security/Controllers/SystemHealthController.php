<?php

namespace App\Modules\Security\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class SystemHealthController extends Controller
{
    /**
     * Get system health status snapshot.
     * Logic for checks has been moved to App\Console\Commands\CheckSystemHealthCommand
     * to avoid performance bottlenecks.
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $data = Cache::get('system_health_snapshot');

        if (!$data) {
            // Trigger manual check only if snapshot is missing
            \Illuminate\Support\Facades\Artisan::call('app:check-system-health');
            $data = Cache::get('system_health_snapshot');
        }

        return response()->json($data);
    }
}

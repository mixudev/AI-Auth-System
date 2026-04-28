<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * Middleware ini memeriksa apakah user memiliki permission yang diperlukan.
     * Penggunaan: ->middleware('permission:users.view,users.edit')
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        // Jika user tidak terautentikasi
        if (!$request->user()) {
            abort(401, 'Unauthorized');
        }

        // Periksa apakah user memiliki salah satu dari permissions yang diminta
        $userHasPermission = false;
        foreach ($permissions as $permission) {
            // Centralize authorization via Gate/User::can()
            if ($request->user()->can(trim($permission))) {
                $userHasPermission = true;
                break;
            }
        }

        if (!$userHasPermission) {
            abort(403, 'Forbidden - Insufficient permissions');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * Middleware ini memeriksa apakah user memiliki role yang diperlukan.
     * Penggunaan: ->middleware('role:super-admin,admin')
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Jika user tidak terautentikasi
        if (!$request->user()) {
            abort(401, 'Unauthorized');
        }

        // Periksa apakah user memiliki salah satu dari roles yang diminta
        $userHasRole = false;
        foreach ($roles as $role) {
            if ($request->user()->hasRole(trim($role))) {
                $userHasRole = true;
                break;
            }
        }

        if (!$userHasRole) {
            abort(403, 'Forbidden - Insufficient role');
        }

        return $next($request);
    }
}

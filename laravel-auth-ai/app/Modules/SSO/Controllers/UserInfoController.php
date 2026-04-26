<?php

namespace App\Modules\SSO\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserInfoController
{
    /**
     * GET /api/user
     *
     * Mengembalikan profil user yang terautentikasi via Passport Bearer token.
     * Format response sesuai yang diharapkan oleh package mixu/sso-auth client.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'id'           => $user->id,
            'name'         => $user->name,
            'email'        => $user->email,
            'avatar'       => $user->avatar_url ?? null,
            'is_active'    => (bool) $user->is_active,
            // Roles dari sistem Authorization (slug-based)
            'roles'        => $user->roles()->pluck('slug')->toArray(),
            // Access areas dari sistem SSO (slug-based, tabel terpisah)
            'access_areas' => $user->accessAreas()->pluck('slug')->toArray(),
        ]);
    }
}

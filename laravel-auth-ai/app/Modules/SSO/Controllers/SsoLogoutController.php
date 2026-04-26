<?php

namespace App\Modules\SSO\Controllers;

use App\Modules\SSO\Services\GlobalLogoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SsoLogoutController
{
    public function __construct(
        private readonly GlobalLogoutService $globalLogout
    ) {}

    /**
     * POST /api/logout
     *
     * Logout session SSO:
     * 1. Revoke Passport access token yang dipakai request ini
     * 2. Dispatch global logout webhook ke semua client apps
     * 3. Return success response ke client yang memanggil
     */
    public function handle(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $userId = $user->id;
        $email  = $user->email;

        try {
            // Revoke current Passport token
            $token = $request->user()->token();
            if ($token) {
                $token->revoke();
                Log::info('SSO logout: token revoked.', [
                    'user_id'  => $userId,
                    'email'    => $email,
                    'token_id' => $token->id,
                ]);
            }

            // Dispatch global logout ke semua client (async via queue)
            $this->globalLogout->dispatch($userId, $email);

        } catch (\Throwable $e) {
            Log::error('SSO logout error.', [
                'user_id' => $userId,
                'email'   => $email,
                'error'   => $e->getMessage(),
            ]);

            // Tetap return success agar client bisa lanjut hapus session lokalnya
        }

        return response()->json([
            'success'         => true,
            'message'         => 'Successfully logged out from SSO Server',
            'session_cleared' => true,
        ]);
    }
}

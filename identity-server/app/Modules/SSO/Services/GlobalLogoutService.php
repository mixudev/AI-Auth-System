<?php

namespace App\Modules\SSO\Services;

use App\Modules\SSO\Jobs\SendGlobalLogoutWebhookJob;
use App\Modules\SSO\Models\SsoClient;
use Illuminate\Support\Facades\Log;

class GlobalLogoutService
{
    /**
     * Dispatch webhook global logout ke semua registered client yang aktif.
     *
     * @param int    $userId  ID user yang logout
     * @param string $email   Email user yang logout
     */
    public function dispatch(int $userId, string $email): void
    {
        $clients = SsoClient::active()
            ->whereNotNull('webhook_url')
            ->whereNotNull('webhook_secret')
            ->get();

        if ($clients->isEmpty()) {
            Log::debug('SSO global logout: no active clients with webhook configured.', [
                'user_id' => $userId,
                'email'   => $email,
            ]);
            return;
        }

        foreach ($clients as $client) {
            SendGlobalLogoutWebhookJob::dispatch($client, $userId, $email);
        }

        Log::info('SSO global logout dispatched.', [
            'user_id'       => $userId,
            'email'         => $email,
            'client_count'  => $clients->count(),
        ]);
    }
}

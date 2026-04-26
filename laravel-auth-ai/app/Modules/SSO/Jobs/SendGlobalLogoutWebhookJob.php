<?php

namespace App\Modules\SSO\Jobs;

use App\Modules\SSO\Models\SsoClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendGlobalLogoutWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan ulang jika gagal.
     */
    public int $tries = 3;

    /**
     * Timeout per request (detik).
     */
    public int $timeout = 10;

    public function __construct(
        public readonly SsoClient $client,
        public readonly int $userId,
        public readonly string $email,
    ) {}

    public function handle(): void
    {
        $payload = json_encode([
            'event'   => 'global_logout',
            'user_id' => $this->userId,
            'email'   => $this->email,
        ]);

        $signature = hash_hmac('sha256', $payload, $this->client->webhook_secret);

        try {
            $response = Http::withHeaders([
                'X-SSO-Signature' => $signature,
                'Content-Type'    => 'application/json',
                'Accept'          => 'application/json',
            ])
            ->timeout($this->timeout)
            ->post($this->client->webhook_url, json_decode($payload, true));

            if ($response->successful()) {
                Log::info('SSO global logout webhook sent.', [
                    'client'  => $this->client->name,
                    'url'     => $this->client->webhook_url,
                    'user_id' => $this->userId,
                    'email'   => $this->email,
                    'status'  => $response->status(),
                ]);
            } else {
                Log::warning('SSO global logout webhook failed.', [
                    'client'  => $this->client->name,
                    'url'     => $this->client->webhook_url,
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('SSO global logout webhook exception.', [
                'client' => $this->client->name,
                'url'    => $this->client->webhook_url,
                'error'  => $e->getMessage(),
            ]);

            // Re-throw agar queue mencoba ulang
            throw $e;
        }
    }
}

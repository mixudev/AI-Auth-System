<?php

namespace App\Modules\WaGateway\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    public function sendMessage(string $target, string $message, array $options = []): array
    {
        $provider = strtolower((string) config('wa_gateway.provider', 'fonnte'));
        $providerConfig = config("wa_gateway.providers.$provider", []);

        if (empty($providerConfig)) {
            return ['status' => false, 'reason' => "Provider WA '$provider' tidak ditemukan"];
        }

        if ($provider !== 'fonnte') {
            return ['status' => false, 'reason' => "Provider WA '$provider' belum diimplementasikan di WhatsAppService"];
        }

        $target = $this->normalizeTarget($target, (string) ($providerConfig['default_country_code'] ?? '62'));

        $data = array_merge([
            'target' => $target,
            'message' => $message,
        ], $options);

        if (!array_key_exists('delay', $data)) {
            $data['delay'] = config('wa_gateway.guardrail.default_random_delay', '3-8');
        }

        if (!array_key_exists('countryCode', $data)) {
            $data['countryCode'] = $providerConfig['default_country_code'] ?? '62';
        }

        $headerName = $providerConfig['token_header'] ?? 'Authorization';
        $tokenPrefix = $providerConfig['token_prefix'] ?? '';
        $token = $providerConfig['token'] ?? '';
        $baseUrl = $providerConfig['base_url'] ?? 'https://api.fonnte.com/send';
        $timeout = (int) ($providerConfig['timeout'] ?? 15);

        if (empty($token)) {
            return ['status' => false, 'reason' => "Token provider '$provider' belum dikonfigurasi"];
        }

        $response = Http::withHeaders([
            $headerName => $tokenPrefix . $token,
        ])
            ->timeout($timeout)
            ->asForm()
            ->post($baseUrl, $data);

        return $response->json() ?? ['status' => false, 'reason' => 'Empty response'];
    }

    protected function normalizeTarget(string $target, string $countryCode): string
    {
        $clean = preg_replace('/[^0-9]/', '', trim($target)) ?? '';

        if ($clean !== '' && str_starts_with($clean, '0')) {
            return $countryCode . substr($clean, 1);
        }

        return $clean;
    }
}

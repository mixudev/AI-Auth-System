<?php

namespace App\Modules\WaGateway\Services;

use App\Modules\WaGateway\Models\WaGatewayConfig;
use App\Modules\WaGateway\Models\WaGatewayTemplate;
use Illuminate\Support\Facades\Log;

class WaAlertService
{
    /**
     * Kirim alert keamanan kritis ke WhatsApp menggunakan template
     */
    public function sendCriticalAlert(
        string $eventType,
        string $message,
        array $metadata = []
    ): bool {
        try {
            $config = WaGatewayConfig::where('purpose', 'security')
                ->where('is_active', true)
                ->first();

            if (!$config) {
                // If no specific security config, try to use the global system config
                $service = new WaGatewayService();
            } else {
                $service = new WaGatewayService($config);
            }

            $template = WaGatewayTemplate::where('slug', 'security-alert')
                ->where('is_active', true)
                ->first();

            $data = array_merge([
                'event' => $message,
                'type' => strtoupper(str_replace('_', ' ', $eventType)),
                'time' => now()->format('Y-m-d H:i:s'),
                'ip' => $metadata['ip'] ?? request()->ip(),
                'user' => $metadata['user'] ?? 'Guest',
            ], $metadata);

            $alertMessage = $template
                ? $template->parse($data)
                : $this->formatDefaultAlert($eventType, $message, $data);

            $response = $service->sendMessage(
                $config ? $config->alert_phone_number : config('wa_gateway.providers.fonnte.default_country_code', '62') . '8123456789', // Fallback number if no config
                $alertMessage, 
                ['is_critical' => true]
            );

            return $response['status'] ?? false;
        } catch (\Exception $e) {
            Log::error('Failed to send critical WA alert: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fallback format jika template tidak ditemukan
     */
    protected function formatDefaultAlert(string $type, string $msg, array $data): string
    {
        $formatted = '[SECURITY ALERT - ' . $data['type'] . "]\n\n";
        $formatted .= 'Event: ' . $msg . "\n";
        $formatted .= 'Time: ' . $data['time'] . "\n";
        $formatted .= 'IP Address: ' . $data['ip'] . "\n";
        $formatted .= 'User: ' . $data['user'] . "\n\n";
        $formatted .= 'Periksa dashboard untuk detail.';

        return $formatted;
    }

    public function sendTestAlert(string $phoneNumber, ?WaGatewayConfig $config = null): bool
    {
        try {
            if (!$config) {
                $config = WaGatewayConfig::where('is_active', true)->first();
            }

            if (!$config) {
                return false;
            }

            $service = new WaGatewayService($config);
            $testMessage = '*Test Alert dari ' . config('app.name') . "*\n\n";
            $testMessage .= '*Waktu:* ' . now()->format('Y-m-d H:i:s') . "\n";
            $testMessage .= 'WA Gateway berfungsi dengan baik.';

            $response = $service->sendMessage($phoneNumber, $testMessage);
            return $response['status'] ?? false;
        } catch (\Exception $e) {
            Log::error('Failed to send test alert: ' . $e->getMessage());
            return false;
        }
    }
}

<?php

namespace App\Modules\WaGateway\Observers;

use App\Modules\Security\Models\SecurityNotification;
use App\Modules\WaGateway\Services\WaAlertService;
use Illuminate\Support\Facades\Log;

class SecurityNotificationObserver
{
    protected WaAlertService $alertService;

    public function __construct(WaAlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    /**
     * Handle the SecurityNotification "created" event.
     */
    public function created(SecurityNotification $notification): void
    {
        // Hanya kirim untuk event critical tertentu
        $criticalEvents = [
            "suspicious_login_detected",
            "multiple_failed_login_attempts",
            "ip_blacklist_triggered",
            "unauthorized_access_attempt",
            "session_hijacking_detected",
            "brute_force_attempt",
            "location_anomaly_detected",
            "device_mismatch_detected",
            "unusual_activity",
            "security_alert",
            "critical_event",
        ];

        // Check apakah ini event critical berdasarkan type atau event
        $isCritical = in_array($notification->event, $criticalEvents) 
                   || in_array($notification->type, $criticalEvents);

        if ($isCritical) {
            try {
                $metadata = [
                    "user_id" => $notification->user_id,
                    "ip_address" => $notification->ip_address,
                    "event_type" => $notification->event,
                ];

                // Kirim alert ke WA Gateway
                $this->alertService->sendCriticalAlert(
                    $notification->event,
                    $notification->message,
                    $metadata
                );

                Log::info("Critical alert sent to WA Gateway", [
                    "notification_id" => $notification->id,
                    "event" => $notification->event,
                ]);

            } catch (\Exception $e) {
                Log::error("Failed to send WA alert for notification", [
                    "notification_id" => $notification->id,
                    "error" => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Handle the SecurityNotification "updated" event.
     */
    public function updated(SecurityNotification $notification): void
    {
        // Optional: handle update events
    }

    /**
     * Handle the SecurityNotification "deleted" event.
     */
    public function deleted(SecurityNotification $notification): void
    {
        // Optional: handle delete events
    }
}

<?php

namespace App\Modules\AuditLog\Controllers\Admin;

use App\Modules\AuditLog\Models\AuditLog;
use App\Modules\Security\Models\LoginLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * LogCenterController
 * 
 * Pusat monitoring terpadu untuk seluruh log di sistem.
 * Menggabungkan Auth Logs (Login/Security) dan Audit Logs (Aktivitas Data) 
 * dalam satu interface yang mudah dipindah-pindah menggunakan tab.
 */
class LogCenterController
{
    /**
     * Tampilkan halaman pusat log.
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'auth'); 
        $search = $request->get('search');
        $status = $request->get('status');

        // 1. Fetch Auth Logs (LoginLog)
        $authQuery = LoginLog::with('user:id,name,email')
            ->when($tab === 'auth' && $search, function($q) use ($search) {
                $q->where('email_attempted', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            })
            ->when($tab === 'auth' && $status, function($q) use ($status) {
                $q->where('status', $status);
            });

        $authLogs = $authQuery->latest('occurred_at')
            ->paginate(15, ['*'], 'auth_page')
            ->appends(['tab' => 'auth', 'search' => $search, 'status' => $status]);

        // 2. Fetch Audit Logs (Activity)
        $auditQuery = AuditLog::with('user')
            ->when($tab === 'audit' && $search, function ($query) use ($search) {
                $query->where('event', 'like', "%{$search}%")
                      ->orWhere('ip_address', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($u) use ($search) {
                          $u->where('name', 'like', "%{$search}%");
                      });
            });

        $auditLogs = $auditQuery->latest()
            ->paginate(15, ['*'], 'audit_page')
            ->appends(['tab' => 'audit', 'search' => $search]);

        // 3. Stats for Auth Logs
        $stats = [
            'total'   => LoginLog::count(),
            'success' => LoginLog::where('status', 'success')->count(),
            'failed'  => LoginLog::where('status', 'failed')->count(),
            'blocked' => LoginLog::where('status', 'blocked')->count(),
            'otp'     => LoginLog::where('status', 'otp_required')->count(),
        ];

        return view('AuditLog::Admin.center', compact('authLogs', 'auditLogs', 'tab', 'stats'));
    }
}

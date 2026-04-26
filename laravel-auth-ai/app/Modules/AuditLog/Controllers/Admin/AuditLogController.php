<?php

namespace App\Modules\AuditLog\Controllers\Admin;

use App\Modules\AuditLog\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * AuditLogController
 * 
 * Pusat monitoring seluruh aktivitas sensitif di sistem.
 * Memungkinkan admin untuk melacak siapa yang melakukan perubahan data, kapan, dan dari mana.
 */
class AuditLogController
{
    /**
     * Tampilkan daftar seluruh log aktivitas dengan fitur paginasi dan pencarian.
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');

        $logs = AuditLog::with('user')
            ->when($search, function ($query) use ($search) {
                $query->where('event', 'like', "%{$search}%")
                      ->orWhere('ip_address', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($u) use ($search) {
                          $u->where('name', 'like', "%{$search}%");
                      });
            })
            ->latest()
            ->paginate(15);

        return view('AuditLog::Admin.index', compact('logs'));
    }

    /**
     * Tampilkan detail spesifik dari sebuah log aktivitas.
     * Berguna untuk melihat perbandingan data (Old Values vs New Values).
     * 
     * @param AuditLog $auditLog
     * @return View
     */
    public function show(AuditLog $auditLog): View
    {
        return view('AuditLog::Admin.show', compact('auditLog'));
    }
}

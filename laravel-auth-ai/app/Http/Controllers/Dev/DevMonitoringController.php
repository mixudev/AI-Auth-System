<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Models\IpBlacklist;
use App\Models\IpWhitelist;
use App\Models\LoginLog;
use App\Models\OtpVerification;
use App\Models\TrustedDevice;
use App\Models\User;
use App\Models\UserBlock;
use App\Services\BlockingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * DEV ONLY — Hapus atau proteksi controller ini sebelum production!
 *
 * Optimisasi untuk ratusan juta data:
 * - Cursor-based pagination (bukan offset) → O(log n) vs O(n)
 * - Selective column fetching (select spesifik, bukan SELECT *)
 * - Cache stats dengan TTL pendek (single-query aggregation)
 * - DB::raw COUNT untuk stats (hindari Eloquent overhead)
 * - Streaming CSV export via chunked query
 *
 * Rekomendasi index tambahan di migration:
 *   login_logs:         (id DESC), (decision, id DESC), (status, id DESC)
 *   otp_verifications:  (id DESC), (expires_at, verified_at)
 *   trusted_devices:    (id DESC), (is_revoked, id DESC)
 *   user_blocks:        (user_id, blocked_until)
 */
class DevMonitoringController extends Controller
{
    private const PAGE_SIZE    = 50;
    private const STATS_TTL    = 10;   // detik
    private const EXPORT_CHUNK = 1000;

    public function __construct(
        private readonly BlockingService $blockingService
    ) {}

    public function dashboard(): \Illuminate\View\View
    {
        return view('dev.monitoring');
    }

    // ── Stats ─────────────────────────────────────────────────────────────

    public function stats(): JsonResponse
    {
        $data = Cache::remember('dev_monitor_stats', self::STATS_TTL, function () {
            // Single aggregation query >> 9 query terpisah
            $row = DB::selectOne("
                SELECT
                    (SELECT COUNT(*) FROM users)                                                AS users,
                    (SELECT COUNT(*) FROM users WHERE is_active = 1)                           AS active_users,
                    (SELECT COUNT(*) FROM login_logs)                                          AS total_logs,
                    (SELECT COUNT(*) FROM login_logs WHERE decision = 'BLOCK')                 AS blocked_logs,
                    (SELECT COUNT(*) FROM otp_verifications
                        WHERE expires_at > NOW() AND verified_at IS NULL)                     AS active_otps,
                    (SELECT COUNT(*) FROM trusted_devices WHERE is_revoked = 0)                AS trusted_devices,
                    (SELECT COUNT(*) FROM ip_blacklists
                        WHERE blocked_until IS NULL OR blocked_until > NOW())                  AS ip_blacklisted,
                    (SELECT COUNT(*) FROM ip_whitelists)                                       AS ip_whitelisted,
                    (SELECT COUNT(*) FROM user_blocks
                        WHERE blocked_until IS NULL OR blocked_until > NOW())                  AS users_blocked
            ");
            return (array) $row;
        });

        return response()->json($data);
    }

    // ── OTP ───────────────────────────────────────────────────────────────

    public function otps(Request $request): JsonResponse
    {
        $cursor = $request->integer('cursor', 0);
        $status = $request->input('status');
        $search = $request->input('search');

        $now = now();

        $rows = OtpVerification::select([
                'otp_verifications.id',
                'otp_verifications.token',
                'otp_verifications.attempts',
                'otp_verifications.expires_at',
                'otp_verifications.verified_at',
                'otp_verifications.created_at',
                'users.name  as user_name',
                'users.email as user_email',
            ])
            ->join('users', 'users.id', '=', 'otp_verifications.user_id')
            ->when($cursor > 0, fn($q) => $q->where('otp_verifications.id', '<', $cursor))
            ->when($status === 'active',   fn($q) => $q->whereNull('verified_at')->where('expires_at', '>', $now))
            ->when($status === 'verified', fn($q) => $q->whereNotNull('verified_at'))
            ->when($status === 'expired',  fn($q) => $q->whereNull('verified_at')->where('expires_at', '<=', $now))
            ->when($search, fn($q) => $q->where(fn($sq) =>
                $sq->where('users.name',  'like', "%{$search}%")
                   ->orWhere('users.email','like', "%{$search}%")
            ))
            ->orderByDesc('otp_verifications.id')
            ->limit(self::PAGE_SIZE + 1)
            ->get();

        $hasMore = $rows->count() > self::PAGE_SIZE;
        if ($hasMore) $rows->pop();

        $data = $rows->map(fn($o) => [
            'id'          => $o->id,
            'user'        => $o->user_name,
            'email'       => $o->user_email,
            'otp_code'    => $o->token ? substr($o->token, 0, 20) . '…' : '—',
            'status'      => $o->verified_at ? 'verified'
                           : ($o->expires_at && $o->expires_at < $now ? 'expired' : 'active'),
            'attempts'    => $o->attempts ?? 0,
            'expires_at'  => $o->expires_at?->toDateTimeString(),
            'verified_at' => $o->verified_at?->toDateTimeString(),
            'created_at'  => $o->created_at->toDateTimeString(),
        ]);

        return response()->json([
            'data'        => $data,
            'next_cursor' => $hasMore ? $rows->last()->id : null,
            'has_more'    => $hasMore,
        ]);
    }

    // ── Login Logs ────────────────────────────────────────────────────────

    public function loginLogs(Request $request): JsonResponse
    {
        $cursor   = $request->integer('cursor', 0);
        $status   = $request->input('status');
        $decision = $request->input('decision');
        $search   = $request->input('search');

        $rows = LoginLog::select([
                'login_logs.id',
                'login_logs.ip_address',
                'login_logs.device_fingerprint',
                'login_logs.status',
                'login_logs.decision',
                'login_logs.risk_score',
                'login_logs.reason_flags',
                'login_logs.occurred_at',
                'login_logs.email_attempted',
                'users.name  as user_name',
                'users.email as user_email',
            ])
            ->leftJoin('users', 'users.id', '=', 'login_logs.user_id')
            ->when($cursor > 0, fn($q) => $q->where('login_logs.id', '<', $cursor))
            ->when($status,   fn($q) => $q->where('login_logs.status',   $status))
            ->when($decision, fn($q) => $q->where('login_logs.decision', strtoupper($decision)))
            ->when($search, fn($q) => $q->where(fn($sq) =>
                $sq->where('users.name',             'like', "%{$search}%")
                   ->orWhere('login_logs.ip_address','like', "%{$search}%")
                   ->orWhere('users.email',          'like', "%{$search}%")
            ))
            ->orderByDesc('login_logs.id')
            ->limit(self::PAGE_SIZE + 1)
            ->get();

        $hasMore = $rows->count() > self::PAGE_SIZE;
        if ($hasMore) $rows->pop();

        $data = $rows->map(fn($l) => [
            'id'           => $l->id,
            'user'         => $l->user_name ?? 'Unknown',
            'email'        => $l->user_email ?? $l->email_attempted ?? '—',
            'ip_address'   => $l->ip_address,
            'device_fp'    => $l->device_fingerprint ? substr($l->device_fingerprint, 0, 16) . '…' : '—',
            'status'       => $l->status,
            'decision'     => $l->decision ?? '—',
            'risk_score'   => $l->risk_score,
            'reason_flags' => $l->reason_flags,
            'occurred_at'  => $l->occurred_at->toDateTimeString(),
        ]);

        return response()->json([
            'data'        => $data,
            'next_cursor' => $hasMore ? $rows->last()->id : null,
            'has_more'    => $hasMore,
        ]);
    }

    // ── Trusted Devices ───────────────────────────────────────────────────

    public function trustedDevices(Request $request): JsonResponse
    {
        $cursor = $request->integer('cursor', 0);
        $status = $request->input('status');
        $search = $request->input('search');

        $rows = TrustedDevice::select([
                'trusted_devices.id',
                'trusted_devices.fingerprint_hash',
                'trusted_devices.device_label',
                'trusted_devices.ip_address',
                'trusted_devices.is_revoked',
                'trusted_devices.last_seen_at',
                'trusted_devices.trusted_until',
                'users.name  as user_name',
                'users.email as user_email',
            ])
            ->join('users', 'users.id', '=', 'trusted_devices.user_id')
            ->when($cursor > 0, fn($q) => $q->where('trusted_devices.id', '<', $cursor))
            ->when($status === 'trusted', fn($q) => $q->where('is_revoked', false))
            ->when($status === 'revoked', fn($q) => $q->where('is_revoked', true))
            ->when($search, fn($q) => $q->where(fn($sq) =>
                $sq->where('users.name',              'like', "%{$search}%")
                   ->orWhere('trusted_devices.ip_address','like', "%{$search}%")
                   ->orWhere('users.email',            'like', "%{$search}%")
            ))
            ->orderByDesc('trusted_devices.id')
            ->limit(self::PAGE_SIZE + 1)
            ->get();

        $hasMore = $rows->count() > self::PAGE_SIZE;
        if ($hasMore) $rows->pop();

        $data = $rows->map(fn($d) => [
            'id'            => $d->id,
            'user'          => $d->user_name,
            'email'         => $d->user_email,
            'fingerprint'   => substr($d->fingerprint_hash, 0, 16) . '…',
            'device_label'  => $d->device_label ?? '—',
            'ip_address'    => $d->ip_address,
            'is_revoked'    => $d->is_revoked,
            'last_seen'     => $d->last_seen_at?->toDateTimeString() ?? '—',
            'trusted_until' => $d->trusted_until?->toDateTimeString() ?? '—',
        ]);

        return response()->json([
            'data'        => $data,
            'next_cursor' => $hasMore ? $rows->last()->id : null,
            'has_more'    => $hasMore,
        ]);
    }

    // ── Users ─────────────────────────────────────────────────────────────

    public function users(Request $request): JsonResponse
    {
        $cursor = $request->integer('cursor', 0);
        $status = $request->input('status');
        $search = $request->input('search');

        $blockedIds = Cache::remember('dev_blocked_user_ids', 15, fn() =>
            UserBlock::active()->pluck('user_id')->flip()->toArray()
        );

        $rows = User::select([
                'id','name','email','is_active',
                'email_verified_at','last_login_at',
                'last_login_ip','created_at',
            ])
            ->withCount(['loginLogs as login_count', 'trustedDevices as device_count'])
            ->when($cursor > 0, fn($q) => $q->where('id', '<', $cursor))
            ->when($search, fn($q) => $q->where(fn($sq) =>
                $sq->where('name',  'like', "%{$search}%")
                   ->orWhere('email','like', "%{$search}%")
            ))
            ->when($status === 'blocked', fn($q) => $q->whereIn('id', array_keys($blockedIds)))
            ->when($status === 'ok',      fn($q) => $q->whereNotIn('id', array_keys($blockedIds)))
            ->orderByDesc('id')
            ->limit(self::PAGE_SIZE + 1)
            ->get();

        $hasMore = $rows->count() > self::PAGE_SIZE;
        if ($hasMore) $rows->pop();

        $data = $rows->map(fn($u) => [
            'id'            => $u->id,
            'name'          => $u->name,
            'email'         => $u->email,
            'is_active'     => $u->is_active,
            'verified'      => !is_null($u->email_verified_at),
            'last_login_at' => $u->last_login_at?->toDateTimeString() ?? '—',
            'last_login_ip' => $u->last_login_ip ?? '—',
            'login_count'   => $u->login_count,
            'device_count'  => $u->device_count,
            'is_blocked'    => isset($blockedIds[$u->id]),
        ]);

        return response()->json([
            'data'        => $data,
            'next_cursor' => $hasMore ? $rows->last()->id : null,
            'has_more'    => $hasMore,
        ]);
    }

    // ── IP Blacklist ───────────────────────────────────────────────────────

    public function ipBlacklist(Request $request): JsonResponse
    {
        $cursor = $request->integer('cursor', 0);
        $search = $request->input('search');

        $rows = IpBlacklist::select(['id','ip_address','reason','blocked_by','block_count','blocked_until','blocked_at'])
            ->when($cursor > 0, fn($q) => $q->where('id', '<', $cursor))
            ->when($search, fn($q) =>
                $q->where('ip_address','like',"%{$search}%")
                  ->orWhere('reason',   'like',"%{$search}%")
            )
            ->orderByDesc('id')
            ->limit(self::PAGE_SIZE + 1)
            ->get();

        $hasMore = $rows->count() > self::PAGE_SIZE;
        if ($hasMore) $rows->pop();

        $now  = now();
        $data = $rows->map(fn($r) => [
            'id'            => $r->id,
            'ip_address'    => $r->ip_address,
            'reason'        => $r->reason ?? '—',
            'blocked_by'    => $r->blocked_by,
            'block_count'   => $r->block_count,
            'blocked_until' => $r->blocked_until?->toDateTimeString() ?? 'Permanen',
            'blocked_at'    => $r->blocked_at->toDateTimeString(),
            'is_active'     => is_null($r->blocked_until) || $r->blocked_until > $now,
        ]);

        return response()->json([
            'data'        => $data,
            'next_cursor' => $hasMore ? $rows->last()->id : null,
            'has_more'    => $hasMore,
        ]);
    }

    public function addIpBlacklist(Request $request): JsonResponse
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason'     => 'nullable|string|max:255',
            'minutes'    => 'nullable|integer|min:1',
        ]);

        $record = $this->blockingService->blacklistIp(
            $request->ip_address,
            $request->reason ?? 'Manual block',
            $request->minutes,
            'admin'
        );

        Cache::forget('dev_monitor_stats');
        return response()->json(['success' => true, 'message' => "IP {$request->ip_address} ditambahkan ke blacklist.", 'data' => $record]);
    }

    public function removeIpBlacklist(string $ip): JsonResponse
    {
        $this->blockingService->unblacklistIp($ip);
        Cache::forget('dev_monitor_stats');
        return response()->json(['success' => true, 'message' => "IP {$ip} dihapus dari blacklist."]);
    }

    // ── IP Whitelist ───────────────────────────────────────────────────────

    public function ipWhitelist(Request $request): JsonResponse
    {
        $cursor = $request->integer('cursor', 0);
        $search = $request->input('search');

        $rows = IpWhitelist::select(['id','ip_address','label','added_by','created_at'])
            ->when($cursor > 0, fn($q) => $q->where('id', '<', $cursor))
            ->when($search, fn($q) =>
                $q->where('ip_address','like',"%{$search}%")
                  ->orWhere('label',    'like',"%{$search}%")
            )
            ->orderByDesc('id')
            ->limit(self::PAGE_SIZE + 1)
            ->get();

        $hasMore = $rows->count() > self::PAGE_SIZE;
        if ($hasMore) $rows->pop();

        $data = $rows->map(fn($r) => [
            'id'         => $r->id,
            'ip_address' => $r->ip_address,
            'label'      => $r->label ?? '—',
            'added_by'   => $r->added_by ?? '—',
            'created_at' => $r->created_at->toDateTimeString(),
        ]);

        return response()->json([
            'data'        => $data,
            'next_cursor' => $hasMore ? $rows->last()->id : null,
            'has_more'    => $hasMore,
        ]);
    }

    public function addIpWhitelist(Request $request): JsonResponse
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'label'      => 'nullable|string|max:255',
        ]);

        $record = $this->blockingService->whitelistIp(
            $request->ip_address,
            $request->label ?? '',
            'admin'
        );

        Cache::forget('dev_monitor_stats');
        return response()->json(['success' => true, 'message' => "IP {$request->ip_address} ditambahkan ke whitelist.", 'data' => $record]);
    }

    public function removeIpWhitelist(string $ip): JsonResponse
    {
        $this->blockingService->removeFromWhitelist($ip);
        Cache::forget('dev_monitor_stats');
        return response()->json(['success' => true, 'message' => "IP {$ip} dihapus dari whitelist."]);
    }

    // ── User Block ─────────────────────────────────────────────────────────

    public function unblockUser(int $userId): JsonResponse
    {
        $user = User::select('id', 'name')->find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User tidak ditemukan.'], 404);
        }

        DB::transaction(function () use ($user) {
            $this->blockingService->unblockUser($user->id, 'admin');
            Cache::forget("block_count:user:{$user->id}");

            LoginLog::where('user_id', $user->id)
                ->where('decision', LoginLog::DECISION_BLOCK)
                ->orderByDesc('occurred_at')
                ->limit(10)
                ->delete();

            TrustedDevice::where('user_id', $user->id)
                ->where('is_revoked', true)
                ->update(['is_revoked' => false]);

            $user->update(['is_active' => true]);
        });

        Cache::forget('dev_blocked_user_ids');
        Cache::forget('dev_monitor_stats');

        return response()->json(['success' => true, 'message' => "User {$user->name} berhasil di-unblock."]);
    }

    public function blockUserManual(Request $request, int $userId): JsonResponse
    {
        $request->validate(['minutes' => 'nullable|integer|min:1', 'reason' => 'nullable|string']);

        $user = User::select('id', 'name')->find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User tidak ditemukan.'], 404);
        }

        $this->blockingService->blockUser($userId, $request->reason ?? 'Manual block', $request->minutes, 'admin');

        Cache::forget('dev_blocked_user_ids');
        Cache::forget('dev_monitor_stats');

        return response()->json(['success' => true, 'message' => "User {$user->name} berhasil diblokir."]);
    }

    // ── Device ────────────────────────────────────────────────────────────

    public function revokeDevice(int $deviceId): JsonResponse
    {
        $device = TrustedDevice::select(['id','is_revoked','fingerprint_hash','user_id'])
            ->with('user:id,name')
            ->find($deviceId);

        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Device tidak ditemukan.'], 404);
        }

        $newState = !$device->is_revoked;
        $device->update(['is_revoked' => $newState]);
        Cache::forget("device_blocked:{$device->fingerprint_hash}");

        return response()->json([
            'success'    => true,
            'is_revoked' => $newState,
            'message'    => 'Device ' . ($newState ? 'direvoke' : 'di-restore') . " untuk user {$device->user?->name}.",
        ]);
    }

    // ── Export CSV (streaming untuk data besar) ───────────────────────────

    public function exportLogs(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $decision = $request->input('decision');

        return response()->streamDownload(function () use ($decision) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID','User','Email','IP','Status','Decision','Risk','Occurred At']);

            LoginLog::select([
                    'login_logs.id','login_logs.ip_address',
                    'login_logs.status','login_logs.decision',
                    'login_logs.risk_score','login_logs.occurred_at',
                    'users.name as user_name','users.email as user_email',
                ])
                ->leftJoin('users','users.id','=','login_logs.user_id')
                ->when($decision, fn($q) => $q->where('decision', strtoupper($decision)))
                ->orderByDesc('login_logs.id')
                ->chunk(self::EXPORT_CHUNK, function ($rows) use ($handle) {
                    foreach ($rows as $l) {
                        fputcsv($handle, [
                            $l->id, $l->user_name, $l->user_email,
                            $l->ip_address, $l->status, $l->decision,
                            $l->risk_score, $l->occurred_at,
                        ]);
                    }
                    ob_flush(); flush();
                });

            fclose($handle);
        }, 'login_logs_' . now()->format('Ymd_His') . '.csv');
    }
}
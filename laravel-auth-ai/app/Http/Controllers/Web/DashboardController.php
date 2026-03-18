<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $aiOnline = $this->checkAiStatus();  

        $recentLogs = LoginLog::where('user_id', $user->id)
            ->latest('occurred_at')
            ->take(5)
            ->get();

        $stats = [
            'total_logins'  => LoginLog::where('user_id', $user->id)->where('decision', 'ALLOW')->count(),
            'blocked_count' => LoginLog::where('user_id', $user->id)->where('decision', 'BLOCK')->count(),
            'otp_count'     => LoginLog::where('user_id', $user->id)->where('decision', 'OTP')->count(),
            'last_login'    => LoginLog::where('user_id', $user->id)->where('decision', 'ALLOW')
                                ->latest('occurred_at')
                                ->first()?->occurred_at,
        ];

        return view('dashboard.index', compact('user', 'recentLogs', 'stats', 'aiOnline'));
    }

    public function auditLog(Request $request)
    {
        $user = Auth::user();

        $logs = LoginLog::where('user_id', $user->id)
            ->when($request->filter, function ($q) use ($request) {
                $q->where('decision', strtoupper($request->filter));
            })
            ->latest('occurred_at')
            ->paginate(15);

        return view('dashboard.audit-log', compact('logs'));
    }

    private function checkAiStatus(): bool
    {
        try {
            $response = Http::timeout(2)->get('http://fastapi-risk:8000/health');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

}
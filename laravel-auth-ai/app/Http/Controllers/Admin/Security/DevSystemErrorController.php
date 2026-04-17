<?php

namespace App\Http\Controllers\Admin\Security;

use App\Http\Controllers\Controller;
use App\Models\SecurityNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DevSystemErrorController extends Controller
{
    /**
     * Ambil data log kesalahan sistem (Security Notifications).
     */
    public function index(Request $request): JsonResponse
    {
        abort_unless(app()->environment(['local', 'development', 'testing']), 403, 'Akses ditolak. Fitur ini hanya tersedia dalam mode developer.');

        $cursor = $request->integer('cursor', 0);
        $search = $request->string('search');

        $query = SecurityNotification::query()
            ->where(function($q) {
                $q->where('type', 'error')
                  ->orWhere('type', 'warning')
                  ->orWhere('event', 'like', 'auth.system%');
            })
            ->when($search, function ($q, $search) {
                $q->where(function($qq) use ($search) {
                    $qq->where('title', 'like', "%{$search}%")
                       ->orWhere('message', 'like', "%{$search}%")
                       ->orWhere('ip_address', 'like', "%{$search}%");
                });
            })
            ->latest('id');

        // Cursor-based pagination logic (match existing dev monitoring SPA)
        if ($cursor > 0) {
            $query->where('id', '<', $cursor);
        }

        $items = $query->limit(21)->get();
        $hasMore = $items->count() > 20;

        if ($hasMore) {
            $items = $items->take(20);
            $nextCursor = $items->last()->id;
        } else {
            $nextCursor = 0;
        }

        return response()->json([
            'data'        => $items->map(fn($item) => $this->transform($item)),
            'has_more'    => $hasMore,
            'next_cursor' => $nextCursor,
        ]);
    }

    private function transform(SecurityNotification $item): array
    {
        return [
            'id'         => $item->id,
            'type'       => $item->type,
            'event'      => $item->event,
            'title'      => $item->title,
            'message'    => $item->message,
            'meta'       => $item->meta,
            'ip_address' => $item->ip_address,
            'user_agent' => $item->user_agent,
            'occurred_at'=> $item->created_at->format('Y-m-d H:i:s'),
            'time_ago'   => $item->created_at->diffForHumans(),
        ];
    }
}

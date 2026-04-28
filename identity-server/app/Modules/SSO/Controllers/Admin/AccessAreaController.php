<?php

namespace App\Modules\SSO\Controllers\Admin;

use App\Models\User;
use App\Modules\SSO\Models\AccessArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AccessAreaController
{
    /**
     * Daftar semua access areas.
     */
    public function index(): View
    {
        $areas = AccessArea::withCount('users')->latest()->paginate(20);

        return view('admin.sso.access-areas.index', compact('areas'));
    }

    /**
     * Simpan area baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:100|alpha_dash|unique:access_areas,slug',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ]);

        $area = AccessArea::create([
            'name'        => $validated['name'],
            'slug'        => $validated['slug'] ?? null, // auto-generate kalau kosong
            'description' => $validated['description'] ?? null,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        Log::info('SSO Access Area created.', ['slug' => $area->slug]);

        return redirect()
            ->route('sso.access-areas.index')
            ->with('success', "Access area \"{$area->name}\" berhasil dibuat.");
    }

    /**
     * Form assign users ke area. Mendukung paginasi ganda dan pencarian.
     */
    public function edit(Request $request, AccessArea $accessArea): View
    {
        $searchAvail = $request->input('search_avail');
        $searchAssign = $request->input('search_assign');

        // 1. Ambil ID user yang sudah di-assign untuk pengecualian di tabel Available
        $assignedIds = $accessArea->users()->pluck('users.id');

        // 2. Available Users (Belum di-assign)
        $availableUsers = User::select('id', 'name', 'email')
            ->whereNotIn('id', $assignedIds)
            ->when($searchAvail, function($q) use ($searchAvail) {
                $q->where(function($sq) use ($searchAvail) {
                    $sq->where('name', 'like', "%{$searchAvail}%")
                       ->orWhere('email', 'like', "%{$searchAvail}%");
                });
            })
            ->orderBy('name')
            ->paginate(10, ['*'], 'avail_page')
            ->appends(['search_avail' => $searchAvail, 'search_assign' => $searchAssign]);

        // 3. Assigned Users (Sudah di-assign)
        $assignedUsers = $accessArea->users()
            ->select('users.id', 'users.name', 'users.email')
            ->when($searchAssign, function($q) use ($searchAssign) {
                $q->where(function($sq) use ($searchAssign) {
                    $sq->where('name', 'like', "%{$searchAssign}%")
                       ->orWhere('email', 'like', "%{$searchAssign}%");
                });
            })
            ->orderBy('name')
            ->paginate(10, ['*'], 'assign_page')
            ->appends(['search_avail' => $searchAvail, 'search_assign' => $searchAssign]);

        return view('admin.sso.access-areas.edit', compact('accessArea', 'availableUsers', 'assignedUsers'));
    }

    /**
     * Update area info dasar.
     */
    public function update(Request $request, AccessArea $accessArea): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => "nullable|string|max:100|alpha_dash|unique:access_areas,slug,{$accessArea->id}",
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ]);

        $accessArea->update([
            'name'        => $validated['name'],
            'slug'        => $validated['slug'] ?? $accessArea->slug,
            'description' => $validated['description'] ?? null,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('sso.access-areas.index')
            ->with('success', "Access area \"{$accessArea->name}\" berhasil diperbarui.");
    }

    /**
     * Hapus area.
     */
    public function destroy(AccessArea $accessArea): RedirectResponse
    {
        $name = $accessArea->name;
        $accessArea->users()->detach(); // Hapus pivot dulu
        $accessArea->delete();

        Log::info('SSO Access Area deleted.', ['name' => $name]);

        return redirect()
            ->route('sso.access-areas.index')
            ->with('success', "Access area \"{$name}\" berhasil dihapus.");
    }

    /**
     * Assign users ke area (Bulk Attach).
     */
    public function assignToUser(Request $request, AccessArea $accessArea): RedirectResponse
    {
        $validated = $request->validate([
            'user_ids'    => 'required|array',
            'user_ids.*'  => 'exists:users,id',
        ]);

        $accessArea->users()->attach($validated['user_ids']);

        Log::info('Users assigned to SSO Access Area.', [
            'area_id' => $accessArea->id,
            'user_ids' => $validated['user_ids'],
        ]);

        return back()->with('success', count($validated['user_ids']) . " pengguna berhasil ditambahkan ke area.");
    }

    /**
     * Revoke users dari area (Bulk Detach).
     */
    public function revokeUsers(Request $request, AccessArea $accessArea): RedirectResponse
    {
        $validated = $request->validate([
            'user_ids'    => 'required|array',
            'user_ids.*'  => 'exists:users,id',
        ]);

        $accessArea->users()->detach($validated['user_ids']);

        Log::info('Users revoked from SSO Access Area.', [
            'area_id' => $accessArea->id,
            'user_ids' => $validated['user_ids'],
        ]);

        return back()->with('success', count($validated['user_ids']) . " pengguna berhasil dicabut dari area.");
    }
}

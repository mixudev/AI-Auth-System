<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drop kolom is_admin dari tabel users.
 *
 * Authorization terpusat menggunakan role/permission system (tabel user_role,
 * roles, role_permission, permissions). Kolom boolean is_admin tidak lagi
 * diperlukan dan menimbulkan risiko authorization yang tidak konsisten
 * jika tidak sinkron dengan role yang dimiliki user.
 *
 * Setelah migrasi ini, gunakan:
 * - $user->hasRole('admin') atau $user->hasRole('super-admin')
 * - $user->isAdmin() — method helper di User model yang sudah pure-role-based
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'is_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_admin');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'is_admin')) {
            Schema::table('users', function (Blueprint $table) {
                // Restore kolom dengan default false agar rollback aman
                $table->boolean('is_admin')->default(false)->after('is_active');
            });
        }
    }
};

<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiExceptionHandler
{
    /*
    |--------------------------------------------------------------------------
    | Handler terpusat untuk mengonversi exception menjadi respons JSON
    | yang konsisten dan ramah pengguna.
    |
    | Stack trace dan detail teknis TIDAK pernah dikembalikan ke klien
    | di environment production — hanya dicatat di log.
    |--------------------------------------------------------------------------
    */

    public static function render(\Throwable $e): JsonResponse
    {
        // -- Exception validasi Laravel
        if ($e instanceof ValidationException) {
            return response()->json([
                'message'    => 'Data yang dikirimkan tidak valid.',
                'error_code' => 'VALIDATION_FAILED',
                'errors'     => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // -- Exception autentikasi
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'message'    => 'Anda harus login untuk mengakses resource ini.',
                'error_code' => 'UNAUTHENTICATED',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // -- Exception model tidak ditemukan
        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'message'    => 'Data yang diminta tidak ditemukan.',
                'error_code' => 'NOT_FOUND',
            ], Response::HTTP_NOT_FOUND);
        }

        // -- Exception HTTP generik (abort(), response 403, 404, dsb.)
        if ($e instanceof HttpException) {
            return response()->json([
                'message'    => $e->getMessage() ?: 'Terjadi kesalahan pada request Anda.',
                'error_code' => 'HTTP_ERROR',
            ], $e->getStatusCode());
        }

        // -- Exception tidak terduga: catat di log, jangan bocorkan detail ke klien
        Log::error('Exception tidak tertangani', [
            'exception' => get_class($e),
            'message'   => $e->getMessage(),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
        ]);

        return response()->json([
            'message'    => 'Terjadi kesalahan internal. Tim kami telah diberitahu.',
            'error_code' => 'INTERNAL_ERROR',
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

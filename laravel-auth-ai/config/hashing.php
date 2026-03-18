<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Algoritma Hashing Password Default
    |--------------------------------------------------------------------------
    | Menggunakan Argon2id sebagai algoritma hashing yang direkomendasikan
    | oleh OWASP untuk penyimpanan password modern.
    |
    | Argon2id menggabungkan ketahanan terhadap serangan GPU (Argon2d)
    | dan serangan side-channel (Argon2i).
    |--------------------------------------------------------------------------
    */

    'driver' => 'argon2id',

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Argon2id
    |--------------------------------------------------------------------------
    | memory  : Jumlah memori yang digunakan (dalam KiB). Nilai lebih tinggi
    |           membuat brute-force lebih mahal. Minimum rekomendasi OWASP: 19456
    | threads : Jumlah thread paralel untuk hashing
    | time    : Jumlah iterasi (time cost). Nilai lebih tinggi = lebih lambat
    |--------------------------------------------------------------------------
    */

    'argon' => [
        'memory'  => env('HASH_MEMORY', 65536),   // 64 MB — sesuaikan dengan kapasitas server
        'threads' => env('HASH_THREADS', 1),
        'time'    => env('HASH_TIME', 4),
    ],

];

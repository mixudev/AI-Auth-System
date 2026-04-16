<x-email-base-text heading="Selamat Datang di {{ config('app.name') }}">

Halo {{ $userName }},

Akun Anda di {{ config('app.name') }} telah berhasil dibuat.

DETAIL AKUN
───────────
Nama          : {{ $userName }}
Email         : {{ $userEmail }}
Paket         : {{ $plan }}
Bergabung     : {{ $createdAt }}

Masuk ke akun Anda:
{{ $loginUrl }}

Jika Anda memerlukan bantuan, kunjungi:
{{ config('app.url') }}/help

</x-email-base-text>

<x-email.base-text heading="Verifikasi Email Anda">

Halo {{ $userName }},

Kami menerima permintaan untuk memverifikasi alamat email Anda
di {{ config('app.name') }}.

Tautan Verifikasi:
{{ $actionUrl }}

Tautan ini berlaku selama {{ $expiresIn ?? '24 jam' }}.

Jika Anda tidak merasa membuat permintaan ini, abaikan email ini.

</x-email.base-text>

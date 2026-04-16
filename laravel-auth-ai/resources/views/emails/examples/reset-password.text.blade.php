<x-email-base-text heading="Reset Password">

Halo {{ $userName }},

Kami menerima permintaan untuk mengatur ulang kata sandi akun Anda
di {{ config('app.name') }}.

Tautan Reset Password (berlaku {{ $expiresIn }}):
{{ $actionUrl }}

@if($ipAddress)
Permintaan dikirim dari IP: {{ $ipAddress }}
@endif

Jika Anda tidak meminta reset password, abaikan email ini.
Kata sandi Anda tidak akan berubah.

</x-email-base-text>

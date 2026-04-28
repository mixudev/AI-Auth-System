<x-email.base
    subject="Verifikasi Alamat Email Anda"
    heading="Verifikasi email Anda.">

  <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
            font-size:15px;color:#57534e;line-height:1.75;margin:0 0 16px 0;">
    Halo {{ $userName }},
  </p>

  <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
            font-size:15px;color:#57534e;line-height:1.75;margin:0 0 16px 0;">
    Kami menerima permintaan untuk memverifikasi alamat email yang terkait dengan
    akun <strong style="color:#1c1917;font-weight:600;">{{ config('app.name') }}</strong> Anda.
    Klik tombol di bawah untuk menyelesaikan verifikasi.
  </p>

  <x-email.button url="{{ $actionUrl }}" label="Verifikasi Email Saya" />

  <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
            font-size:13px;color:#a8a29e;line-height:1.6;margin:0 0 16px 0;">
    Tautan ini berlaku selama
    <strong style="color:#78716c;">{{ $expiresIn ?? '24 jam' }}</strong>.
    Setelah kedaluwarsa, Anda perlu meminta tautan baru.
  </p>

  <x-email.divider />

  <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
            font-size:12px;color:#a8a29e;line-height:1.7;margin:0 0 8px 0;">
    Jika tombol tidak berfungsi, salin tautan berikut ke browser Anda:
  </p>
  <p style="font-family:'Courier New',Courier,monospace;
            font-size:12px;color:#78716c;line-height:1.6;margin:0 0 20px 0;
            word-break:break-all;">
    <a href="{{ $actionUrl }}" style="color:#44403c;text-decoration:underline;">
      {{ $actionUrl }}
    </a>
  </p>

  <x-email.divider style="space" spacing="8" />

  <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
            font-size:13px;color:#a8a29e;line-height:1.6;margin:0;">
    Jika Anda tidak merasa membuat permintaan ini, abaikan email ini.
  </p>

  <x-slot:footer>
    <x-email.footer
      customNote="Email ini dikirim secara otomatis. Mohon jangan membalas email ini." />
  </x-slot:footer>

</x-email.base>
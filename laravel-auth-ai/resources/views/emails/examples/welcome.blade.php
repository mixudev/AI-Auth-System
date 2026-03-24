<x-email.base
    subject="Selamat Datang di {{ config('app.name') }}"
    heading="Selamat datang.">

  <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
            font-size:15px;color:#57534e;line-height:1.75;margin:0 0 16px 0;">
    Halo {{ $userName }},
  </p>

  <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
            font-size:15px;color:#57534e;line-height:1.75;margin:0 0 24px 0;">
    Akun Anda di <strong style="color:#1c1917;">{{ config('app.name') }}</strong>
    telah berhasil dibuat. Berikut adalah ringkasan detail akun Anda.
  </p>

  {{-- Info table --}}
  <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation"
         style="border:1px solid #e7e5e4;border-radius:2px;margin-bottom:24px;">
    <tr>
      <td style="padding:0 20px;">

        <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
          <tr>
            <td width="40%" style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
                font-size:13px;color:#a8a29e;padding:13px 0;border-bottom:1px solid #f5f5f4;">
              Nama
            </td>
            <td width="60%" align="right"
                style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
                       font-size:13px;color:#1c1917;font-weight:500;padding:13px 0;
                       border-bottom:1px solid #f5f5f4;">
              {{ $userName }}
            </td>
          </tr>
        </table>

        <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
          <tr>
            <td width="40%" style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
                font-size:13px;color:#a8a29e;padding:13px 0;border-bottom:1px solid #f5f5f4;">
              Email
            </td>
            <td width="60%" align="right"
                style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
                       font-size:13px;color:#1c1917;font-weight:500;padding:13px 0;
                       border-bottom:1px solid #f5f5f4;word-break:break-all;">
              {{ $userEmail }}
            </td>
          </tr>
        </table>

        <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
          <tr>
            <td width="40%" style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
                font-size:13px;color:#a8a29e;padding:13px 0;">
              Bergabung
            </td>
            <td width="60%" align="right"
                style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
                       font-size:13px;color:#1c1917;font-weight:500;padding:13px 0;">
              {{ $createdAt ?? now()->translatedFormat('d F Y') }}
            </td>
          </tr>
        </table>

      </td>
    </tr>
  </table>

  <x-email.button url="{{ $loginUrl }}" label="Masuk ke Akun Anda" />

  <x-email.divider />

  <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
            font-size:13px;color:#a8a29e;line-height:1.6;margin:0;">
    Jika Anda memerlukan bantuan, balas email ini atau kunjungi
    <a href="{{ config('app.url') }}/help"
       style="color:#78716c;text-decoration:underline;">pusat bantuan</a> kami.
  </p>

</x-email.base>
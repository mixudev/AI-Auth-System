<x-email.base
    subject="Permintaan Reset Password"
    heading="Reset password Anda.">

  <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
            font-size:15px;color:#57534e;line-height:1.75;margin:0 0 16px 0;">
    Halo {{ $userName }},
  </p>

  <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
            font-size:15px;color:#57534e;line-height:1.75;margin:0 0 16px 0;">
    Kami menerima permintaan untuk mengatur ulang kata sandi akun Anda.
    Gunakan tombol di bawah untuk melanjutkan.
  </p>

  <x-email.button url="{{ $actionUrl }}" label="Reset Password" />

  <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
            font-size:13px;color:#a8a29e;line-height:1.6;margin:0 0 24px 0;">
    Tautan ini hanya dapat digunakan satu kali dan akan kedaluwarsa dalam
    <strong style="color:#78716c;">{{ $expiresIn ?? '60 menit' }}</strong>.
  </p>

  <x-email.divider />

  {{-- Security box --}}
  <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation"
         style="background-color:#fafaf9;border:1px solid #e7e5e4;border-radius:2px;
                margin-bottom:20px;">
    <tr>
      <td style="padding:16px 20px;">
        <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
                  font-size:11px;font-weight:600;color:#78716c;text-transform:uppercase;
                  letter-spacing:0.08em;margin:0 0 8px 0;">
          Catatan Keamanan
        </p>
        <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
                  font-size:13px;color:#78716c;line-height:1.6;margin:0;">
          Jika Anda tidak meminta reset password, abaikan email ini.
          Kata sandi Anda tidak akan berubah.
          @isset($ipAddress)
            <br>Permintaan dikirim dari IP:
            <span style="font-family:'Courier New',Courier,monospace;color:#44403c;">
              {{ $ipAddress }}
            </span>
          @endisset
        </p>
      </td>
    </tr>
  </table>

  <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
            font-size:12px;color:#a8a29e;line-height:1.7;margin:0 0 6px 0;">
    Tombol tidak berfungsi? Salin tautan ini:
  </p>
  <p style="font-family:'Courier New',Courier,monospace;
            font-size:12px;color:#78716c;line-height:1.6;margin:0;word-break:break-all;">
    <a href="{{ $actionUrl }}" style="color:#44403c;text-decoration:underline;">
      {{ $actionUrl }}
    </a>
  </p>

</x-email.base>
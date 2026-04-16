<x-email.base
    subject="Kode Verifikasi Login"
    heading="Kode verifikasi Anda.">

  <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
            font-size:15px;color:#57534e;line-height:1.75;margin:0 0 16px 0;">
    Halo {{ $userName }},
  </p>

  <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
            font-size:15px;color:#57534e;line-height:1.75;margin:0 0 24px 0;">
    Kami mendeteksi percobaan login ke akun Anda. Gunakan kode di bawah
    untuk menyelesaikan proses verifikasi.
  </p>

  {{-- OTP Code Block --}}
  <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation"
         style="margin-bottom:24px;">
    <tr>
      <td align="center">
        <table border="0" cellpadding="0" cellspacing="0" role="presentation">
          <tr>
            <td style="background-color:#fafaf9;border:1px solid #e7e5e4;border-radius:2px;
                       border-top:3px solid #1c1917;padding:24px 48px;text-align:center;">
              <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
                        font-size:11px;font-weight:600;color:#a8a29e;
                        text-transform:uppercase;letter-spacing:0.1em;
                        margin:0 0 12px 0;">
                Kode Verifikasi
              </p>
              <p style="font-family:'Courier New',Courier,monospace;
                        font-size:36px;font-weight:700;color:#1c1917;
                        letter-spacing:0.25em;margin:0;line-height:1;">
                {{ $otpCode }}
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
            font-size:13px;color:#a8a29e;line-height:1.6;margin:0 0 24px 0;">
    Kode ini hanya berlaku selama
    <strong style="color:#78716c;">{{ $expiresMinutes }} menit</strong>
    dan hanya dapat digunakan satu kali.
  </p>

  <x-email.divider />

  {{-- Security notice --}}
  <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation"
         style="background-color:#fafaf9;border:1px solid #e7e5e4;border-radius:2px;
                margin-bottom:20px;">
    <tr>
      <td style="padding:16px 20px;">
        <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
                  font-size:11px;font-weight:600;color:#78716c;
                  text-transform:uppercase;letter-spacing:0.08em;
                  margin:0 0 8px 0;">
          Catatan Keamanan
        </p>
        <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
                  font-size:13px;color:#78716c;line-height:1.6;margin:0;">
          Jika Anda tidak sedang melakukan login, abaikan email ini dan
          pertimbangkan untuk segera mengganti password Anda.
          @isset($ipAddress)
            @if($ipAddress)
              <br>Permintaan dikirim dari IP:
              <span style="font-family:'Courier New',Courier,monospace;color:#44403c;">
                {{ $ipAddress }}
              </span>
            @endif
          @endisset
        </p>
      </td>
    </tr>
  </table>


</x-email.base>
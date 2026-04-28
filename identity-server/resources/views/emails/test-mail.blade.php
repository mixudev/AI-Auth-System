<x-email.base
    subject="Uji Coba Koneksi Mail Server"
    heading="Koneksi Berhasil!">

    <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
              font-size:15px;color:#57534e;line-height:1.75;margin:0 0 16px 0;">
        Halo {{ $userName }},
    </p>

    <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
              font-size:15px;color:#57534e;line-height:1.75;margin:0 0 24px 0;">
        Ini adalah pesan konfirmasi otomatis yang dikirimkan untuk menguji konfigurasi <strong>Mail Server (SMTP)</strong> Anda di dashboard admin. Jika Anda menerima email ini, berarti pengaturan koneksi Anda sudah benar dan berfungsi dengan baik.
    </p>

    {{-- Info Table --}}
    <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation"
           style="background-color:#fafaf9;border:1px solid #e7e5e4;border-radius:2px;
                  margin-bottom:24px;">
        <tr>
            <td style="padding:20px;">
                <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
                          font-size:11px;font-weight:600;color:#a8a29e;
                          text-transform:uppercase;letter-spacing:0.1em;
                          margin:0 0 12px 0;">
                    Rincian Koneksi
                </p>
                <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
                    <tr>
                        <td style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:13px; color:#78716c; padding:4px 0;">Host:</td>
                        <td style="font-family:'Courier New',Courier,monospace; font-size:13px; color:#1c1917; font-weight:bold; padding:4px 0;">{{ $mailHost }}</td>
                    </tr>
                    <tr>
                        <td style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:13px; color:#78716c; padding:4px 0;">Port:</td>
                        <td style="font-family:'Courier New',Courier,monospace; font-size:13px; color:#1c1917; font-weight:bold; padding:4px 0;">{{ $mailPort }}</td>
                    </tr>
                    <tr>
                        <td style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:13px; color:#78716c; padding:4px 0;">Encryption:</td>
                        <td style="font-family:'Courier New',Courier,monospace; font-size:13px; color:#1c1917; font-weight:bold; padding:4px 0;">{{ $mailEncryption ?: 'None' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
              font-size:13px;color:#a8a29e;line-height:1.6;margin:0 0 24px 0;">
        Pesan ini dikirimkan melalui pengaturan yang Anda masukkan secara manual di halaman <strong>Configurations</strong>.
    </p>

    <x-email.divider />

    <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
              font-size:12px;color:#a8a29e;line-height:1.6;margin:0; text-align:center;">
        Terima kasih telah menggunakan sistem kami.<br>
        <strong>Tim IT Support</strong>
    </p>

</x-email.base>

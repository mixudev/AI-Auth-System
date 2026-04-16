<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ $lang ?? 'id' }}" xml:lang="{{ $lang ?? 'id' }}">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="format-detection" content="telephone=no, date=no, address=no, email=no" />
  <meta name="x-apple-disable-message-reformatting" />
  <title>{{ $subject ?? config('app.name') }}</title>
  <!--[if mso]>
  <noscript><xml>
    <o:OfficeDocumentSettings xmlns:o="urn:schemas-microsoft-com:office:office">
      <o:PixelsPerInch>96</o:PixelsPerInch>
    </o:OfficeDocumentSettings>
  </xml></noscript>
  <![endif]-->
  <style type="text/css">
    #outlook a        { padding: 0; }
    body              { margin: 0; padding: 0; width: 100% !important; min-width: 100%;
                        -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
    table             { border-collapse: collapse !important;
                        mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
    img               { border: 0; outline: none; text-decoration: none;
                        -ms-interpolation-mode: bicubic; }
    .ExternalClass,
    .ExternalClass p,
    .ExternalClass td,
    .ExternalClass div { line-height: 100%; }
    body, #bodyTable  { background-color: #f5f5f4;
                        font-family: 'Georgia', 'Times New Roman', serif; }
    #emailCard        { background-color: #ffffff; border-radius: 2px;
                        border: 1px solid #e7e5e4; }

    @media screen and (max-width: 600px) {
      #emailContainer { width: 100% !important; }
      .pad            { padding: 32px 24px !important; }
      .pad-footer     { padding: 20px 24px !important; }
      .t-heading      { font-size: 19px !important; }
    }
  </style>
</head>

<body style="margin:0;padding:0;background-color:#f5f5f4;">
<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation"
       id="bodyTable" style="background-color:#f5f5f4;">
  <tr>
    <td align="center" valign="top" style="padding:40px 16px;">
      <table border="0" cellpadding="0" cellspacing="0" width="560"
             id="emailContainer" role="presentation">

        {{-- LOGO --}}
        <tr>
          <td align="{{ $logoAlign ?? 'left' }}" style="padding-bottom:24px;">
            <a href="{{ config('app.url') }}" style="text-decoration:none;">
              <span style="font-family:'Georgia','Times New Roman',serif;font-size:15px;
                           font-weight:normal;letter-spacing:0.12em;text-transform:uppercase;
                           color:#1c1917;text-decoration:none;">
                {{ $appName ?? config('app.name') }}
              </span>
            </a>
          </td>
        </tr>

        {{-- CARD --}}
        <tr>
          <td id="emailCard"
              style="background-color:#ffffff;border-radius:2px;border:1px solid #e7e5e4;">

            {{-- Top rule --}}
            <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
              <tr>
                <td height="3" bgcolor="#1c1917"
                    style="font-size:3px;line-height:3px;background-color:#1c1917;">&nbsp;</td>
              </tr>
            </table>

            {{-- Content --}}
            <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
              <tr>
                <td class="pad" style="padding:44px 48px 36px;">

                  @isset($heading)
                    <h1 style="font-family:'Georgia','Times New Roman',serif;font-size:22px;
                               font-weight:normal;color:#1c1917;line-height:1.35;
                               letter-spacing:-0.01em;margin:0 0 24px 0;">
                      {{ $heading }}
                    </h1>
                  @endisset

                  {{ $slot }}

                </td>
              </tr>
            </table>

            {{-- Footer slot atau default --}}
            @isset($footer)
              {{ $footer }}
            @else
              {{-- Default footer — langsung inline tanpa component --}}
              <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
                <tr>
                  <td style="border-top:1px solid #e7e5e4;padding:24px 48px 32px;" class="pad-footer">
                    <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                           style="margin-bottom:12px;">
                      <tr>
                        <td>
                          <a href="{{ config('app.url') }}/help"
                             style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
                                    font-size:12px;color:#78716c;text-decoration:none;">Bantuan</a>
                          <span style="color:#d6d3d1;margin:0 8px;">&middot;</span>
                          <a href="{{ config('app.url') }}/privacy"
                             style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
                                    font-size:12px;color:#78716c;text-decoration:none;">Kebijakan Privasi</a>
                          <span style="color:#d6d3d1;margin:0 8px;">&middot;</span>
                          <a href="{{ config('app.url') }}/terms"
                             style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
                                    font-size:12px;color:#78716c;text-decoration:none;">Ketentuan Layanan</a>
                        </td>
                      </tr>
                    </table>
                    <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
                              font-size:12px;color:#a8a29e;line-height:1.6;margin:0;">
                      &copy; {{ date('Y') }} {{ config('app.name') }}.
                    </p>
                  </td>
                </tr>
              </table>
            @endisset

          </td>
        </tr>

        {{-- Below card --}}
        <tr>
          <td align="center" style="padding-top:24px;">
            <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
                      font-size:12px;color:#a8a29e;margin:0;line-height:1.6;">
              {{ config('app.name') }}
              &nbsp;&middot;&nbsp;
              <a href="{{ config('app.url') }}/email-preferences"
                 style="color:#a8a29e;text-decoration:underline;">Kelola preferensi email</a>
              &nbsp;&middot;&nbsp;
              <a href="{{ $unsubscribeUrl ?? config('app.url').'/unsubscribe' }}"
                 style="color:#a8a29e;text-decoration:underline;">Unsubscribe</a>
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>
</body>
</html>
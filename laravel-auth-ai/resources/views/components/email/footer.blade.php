@props([
    'address'       => null,
    'showLinks'     => true,
    'showCopyright' => true,
    'customNote'    => null,
])
<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
  <tr>
    <td style="border-top:1px solid #e7e5e4;padding:24px 48px 32px;" class="pad-footer">

      @if($customNote)
        <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
                  font-size:12px;color:#a8a29e;line-height:1.6;margin:0 0 12px 0;">
          {{ $customNote }}
        </p>
      @endif

      @if($showLinks)
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
      @endif

      @if($showCopyright)
        <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
                  font-size:12px;color:#a8a29e;line-height:1.6;margin:0;">
          &copy; {{ date('Y') }} {{ config('app.name') }}.
          @if($address ?? config('mail.company_address'))
            &middot; {{ $address ?? config('mail.company_address') }}
          @endif
        </p>
      @endif

    </td>
  </tr>
</table>
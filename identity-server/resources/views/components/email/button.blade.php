@props([
    'url',
    'label'     => 'Klik di Sini',
    'align'     => 'left',
    'fullwidth' => false,
])
<table border="0" cellpadding="0" cellspacing="0"
       @if($fullwidth) width="100%" @endif
       role="presentation" style="margin:28px 0;">
  <tr>
    <td align="{{ $align }}">
      <!--[if mso]>
      <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml"
                   xmlns:w="urn:schemas-microsoft-com:office:word"
                   href="{{ $url }}"
                   style="height:44px;v-text-anchor:middle;width:{{ $fullwidth ? '100%' : '200px' }};"
                   arcsize="2%" stroke="f" fillcolor="#1c1917">
        <w:anchorlock/>
        <center style="color:#ffffff;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
                        font-size:13px;letter-spacing:0.06em;text-transform:uppercase;">
          {{ $label }}
        </center>
      </v:roundrect>
      <![endif]-->
      <!--[if !mso]><!-->
      <a href="{{ $url }}"
         style="background-color:#1c1917;border-radius:2px;color:#ffffff;
                display:{{ $fullwidth ? 'block' : 'inline-block' }};
                font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
                font-size:13px;font-weight:500;letter-spacing:0.06em;line-height:1;
                padding:14px {{ $fullwidth ? '24px' : '28px' }};text-align:center;
                text-decoration:none;text-transform:uppercase;-webkit-text-size-adjust:none;">
        {{ $label }}
      </a>
      <!--<![endif]-->
    </td>
  </tr>
</table>
@props([
    'spacing' => '24',
    'style'   => 'line',
])
<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
  <tr>
    <td style="padding:{{ $spacing }}px 0 0;font-size:1px;line-height:1px;">
      @if($style === 'line')
        <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
          <tr>
            <td height="1" bgcolor="#e7e5e4"
                style="font-size:1px;line-height:1px;background-color:#e7e5e4;">&nbsp;</td>
          </tr>
        </table>
        <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
          <tr>
            <td height="{{ $spacing }}"
                style="font-size:1px;line-height:1px;">&nbsp;</td>
          </tr>
        </table>
      @else
        <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
          <tr>
            <td height="{{ $spacing }}"
                style="font-size:1px;line-height:1px;">&nbsp;</td>
          </tr>
        </table>
      @endif
    </td>
  </tr>
</table>
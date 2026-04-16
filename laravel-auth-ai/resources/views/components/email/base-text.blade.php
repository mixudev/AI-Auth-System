{{ $appName ?? config('app.name') }}
{{ str_repeat('─', 42) }}

@isset($heading)
{{ strtoupper($heading) }}
{{ str_repeat('─', 42) }}

@endisset
{{ $slot }}

{{ str_repeat('─', 42) }}
© {{ date('Y') }} {{ config('app.name') }}
{{ config('mail.from.address') }}

Kelola preferensi: {{ config('app.url') }}/email-preferences
Unsubscribe: {{ $unsubscribeUrl ?? config('app.url').'/unsubscribe' }}

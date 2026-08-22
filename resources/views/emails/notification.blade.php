@component('mail::message')
{{-- The logo is rendered in the custom mail header.  Only the body content is defined here. --}}

@isset($title)
## {{ $title }}
@endisset

@isset($message)
{!! nl2br(e($message)) !!}
@endisset

@isset($actionUrl)
@component('mail::button', ['url' => $actionUrl])
{{ $actionText ?? 'Pregled' }}
@endcomponent
@endisset

Hvala što koristite našu platformu!<br>
{{ config('app.name') }}
@endcomponent
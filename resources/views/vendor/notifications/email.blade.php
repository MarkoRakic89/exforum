@component('mail::layout')
{{-- Laravel default email notification template overridden to include custom logo and Serbian text --}}

@slot('header')
@component('mail::header', ['url' => config('app.url')])
<div style="display: flex; align-items: center;">
    <span style="margin-right: 8px;">
        @include('_partials.macros', ['width' => 40, 'height' => 40])
    </span>
    <span style="font-size: 20px; font-weight: bold; color: #dc3545;">
        {{ config('app.name') }}
    </span>
</div>
@endcomponent
@endslot

@isset($greeting)
# {{ $greeting }}
@endisset

@foreach ($introLines as $line)
{{ $line }}

@endforeach

@isset($actionText)
@component('mail::button', ['url' => $actionUrl, 'color' => 'red'])
{{ $actionText }}
@endcomponent
@endisset

@foreach ($outroLines as $line)
{{ $line }}

@endforeach

Pozdrav,<br>
{{ config('app.name') }}

@slot('subcopy')
@isset($actionText)
@component('mail::subcopy')
Ako imate problema prilikom klika na "{{ $actionText }}" dugme, kopirajte i nalepite sledeći URL u vaš internet pretraživač: <span class="break-all">{{ $actionUrl }}</span>
@endcomponent
@endisset
@endslot

@slot('footer')
@component('mail::footer')
© {{ date('Y') }} {{ config('app.name') }}. Sva prava zadržana.
@endcomponent
@endslot
@endcomponent
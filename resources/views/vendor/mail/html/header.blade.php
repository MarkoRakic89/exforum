{{--
  Custom mail header overriding the default Laravel header.  This header
  displays the application logo centered at the top of each email.  The
  logo is stored in the public directory (public/logo.svg).  Adjust the
  width and height as needed.  If no logo is found the application name
  will be displayed instead.
--}}
<tr>
    <td class="header" style="padding: 20px 0; text-align: center;">
        {{-- Use the SVG logo directly by including the macros partial.  This avoids issues with
             relative paths when emails are sent and ensures the logo displays correctly.
             If the macro file is missing, fall back to showing the app name. --}}
        @php
            // Try rendering the SVG macro; if the file is not found, catch the exception.
            $logoHtml = '';
            try {
                $logoHtml = view('_partials.macros', ['width' => '80', 'height' => '80'])->render();
            } catch (Throwable $e) {
                $logoHtml = '';
            }
        @endphp
        @if(!empty($logoHtml))
            {!! $logoHtml !!}
        @else
            <span style="font-size: 24px; font-weight: bold;">{{ config('app.name') }}</span>
        @endif
    </td>
</tr>
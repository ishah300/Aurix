@php
    $brandAppearance = array_merge((array) config('aurix.appearance.defaults', []), (array) ($aurixAppearance ?? []));
    $brandLogoMode = (string) ($brandAppearance['logo_mode'] ?? 'svg');
    $brandLogoHeight = (int) ($brandAppearance['logo_height'] ?? 32);
    $brandLogoSvg = (string) ($brandAppearance['logo_svg'] ?? '');
    $brandLogoImageUrl = (string) ($brandAppearance['logo_image_url'] ?? '');
@endphp

<div class="la-brand">
    <div class="la-brand-logo" style="height: {{ $brandLogoHeight }}px; width: {{ $brandLogoHeight }}px;">
        @if($brandLogoMode === 'upload' && $brandLogoImageUrl !== '')
            <img src="{{ $brandLogoImageUrl }}" alt="Aurix" style="height: 100%; width: 100%; object-fit: contain;">
        @else
            {!! $brandLogoSvg !!}
        @endif
    </div>
    <div class="la-brand-text">Aurix</div>
</div>


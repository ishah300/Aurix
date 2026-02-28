@props(['class' => '', 'height' => null])

@php
    $appearance = array_merge((array) config('aurix.appearance.defaults', []), (array) ($aurixAppearance ?? []));
    $mode = (string) ($appearance['logo_mode'] ?? 'svg');
    $svg = (string) ($appearance['logo_svg'] ?? '');
    $imageUrl = trim((string) ($appearance['logo_image_url'] ?? ''));
    $fallbackImageUrl = trim((string) config('aurix.appearance.defaults.logo_image_url', ''));
    $effectiveImageUrl = $imageUrl !== '' ? $imageUrl : $fallbackImageUrl;
    $explicitHeight = $height !== null ? (int) $height : null;
    $defaultHeight = (int) ($appearance['logo_height'] ?? 32);
    $computedHeight = $explicitHeight ?? ($class === '' ? $defaultHeight : null);
@endphp

@if($effectiveImageUrl !== '')
    <img
        src="{{ $effectiveImageUrl }}"
        alt="Aurix"
        class="{{ $class }}"
        @if($computedHeight !== null) style="height: {{ $computedHeight }}px; width: auto;" @endif
    />
@else
    <span
        class="inline-flex items-center justify-center {{ $class }}"
        style="color: {{ (string) ($appearance['text_color'] ?? '#0f172a') }};@if($computedHeight !== null) height: {{ $computedHeight }}px; width: {{ $computedHeight }}px; @endif"
    >
        {!! $svg !!}
    </span>
@endif

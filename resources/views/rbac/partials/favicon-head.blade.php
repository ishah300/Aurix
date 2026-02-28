@php
    $defaults = (array) config('aurix.appearance.defaults', []);
    $appearance = (array) ($aurixAppearance ?? []);

    $pick = static function (string $key) use ($appearance, $defaults): string {
        $value = trim((string) ($appearance[$key] ?? ''));
        if ($value !== '') {
            return $value;
        }

        return trim((string) ($defaults[$key] ?? ''));
    };

    $faviconLight = $pick('favicon_light_url');
    $faviconDark = $pick('favicon_dark_url');
    $appleTouch = '/vendor/aurix/apple-touch-icon.png';
@endphp

@if($faviconLight !== '')
    <link rel="icon" type="image/png" href="{{ $faviconLight }}" media="(prefers-color-scheme: light)">
    <link rel="icon" type="image/png" href="{{ $faviconLight }}">
@endif

@if($faviconDark !== '')
    <link rel="icon" type="image/png" href="{{ $faviconDark }}" media="(prefers-color-scheme: dark)">
@endif

<link rel="apple-touch-icon" href="{{ $appleTouch }}">

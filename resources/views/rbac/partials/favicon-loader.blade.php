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

    $favLight = $pick('favicon_light_url');
    $favDark = $pick('favicon_dark_url');
    $favApple = '/vendor/aurix/apple-touch-icon.png';
@endphp

<script>
(() => {
    const ensure = (selector, attrs) => {
        let el = document.head.querySelector(selector);
        if (!el) {
            el = document.createElement('link');
            document.head.appendChild(el);
        }
        Object.entries(attrs).forEach(([k, v]) => el.setAttribute(k, v));
    };

    const light = @json($favLight);
    const dark = @json($favDark);
    const apple = @json($favApple);

    if (light) {
        ensure('link[rel="icon"][data-aurix="light"]', {
            rel: 'icon',
            type: 'image/png',
            href: light,
            media: '(prefers-color-scheme: light)',
            'data-aurix': 'light',
        });
        ensure('link[rel="icon"][data-aurix="default"]', {
            rel: 'icon',
            type: 'image/png',
            href: light,
            'data-aurix': 'default',
        });
    }

    if (dark) {
        ensure('link[rel="icon"][data-aurix="dark"]', {
            rel: 'icon',
            type: 'image/png',
            href: dark,
            media: '(prefers-color-scheme: dark)',
            'data-aurix': 'dark',
        });
    }

    ensure('link[rel="apple-touch-icon"][data-aurix="apple"]', {
        rel: 'apple-touch-icon',
        href: apple,
        'data-aurix': 'apple',
    });
})();
</script>

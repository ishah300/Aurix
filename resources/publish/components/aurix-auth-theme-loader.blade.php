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

    $bgColor = $pick('background_color') !== '' ? $pick('background_color') : '#f8fafc';
    $bgImage = $pick('background_image_url');
    $overlayColor = $pick('background_overlay_color') !== '' ? $pick('background_overlay_color') : '#000000';
    $overlayOpacityRaw = trim((string) ($appearance['background_overlay_opacity'] ?? ($defaults['background_overlay_opacity'] ?? 50)));
    $overlayOpacity = max(0, min(100, (int) $overlayOpacityRaw));
@endphp

<script>
(() => {
    const bgColor = @json($bgColor);
    const bgImage = @json($bgImage);
    const overlayColor = @json($overlayColor);
    const overlayOpacity = @json($overlayOpacity);

    const toRgba = (hex, alpha) => {
        const v = String(hex || '').replace('#', '').trim();
        if (!/^[0-9a-fA-F]{3,6}$/.test(v)) {
            return `rgba(0,0,0,${alpha})`;
        }

        const n = v.length === 3
            ? v.split('').map((c) => c + c).join('')
            : v;

        const r = parseInt(n.slice(0, 2), 16);
        const g = parseInt(n.slice(2, 4), 16);
        const b = parseInt(n.slice(4, 6), 16);

        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    };

    const applyTo = (node) => {
        if (!node) return;

        node.style.backgroundColor = bgColor;
        if (bgImage) {
            const alpha = Math.max(0, Math.min(1, Number(overlayOpacity) / 100));
            const overlay = toRgba(overlayColor, alpha);
            node.style.backgroundImage = `linear-gradient(${overlay}, ${overlay}), url('${bgImage}')`;
            node.style.backgroundSize = 'cover';
            node.style.backgroundPosition = 'center';
            node.style.backgroundRepeat = 'no-repeat';
        } else {
            node.style.backgroundImage = 'none';
        }
    };

    const applyAuthTheme = () => {
        const marker = document.querySelector('[data-aurix-auth-page]');
        if (!marker) return;

        // Prefer dedicated Aurix auth shells, but fall back to the
        // Breeze-style full-page shell when present so the *page
        // background* (not just the card) is themed.
        const shell = marker.closest('.aurix-auth-page, .min-h-screen, [data-aurix-auth-shell]');

        const target = shell
            || marker.parentElement
            || marker;

        applyTo(target);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyAuthTheme, { once: true });
    } else {
        applyAuthTheme();
    }
})();
</script>

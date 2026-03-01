<?php

declare(strict_types=1);

namespace Aurix\Support;

use DOMDocument;
use DOMElement;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AppearanceInputSanitizer
{
    private const SVG_ALLOWED_TAGS = [
        'svg',
        'g',
        'path',
        'rect',
        'circle',
        'ellipse',
        'line',
        'polyline',
        'polygon',
        'text',
        'defs',
        'lineargradient',
        'radialgradient',
        'stop',
        'title',
        'desc',
        'symbol',
        'use',
        'clipPath',
        'clippath',
        'mask',
        'pattern',
        'image',
    ];

    private const SVG_ALLOWED_ATTRS = [
        'fill', 'stroke', 'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'stroke-dasharray',
        'd', 'x', 'y', 'x1', 'x2', 'y1', 'y2', 'cx', 'cy', 'r', 'rx', 'ry', 'width', 'height',
        'viewbox', 'preserveaspectratio', 'transform', 'opacity', 'class', 'style', 'id', 'role',
        'aria-label', 'xmlns', 'xmlns:xlink', 'xlink:href', 'href', 'gradientunits', 'gradienttransform',
        'offset', 'patternunits', 'patterncontentunits', 'maskunits', 'maskcontentunits'
    ];

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public static function clean(array $payload): array
    {
        if (array_key_exists('custom_css', $payload)) {
            $payload['custom_css'] = self::sanitizeCss((string) $payload['custom_css']);
        }

        if (array_key_exists('logo_svg', $payload)) {
            $payload['logo_svg'] = self::sanitizeSvgReference($payload['logo_svg']);
        }

        return $payload;
    }

    private static function sanitizeCss(string $css): string
    {
        $trimmed = trim($css);

        if ($trimmed === '') {
            return '';
        }

        if (Str::contains($trimmed, ['<', '>'])) {
            throw ValidationException::withMessages([
                'custom_css' => __('Custom CSS cannot contain HTML tags or markup.'),
            ]);
        }

        return $trimmed;
    }

    private static function sanitizeSvgReference(mixed $svg): ?string
    {
        if ($svg === null) {
            return null;
        }

        $value = trim((string) $svg);
        if ($value === '') {
            return '';
        }

        if (Str::startsWith(ltrim($value), '<svg')) {
            return self::sanitizeSvgMarkup($value);
        }

        if (self::isAllowedSvgUrl($value)) {
            return $value;
        }

        throw ValidationException::withMessages([
            'logo_svg' => __('Please provide a valid SVG URL.'),
        ]);
    }

    private static function sanitizeSvgMarkup(string $value): ?string
    {
        if ($value === '') {
            return '';
        }

        libxml_use_internal_errors(true);

        $document = new DOMDocument();
        $loaded = $document->loadXML($value, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);

        if (! $loaded || ! $document->documentElement) {
            throw ValidationException::withMessages([
                'logo_svg' => __('Please provide a valid SVG snippet.'),
            ]);
        }

        $root = $document->documentElement;
        if (strtolower($root->nodeName) !== 'svg') {
            throw ValidationException::withMessages([
                'logo_svg' => __('Only <svg> snippets are allowed for custom logos.'),
            ]);
        }

        self::enforceSvgSafety($root);

        $sanitized = $document->saveXML($root) ?: '';

        return trim($sanitized);
    }

    private static function isAllowedSvgUrl(string $value): bool
    {
        if (str_starts_with($value, '/')) {
            return true;
        }

        if (str_starts_with($value, 'data:image/svg+xml')) {
            return true;
        }

        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = (string) parse_url($value, PHP_URL_SCHEME);
        return in_array(strtolower($scheme), ['http', 'https'], true);
    }

    private static function enforceSvgSafety(DOMElement $element): void
    {
        $nodeName = strtolower($element->nodeName);
        if (! in_array($nodeName, array_map('strtolower', self::SVG_ALLOWED_TAGS), true)) {
            throw ValidationException::withMessages([
                'logo_svg' => __('The SVG contains unsupported elements.'),
            ]);
        }

        if ($element->hasAttributes()) {
            foreach (iterator_to_array($element->attributes) as $attribute) {
                $name = strtolower($attribute->name);
                $value = (string) $attribute->value;

                if (str_starts_with($name, 'on')) {
                    throw ValidationException::withMessages([
                        'logo_svg' => __('SVG event handler attributes are not allowed.'),
                    ]);
                }

                if (! in_array($name, array_map('strtolower', self::SVG_ALLOWED_ATTRS), true)) {
                    throw ValidationException::withMessages([
                        'logo_svg' => __('The SVG contains unsupported attributes.'),
                    ]);
                }

                if (Str::contains(strtolower($value), ['javascript:', '<script', '</script>'])) {
                    throw ValidationException::withMessages([
                        'logo_svg' => __('The SVG contains disallowed script references.'),
                    ]);
                }
            }
        }

        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement) {
                self::enforceSvgSafety($child);
            }
        }
    }
}

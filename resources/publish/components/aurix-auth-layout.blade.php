@php
    $appearance = array_merge((array) config('aurix.appearance.defaults', []), (array) ($aurixAppearance ?? []));
    $backgroundColor = (string) ($appearance['background_color'] ?? '#f8fafc');
    $textColor = (string) ($appearance['text_color'] ?? '#0f172a');
    $buttonColor = (string) ($appearance['button_color'] ?? '#111827');
    $buttonTextColor = (string) ($appearance['button_text_color'] ?? '#ffffff');
    $inputTextColor = (string) ($appearance['input_text_color'] ?? '#0f172a');
    $inputBorderColor = (string) ($appearance['input_border_color'] ?? '#d1d5db');
    $headingAlignment = in_array((string) ($appearance['heading_alignment'] ?? 'left'), ['left', 'center', 'right'], true)
        ? (string) $appearance['heading_alignment']
        : 'left';
    $containerAlignment = in_array((string) ($appearance['container_alignment'] ?? 'center'), ['left', 'center', 'right'], true)
        ? (string) $appearance['container_alignment']
        : 'center';
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    @include('aurix::rbac.partials.favicon-head')

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --aurix-bg: {{ $backgroundColor }};
            --aurix-text: {{ $textColor }};
            --aurix-button: {{ $buttonColor }};
            --aurix-button-text: {{ $buttonTextColor }};
            --aurix-input-text: {{ $inputTextColor }};
            --aurix-input-border: {{ $inputBorderColor }};
            --aurix-heading-align: {{ $headingAlignment }};
        }
        .aurix-auth-page {
            background: var(--aurix-bg);
            color: var(--aurix-text);
        }
        .aurix-auth-card {
            border: 1px solid var(--aurix-input-border);
        }
        .aurix-auth-card h1,
        .aurix-auth-card h2,
        .aurix-auth-card h3 {
            text-align: var(--aurix-heading-align);
        }
        .aurix-auth-input {
            border-color: var(--aurix-input-border) !important;
            color: var(--aurix-input-text) !important;
        }
        .aurix-auth-primary {
            background: var(--aurix-button) !important;
            border-color: var(--aurix-button) !important;
            color: var(--aurix-button-text) !important;
        }
        .aurix-auth-shell {
            justify-content: {{ $containerAlignment === 'left' ? 'flex-start' : ($containerAlignment === 'right' ? 'flex-end' : 'center') }};
        }
        {!! (string) ($appearance['custom_css'] ?? '') !!}
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen flex items-center aurix-auth-shell px-4 py-8 aurix-auth-page">
        <div class="w-full max-w-md">
            {{ $slot }}
        </div>
    </div>
</body>
</html>

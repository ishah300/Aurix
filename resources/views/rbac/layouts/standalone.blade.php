<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title }}</title>
    @php
        $viteHot = public_path('hot');
        $viteManifest = public_path('build/manifest.json');
        $legacyCss = public_path('css/app.css');
    @endphp
    @if(file_exists($viteHot) || file_exists($viteManifest))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @elseif(file_exists($legacyCss))
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @endif
    @include('aurix::rbac.partials.favicon-head')
</head>
<body style="background:#f6f7fb;">
@include($contentView)
</body>
</html>

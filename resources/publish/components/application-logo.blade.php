@props(['class' => ''])

@php
    // Navbar/header brand size: larger than Breeze default for better legibility.
    $size = 56;
@endphp

<x-aurix-auth-logo :height="$size" class="{{ $class }} object-contain" />

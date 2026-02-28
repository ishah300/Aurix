<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $title }}</h2>
    </x-slot>

    @include($contentView)
</x-app-layout>

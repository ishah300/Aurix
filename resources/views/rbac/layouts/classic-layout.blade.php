@extends('layouts.app')

@section('content')
    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-4 text-2xl font-semibold text-gray-900">{{ $title }}</h1>
        </div>
    </div>

    @include($contentView)
@endsection

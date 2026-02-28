@php
    $providers = collect($aurixEnabledSocialProviders ?? [])->values();
    $routeCandidates = [
        'socialite.redirect',
        'social.redirect',
        'oauth.redirect',
        'auth.social.redirect',
    ];

    $resolveProviderUrl = static function (string $slug) use ($routeCandidates): ?string {
        foreach ($routeCandidates as $routeName) {
            if (\Illuminate\Support\Facades\Route::has($routeName)) {
                try {
                    return route($routeName, ['provider' => $slug]);
                } catch (\Throwable) {
                    // Continue trying other route conventions.
                }
            }
        }

        return null;
    };
@endphp

@if($providers->isNotEmpty())
    <div class="mt-6">
        <div class="relative my-4">
            <div class="absolute inset-0 flex items-center">
                <span class="w-full border-t border-gray-300"></span>
            </div>
            <div class="relative flex justify-center text-xs">
                <span class="bg-white px-2 text-gray-500 uppercase tracking-wide">OR</span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-2">
            @foreach($providers as $provider)
                @php
                    $slug = (string) ($provider['slug'] ?? '');
                    $name = (string) ($provider['name'] ?? ucfirst($slug));
                    $url = $resolveProviderUrl($slug);
                @endphp

                @if($url !== null && $slug !== '')
                    <a href="{{ $url }}" class="inline-flex w-full items-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <span class="inline-flex h-5 w-5 items-center justify-center">@include('aurix::rbac.partials._provider_icons', ['slug' => $slug])</span>
                        <span>Continue with {{ $name }}</span>
                    </a>
                @else
                    <button type="button" disabled class="inline-flex w-full items-center gap-2 rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-400 cursor-not-allowed">
                        <span class="inline-flex h-5 w-5 items-center justify-center">@include('aurix::rbac.partials._provider_icons', ['slug' => $slug])</span>
                        <span>Continue with {{ $name }}</span>
                    </button>
                @endif
            @endforeach
        </div>
    </div>
@endif

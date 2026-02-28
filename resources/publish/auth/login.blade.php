<x-aurix-favicon-loader />
<x-aurix-auth-theme-loader />
<x-guest-layout>
    <div data-aurix-auth-page hidden aria-hidden="true"></div>

    <div class="rounded-xl bg-white p-6 shadow-sm">
        @if (session('error'))
            <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif
        @if (session('status'))
            <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif
        @if (session('social_link_url'))
            <div class="mb-4 rounded-md border border-sky-200 bg-sky-50 px-3 py-2 text-sm text-sky-800">
                <p class="mb-2">Confirm link:</p>
                <a
                    href="{{ session('social_link_url') }}"
                    class="inline-flex rounded-md bg-sky-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-sky-700"
                >
                    Confirm Social Link
                </a>
            </div>
        @endif

        <div class="flex justify-end">
            <h2 class="text-3xl font-semibold text-gray-800">Sign in</h2>
        </div>

        <form method="POST" action="{{ route('login') }}" class="mt-6" id="aurixLoginForm">
            @csrf

            <div class="mb-3">
                <label for="email" class="mb-1 block text-sm font-medium text-gray-600">Email Address</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    required
                    value="{{ old('email') }}"
                    class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm placeholder-gray-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                />
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            @php
                $showPassword = $errors->has('password') || (bool) old('password');
            @endphp
            <div class="mb-3 {{ $showPassword ? '' : 'hidden' }}" id="aurixPasswordWrap">
                <label for="password" class="mb-1 block text-sm font-medium text-gray-600">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm placeholder-gray-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                    autocomplete="current-password"
                />
                @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4">
                <button
                    type="submit"
                    id="aurixContinueBtn"
                    class="w-full rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-black"
                >
                    {{ $showPassword ? 'Sign in' : 'Continue' }}
                </button>
            </div>
        </form>

        <p class="mt-4 text-sm text-gray-500">
            Don't have an account?
            <a href="{{ route('register') }}" class="underline">Sign up</a>
        </p>

        <x-aurix-social-providers />
    </div>

    <script>
    (() => {
        const form = document.getElementById('aurixLoginForm');
        const passwordWrap = document.getElementById('aurixPasswordWrap');
        const passwordInput = document.getElementById('password');
        const button = document.getElementById('aurixContinueBtn');

        if (!form || !passwordWrap || !passwordInput || !button) return;

        form.addEventListener('submit', (event) => {
            const isPreviewPath = window.location.pathname.includes('/auth/rbac/appearance/preview/');
            if (isPreviewPath) {
                event.preventDefault();
            }

            const hidden = passwordWrap.classList.contains('hidden');
            if (!hidden) return;

            event.preventDefault();
            passwordWrap.classList.remove('hidden');
            passwordInput.setAttribute('required', 'required');
            button.textContent = 'Sign in';
            passwordInput.focus();
        });
    })();
    </script>
</x-guest-layout>

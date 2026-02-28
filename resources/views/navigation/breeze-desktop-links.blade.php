@php
    $normalizePath = function (?string $href): string {
        $path = ltrim((string) parse_url((string) ($href ?? ''), PHP_URL_PATH), '/');
        return strtolower(trim($path));
    };

    $isSystemMenuNode = function (array $node) use ($normalizePath): bool {
        $menuName = strtolower(trim((string) ($node['menu_name'] ?? '')));
        $menuSlug = strtolower(trim((string) ($node['menu_slug'] ?? '')));
        $routePath = $normalizePath($node['route'] ?? null);

        if (in_array($menuName, ['setup', 'appearance'], true) || in_array($menuSlug, ['setup', 'appearance'], true)) {
            return true;
        }

        return in_array($routePath, ['auth/rbac/setup', 'auth/rbac/appearance'], true);
    };

    $filteredMenuTree = collect($aurixMenuTree ?? collect())
        ->reject($isSystemMenuNode)
        ->map(function (array $menu) use ($isSystemMenuNode): array {
            $menu['children'] = collect($menu['children'] ?? [])
                ->reject($isSystemMenuNode)
                ->values()
                ->all();

            return $menu;
        })
        ->values();

    $isPlaceholderHref = function (?string $href): bool {
        $value = trim((string) ($href ?? ''));
        return $value === '' || $value === '#';
    };

    $isActiveHref = function (?string $href) use ($isPlaceholderHref): bool {
        if ($isPlaceholderHref($href)) {
            return false;
        }

        if (! is_string($href) || $href === '' || $href === '#') {
            return false;
        }

        $path = ltrim((string) parse_url($href, PHP_URL_PATH), '/');
        if ($path === '') {
            return false;
        }

        return request()->is($path);
    };

    $childIsActive = function (array $node) use (&$childIsActive, $isActiveHref): bool {
        if ($isActiveHref($node['route'] ?? null)) {
            return true;
        }

        foreach (($node['children'] ?? []) as $child) {
            if ($childIsActive($child)) {
                return true;
            }
        }

        return false;
    };
@endphp

@foreach($filteredMenuTree as $menu)
    @php
        $href = $menu['route'] ?: '#';
        $hasChildren = !empty($menu['children']);
        $active = $isActiveHref($href) || $childIsActive($menu);
    @endphp

    @if(!$hasChildren)
        <x-nav-link :href="$href" :active="$active">
            {{ $menu['menu_name'] }}
        </x-nav-link>
    @else
        <div class="flex items-center sm:-my-px">
            <x-dropdown align="left" width="48">
                <x-slot name="trigger">
                    <button class="inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none {{ $active ? 'border-indigo-400 text-gray-900 focus:border-indigo-700' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 focus:text-gray-700 focus:border-gray-300' }}">
                        <span>{{ $menu['menu_name'] }}</span>
                        <svg class="ms-1 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </x-slot>

                <x-slot name="content">
                    @foreach(($menu['children'] ?? []) as $child)
                        @php($childHref = $child['route'] ?: '#')
                        @if($isPlaceholderHref($childHref))
                            <span class="block px-4 py-2 text-sm text-gray-700">
                                {{ $child['menu_name'] }}
                            </span>
                        @else
                            <x-dropdown-link :href="$childHref">
                                {{ $child['menu_name'] }}
                            </x-dropdown-link>
                        @endif
                    @endforeach
                </x-slot>
            </x-dropdown>
        </div>
    @endif
@endforeach

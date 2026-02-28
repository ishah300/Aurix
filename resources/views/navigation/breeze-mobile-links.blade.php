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
        $active = $isActiveHref($href) || $childIsActive($menu);
    @endphp

    @if($isPlaceholderHref($href))
        <div class="block px-4 py-2 text-sm font-medium text-gray-700">
            {{ $menu['menu_name'] }}
        </div>
    @else
        <x-responsive-nav-link :href="$href" :active="$active">
            {{ $menu['menu_name'] }}
        </x-responsive-nav-link>
    @endif

    @foreach(($menu['children'] ?? []) as $child)
        @php($childHref = $child['route'] ?: '#')
        <div class="ps-6">
            @if($isPlaceholderHref($childHref))
                <div class="block px-4 py-2 text-sm text-gray-500">
                    {{ $child['menu_name'] }}
                </div>
            @else
                <x-responsive-nav-link :href="$childHref" :active="$isActiveHref($childHref)">
                    {{ $child['menu_name'] }}
                </x-responsive-nav-link>
            @endif
        </div>
    @endforeach
@endforeach

@php
    $renderNode = function (array $node, int $depth = 0) use (&$renderNode) {
        $pad = 10 + ($depth * 14);
        $route = $node['route'] ?: '#';
        $hasChildren = !empty($node['children']);

        echo '<div style="padding-left:' . $pad . 'px;">';
        echo '<a href="' . e($route) . '" style="display:flex;justify-content:space-between;align-items:center;padding:8px 10px;border-radius:8px;text-decoration:none;color:#334155;font-size:14px;">';
        echo '<span>' . e($node['menu_name']) . '</span>';
        echo '<span style="font-size:11px;color:#94a3b8;">';
        $acts = [];
        if (!empty($node['actions']['create'])) $acts[] = 'C';
        if (!empty($node['actions']['update'])) $acts[] = 'U';
        if (!empty($node['actions']['delete'])) $acts[] = 'D';
        if (!empty($node['actions']['edit'])) $acts[] = 'V';
        echo e(implode('/', $acts));
        echo '</span>';
        echo '</a>';
        echo '</div>';

        if ($hasChildren) {
            foreach ($node['children'] as $child) {
                $renderNode($child, $depth + 1);
            }
        }
    };
@endphp

<div style="border:1px solid #e5e7eb;border-radius:10px;background:#fff;padding:10px;">
    <div style="padding:6px 8px;font-size:11px;font-weight:700;color:#64748b;letter-spacing:.08em;text-transform:uppercase;">My Menus</div>
    @if(($aurixMenuTree ?? collect())->isEmpty())
        <div style="padding:8px;font-size:13px;color:#94a3b8;">No menu access assigned.</div>
    @else
        @foreach(($aurixMenuTree ?? collect()) as $node)
            {!! $renderNode($node) !!}
        @endforeach
    @endif
</div>

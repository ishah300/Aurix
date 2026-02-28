<div class="la-wrap">
    @include('aurix::rbac.partials.ui-styles')
    @include('aurix::rbac.partials.js-helpers')

    <div class="la-shell">
        @include('aurix::rbac.partials.brand')
        <div class="la-card">
            <div class="la-head">
                <div>
                    <h1>Assign rights to a role</h1>
                    <div class="la-sub">
                        <span class="la-badge">Role</span>
                        {{ $role->name }} ({{ $role->slug }})
                    </div>
                </div>
                <div class="la-btn-row">
                    <button id="exportJsonBtn" class="la-btn dark" type="button">Export JSON</button>
                    <button id="exportCsvBtn" class="la-btn dark" type="button">Export CSV</button>
                </div>
            </div>
            <div class="la-body">
                <div class="la-status err la-mb-10">
                    RBAC/System menus are management-only (for <code>/auth/rbac/*</code>) and should not be assigned to business roles.
                    They are marked in the table and locked from editing.
                </div>
                @if($isFullAccessRole)
                    <div class="la-status ok la-mb-10">
                        This role has full system access by default. Permission assignment is not required.
                    </div>
                @endif
                <div id="rightsStatus" class="la-status">Loading role permissions...</div>
                <div class="mt-3 max-h-[58vh] overflow-auto rounded-lg border border-slate-200">
                    <table class="min-w-[760px] w-full table-fixed border-collapse text-sm">
                        <colgroup>
                            <col style="width: 46%;">
                            <col style="width: 13.5%;">
                            <col style="width: 13.5%;">
                            <col style="width: 13.5%;">
                            <col style="width: 13.5%;">
                        </colgroup>
                        <thead>
                        <tr>
                            <th class="border border-slate-200 bg-slate-100 px-3 py-2 text-left font-medium text-slate-700">Menu Name</th>
                            <th class="border border-slate-200 bg-slate-100 px-3 py-2 text-center font-medium text-slate-700">View</th>
                            <th class="border border-slate-200 bg-slate-100 px-3 py-2 text-center font-medium text-slate-700">Insert</th>
                            <th class="border border-slate-200 bg-slate-100 px-3 py-2 text-center font-medium text-slate-700">Update</th>
                            <th class="border border-slate-200 bg-slate-100 px-3 py-2 text-center font-medium text-slate-700">Delete</th>
                        </tr>
                        </thead>
                        <tbody id="rightsBody"></tbody>
                    </table>
                </div>
                <div class="la-footer">
                    <a href="{{ $rolesPageUrl }}" class="la-btn">Close</a>
                    <button id="saveRightsBtn" type="button" class="la-btn primary @if($isFullAccessRole) disabled @endif" @if($isFullAccessRole) disabled @endif>Save changes</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const apiBase = @json($apiPrefix);
    const roleId = @json($role->id);
    const isFullAccessRole = @json($isFullAccessRole);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || @json(csrf_token());

    const rightsBody = document.getElementById('rightsBody');
    const rightsStatus = document.getElementById('rightsStatus');
    let matrix = [];

    const req = async (url, options = {}) => {
        const res = await fetch(url, {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                ...(options.headers || {}),
            },
            ...options,
        });
        if (!res.ok) throw new Error(await res.text() || `Request failed (${res.status})`);
        const type = res.headers.get('content-type') || '';
        return type.includes('application/json') ? res.json() : res.text();
    };

    const setStatus = (text, type = 'info') => {
        rightsStatus.className = `la-status${type === 'ok' ? ' ok' : type === 'err' ? ' err' : ''}`;
        rightsStatus.textContent = text;
    };

    const buildTree = (items) => {
        const byId = new Map();
        items.forEach((item) => byId.set(item.menu_id, { ...item, children: [] }));
        const roots = [];

        byId.forEach((node) => {
            const parentId = node.parent_id ?? null;
            if (parentId && byId.has(parentId)) byId.get(parentId).children.push(node);
            else roots.push(node);
        });

        const sortNodes = (arr) => {
            arr.sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0) || a.menu_name.localeCompare(b.menu_name));
            arr.forEach((n) => sortNodes(n.children));
        };
        sortNodes(roots);
        return roots;
    };

    const flatTree = (nodes, depth = 0) => {
        const out = [];
        nodes.forEach((n) => {
            out.push({ ...n, depth, group: depth === 0 && n.children.length > 0 });
            out.push(...flatTree(n.children, depth + 1));
        });
        return out;
    };

    const render = () => {
        rightsBody.innerHTML = '';
        flatTree(buildTree(matrix)).forEach((item) => {
            const tr = document.createElement('tr');
            tr.dataset.menuId = item.menu_id;
            tr.dataset.system = item.is_system_menu ? '1' : '0';
            if (item.is_system_menu) tr.className = 'bg-orange-50';

            const name = item.depth > 0 ? `${'-- '.repeat(item.depth)}${item.menu_name}` : item.menu_name;
            const label = item.is_system_menu
                ? `${name} <span style="margin-left:8px;padding:2px 7px;border-radius:999px;background:#fed7aa;color:#9a3412;font-size:11px;font-weight:700;">RBAC/System</span>`
                : name;
            const disabled = isFullAccessRole || item.is_system_menu ? 'disabled' : '';
            const firstCell = `border border-slate-200 px-3 py-2 align-middle ${item.depth > 0 ? 'pl-7 text-slate-700' : 'text-slate-900'}${item.group ? ' bg-slate-900 text-white font-medium' : ''}`;
            const centerCell = 'border border-slate-200 px-3 py-2 text-center align-middle';

            tr.innerHTML = `
                <td class="${firstCell}">${label}</td>
                <td class="${centerCell}"><input type="checkbox" data-action="view" ${item.actions.edit ? 'checked' : ''} ${disabled}></td>
                <td class="${centerCell}"><input type="checkbox" data-action="insert" ${item.actions.create ? 'checked' : ''} ${disabled}></td>
                <td class="${centerCell}"><input type="checkbox" data-action="update" ${item.actions.update ? 'checked' : ''} ${disabled}></td>
                <td class="${centerCell}"><input type="checkbox" data-action="delete" ${item.actions.delete ? 'checked' : ''} ${disabled}></td>
            `;

            rightsBody.appendChild(tr);
        });
    };

    const load = async () => {
        setStatus('Loading role permissions...');
        const [matrixJson, menusJson] = await Promise.all([
            req(`${apiBase}/roles/${roleId}/permissions-matrix`),
            req(`${apiBase}/menus?per_page=100&sort_by=sort_order&sort_dir=asc`),
        ]);

        const menuMap = new Map((menusJson.data || []).map((m) => [m.id, m]));

        matrix = (matrixJson.matrix || []).map((m) => ({
            ...m,
            parent_id: menuMap.get(m.menu_id)?.parent_id ?? null,
            sort_order: menuMap.get(m.menu_id)?.sort_order ?? 0,
        }));

        render();
        setStatus(`Loaded ${matrix.length} menu rows.`, 'ok');
    };

    const save = async () => {
        if (isFullAccessRole) {
            setStatus('This role already has full access by default.', 'ok');
            return;
        }

        const items = Array.from(rightsBody.querySelectorAll('tr[data-menu-id]'))
            .filter((tr) => tr.dataset.system !== '1')
            .map((tr) => ({
            menu_id: Number(tr.dataset.menuId),
            create: tr.querySelector('[data-action="insert"]').checked,
            update: tr.querySelector('[data-action="update"]').checked,
            delete: tr.querySelector('[data-action="delete"]').checked,
            edit: tr.querySelector('[data-action="view"]').checked,
        }));

        setStatus('Saving changes...');
        await req(`${apiBase}/roles/${roleId}/permissions-matrix`, {
            method: 'PUT',
            body: JSON.stringify({ items }),
        });
        setStatus('Permissions saved successfully.', 'ok');
    };

    const exportRights = async (format) => {
        if (format === 'json') {
            const json = await req(`${apiBase}/roles/${roleId}/permissions-matrix/export?format=json`);
            const text = JSON.stringify({ items: (json.matrix || []).map((m) => ({
                menu_id: m.menu_id,
                view: !!m.actions.edit,
                insert: !!m.actions.create,
                update: !!m.actions.update,
                delete: !!m.actions.delete,
            })) }, null, 2);

            navigator.clipboard?.writeText(text).catch(() => {});
            setStatus('JSON copied to clipboard.', 'ok');
            return;
        }

        const csv = await req(`${apiBase}/roles/${roleId}/permissions-matrix/export?format=csv`);
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `role-${roleId}-permissions-matrix.csv`;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
        setStatus('CSV downloaded.', 'ok');
    };

    document.getElementById('saveRightsBtn').addEventListener('click', () => save().catch((e) => setStatus(e.message, 'err')));
    document.getElementById('exportJsonBtn').addEventListener('click', () => exportRights('json').catch((e) => setStatus(e.message, 'err')));
    document.getElementById('exportCsvBtn').addEventListener('click', () => exportRights('csv').catch((e) => setStatus(e.message, 'err')));

    load().catch((e) => setStatus(e.message, 'err'));
})();
</script>

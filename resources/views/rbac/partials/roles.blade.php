<div class="la-wrap">
    @include('aurix::rbac.partials.ui-styles')
    @include('aurix::rbac.partials.js-helpers')

    <div class="la-shell">
        @include('aurix::rbac.partials.brand')
        <div class="la-card">
            <div class="la-head"><h2>Roles</h2></div>
            <div class="la-body">
                <div class="la-nav">
                    <a href="{{ $setupPageUrl }}" class="la-link">Setup</a>
                    <a href="{{ $appearancePageUrl }}" class="la-link">Appearance</a>
                    <a href="{{ route('aurix.rbac.roles') }}" class="la-link active">Roles</a>
                    <a href="{{ $usersPageUrl }}" class="la-link">Users</a>
                    <a href="{{ $menusPageUrl }}" class="la-link">Menus</a>
                    @if(!empty($postsPageUrl))
                        <a href="{{ $postsPageUrl }}" class="la-link">Posts</a>
                    @endif
                </div>

                <div class="la-tools compact">
                    <div></div>
                    <div class="la-btn-row">
                        @if(!empty($postsPageUrl))
                            <a href="{{ $postsPageUrl }}" class="la-btn">Posts</a>
                        @endif
                        <a href="{{ $usersPageUrl }}" class="la-btn">Manage Users</a>
                    </div>
                </div>

                <div class="la-field">
                    <label for="roleNameInput">Role Name</label>
                    <input id="roleNameInput" class="la-input" type="text" placeholder="Enter Role Name">
                </div>

                <hr class="la-divider">

                <div class="la-row la-right">
                    <button id="saveRoleBtn" type="button" class="la-btn primary">Save changes</button>
                </div>

                <div class="la-tools">
                    <input id="rolesSearch" class="la-input search" type="text" placeholder="Search......">
                    <select id="rolesPageSize" class="la-select page-size">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>

                <div class="la-table-wrap">
                    <table class="la-table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Role name</th>
                            <th>Created date</th>
                            <th>Updated date</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody id="rolesBody"></tbody>
                    </table>
                </div>
                <div id="rolesFoot" class="la-foot"></div>
                <div id="rolesStatus" class="la-status">Loading roles...</div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const apiBase = @json($apiPrefix);
    const rightsRouteTemplate = @json($rightsRouteTemplate);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || @json(csrf_token());

    const rolesBody = document.getElementById('rolesBody');
    const rolesFoot = document.getElementById('rolesFoot');
    const rolesSearch = document.getElementById('rolesSearch');
    const rolesPageSize = document.getElementById('rolesPageSize');
    const rolesStatus = document.getElementById('rolesStatus');

    let roles = [];

    const setStatus = (text, type = 'info') => {
        rolesStatus.className = `la-status${type === 'ok' ? ' ok' : type === 'err' ? ' err' : ''}`;
        rolesStatus.textContent = text;
    };

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
        return res.json();
    };

    const fmt = (value) => {
        if (!value) return '-';
        const d = new Date(value);
        return Number.isNaN(d.getTime()) ? value : d.toLocaleString();
    };

    const render = () => {
        const q = (rolesSearch.value || '').trim().toLowerCase();
        const filtered = q ? roles.filter((r) => (`${r.name} ${r.slug}`).toLowerCase().includes(q)) : roles;

        rolesBody.innerHTML = '';
        filtered.forEach((role, idx) => {
            const url = rightsRouteTemplate.replace('__ROLE__', String(role.id));
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${idx + 1}</td>
                <td>${role.name}</td>
                <td>${fmt(role.created_at)}</td>
                <td>${fmt(role.updated_at)}</td>
                <td>
                    <a href="${url}" class="la-btn dark">Access Rights</a>
                </td>
            `;
            rolesBody.appendChild(tr);
        });

        rolesFoot.textContent = `Showing 1 to ${filtered.length} out of ${roles.length} results`;
    };

    const loadRoles = async () => {
        setStatus('Loading roles...');
        const perPage = Number(rolesPageSize.value || 10);
        const json = await req(`${apiBase}/roles?per_page=${perPage}&sort_by=name&sort_dir=asc`);
        roles = json.data || [];
        render();
        setStatus(`Loaded ${roles.length} roles.`, 'ok');
    };

    document.getElementById('saveRoleBtn').addEventListener('click', async () => {
        const input = document.getElementById('roleNameInput');
        const name = (input.value || '').trim();
        if (!name) return;

        const slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');

        try {
            await req(`${apiBase}/roles`, { method: 'POST', body: JSON.stringify({ name, slug }) });
            input.value = '';
            await loadRoles();
            setStatus('Role created successfully.', 'ok');
        } catch (e) {
            setStatus(e.message, 'err');
        }
    });

    rolesSearch.addEventListener('input', render);
    rolesPageSize.addEventListener('change', () => loadRoles().catch((e) => setStatus(e.message, 'err')));

    loadRoles().catch((e) => setStatus(e.message, 'err'));
})();
</script>

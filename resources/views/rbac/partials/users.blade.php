<div class="la-wrap">
    @include('aurix::rbac.partials.ui-styles')
    @include('aurix::rbac.partials.js-helpers')

    <div class="la-shell">
        @include('aurix::rbac.partials.brand')
        <div class="la-card">
            <div class="la-head"><h2>Users</h2></div>
            <div class="la-body">
                <div class="la-nav">
                    <a href="{{ $setupPageUrl }}" class="la-link">Setup</a>
                    <a href="{{ $appearancePageUrl }}" class="la-link">Appearance</a>
                    <a href="{{ $rolesPageUrl }}" class="la-link">Roles</a>
                    <a href="{{ route('aurix.rbac.users') }}" class="la-link active">Users</a>
                    <a href="{{ $menusPageUrl }}" class="la-link">Menus</a>
                    @if(!empty($postsPageUrl))
                        <a href="{{ $postsPageUrl }}" class="la-link">Posts</a>
                    @endif
                </div>

                <div class="la-grid users">
                    <div class="la-field"><label for="uName">Name</label><input id="uName" class="la-input" type="text" placeholder="Full name"></div>
                    <div class="la-field"><label for="uEmail">Email</label><input id="uEmail" class="la-input" type="email" placeholder="user@example.com"></div>
                    <div class="la-field"><label for="uPassword">Password</label><input id="uPassword" class="la-input" type="password" placeholder="Min 8 chars"></div>
                    <div class="la-field"><label>Roles</label><div id="uRoleGrid" class="la-role-grid"></div></div>
                    <div><button id="saveUserBtn" class="la-btn primary" type="button">Save</button></div>
                </div>

                <div class="la-tools">
                    <input id="usersSearch" class="la-input search" type="text" placeholder="Search......">
                    <select id="usersPageSize" class="la-select page-size">
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
                            <th>Name</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody id="usersBody"></tbody>
                    </table>
                </div>
                <div id="usersFoot" class="la-foot"></div>
                <div id="usersStatus" class="la-status">Loading users...</div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const apiBase = @json($apiPrefix);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || @json(csrf_token());
    const body = document.getElementById('usersBody');
    const foot = document.getElementById('usersFoot');
    const status = document.getElementById('usersStatus');
    const search = document.getElementById('usersSearch');
    const perPageSel = document.getElementById('usersPageSize');
    const roleGrid = document.getElementById('uRoleGrid');

    let users = [];
    let roles = [];
    let editingId = null;

    const setStatus = (text, type = 'info') => {
        status.className = `la-status${type === 'ok' ? ' ok' : type === 'err' ? ' err' : ''}`;
        status.textContent = text;
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

    const selectedRoleSlugs = () => Array.from(roleGrid.querySelectorAll('input[type="checkbox"]:checked')).map((x) => x.value);

    const setSelectedRoleSlugs = (slugs) => {
        const set = new Set(slugs || []);
        roleGrid.querySelectorAll('input[type="checkbox"]').forEach((cb) => {
            cb.checked = set.has(cb.value);
        });
    };

    const renderRoleGrid = () => {
        roleGrid.innerHTML = '';
        roles.forEach((r) => {
            const label = document.createElement('label');
            label.className = 'la-role-item';
            label.innerHTML = `<input type="checkbox" value="${r.slug}"> <span>${r.name} (${r.slug})</span>`;
            roleGrid.appendChild(label);
        });
    };

    const render = () => {
        const q = (search.value || '').trim().toLowerCase();
        const filtered = q ? users.filter((u) => (`${u.name} ${u.email}`).toLowerCase().includes(q)) : users;

        body.innerHTML = '';
        filtered.forEach((u, idx) => {
            const roleBadges = (u.roles || []).map((r) => `<span class="la-badge">${r.slug}</span>`).join('');
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${idx + 1}</td>
                <td>${u.name}</td>
                <td>${u.email}</td>
                <td>${roleBadges || '-'}</td>
                <td>${fmt(u.created_at)}</td>
                <td>
                    <div class="la-row-actions">
                        <button class="la-btn edit-btn" data-id="${u.id}">Edit</button>
                        <button class="la-btn danger del-btn" data-id="${u.id}">Delete</button>
                    </div>
                </td>
            `;
            body.appendChild(tr);
        });

        foot.textContent = `Showing 1 to ${filtered.length} out of ${users.length} results`;

        body.querySelectorAll('.edit-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                const id = Number(btn.dataset.id);
                const u = users.find((x) => x.id === id);
                if (!u) return;
                editingId = id;
                document.getElementById('uName').value = u.name || '';
                document.getElementById('uEmail').value = u.email || '';
                document.getElementById('uPassword').value = '';
                setSelectedRoleSlugs((u.roles || []).map((r) => r.slug));
                setStatus(`Editing user #${id}`);
            });
        });

        body.querySelectorAll('.del-btn').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const id = Number(btn.dataset.id);
                if (!confirm('Delete this user?')) return;
                try {
                    await req(`${apiBase}/users/${id}`, { method: 'DELETE' });
                    await loadUsers();
                    setStatus('User deleted.', 'ok');
                } catch (e) {
                    setStatus(e.message, 'err');
                }
            });
        });
    };

    const loadUsers = async () => {
        const perPage = Number(perPageSel.value || 10);
        const json = await req(`${apiBase}/users?per_page=${perPage}`);
        users = json.data || [];
        render();
    };

    const loadRoles = async () => {
        const json = await req(`${apiBase}/roles?per_page=100`);
        roles = json.data || [];
        renderRoleGrid();
    };

    document.getElementById('saveUserBtn').addEventListener('click', async () => {
        const name = (document.getElementById('uName').value || '').trim();
        const email = (document.getElementById('uEmail').value || '').trim();
        const password = (document.getElementById('uPassword').value || '').trim();
        const selectedRoles = selectedRoleSlugs();

        if (!name || !email) return;

        const payload = { name, email, roles: selectedRoles };
        if (password) payload.password = password;

        try {
            if (editingId) {
                await req(`${apiBase}/users/${editingId}`, { method: 'PUT', body: JSON.stringify(payload) });
                setStatus('User updated.', 'ok');
            } else {
                if (!password) return setStatus('Password is required for new users.', 'err');
                await req(`${apiBase}/users`, { method: 'POST', body: JSON.stringify(payload) });
                setStatus('User created.', 'ok');
            }

            editingId = null;
            document.getElementById('uName').value = '';
            document.getElementById('uEmail').value = '';
            document.getElementById('uPassword').value = '';
            setSelectedRoleSlugs([]);
            await loadUsers();
        } catch (e) {
            setStatus(e.message, 'err');
        }
    });

    search.addEventListener('input', render);
    perPageSel.addEventListener('change', () => loadUsers().catch((e) => setStatus(e.message, 'err')));

    (async () => {
        setStatus('Loading users and roles...');
        await loadRoles();
        await loadUsers();
        setStatus(`Loaded ${users.length} users.`, 'ok');
    })().catch((e) => setStatus(e.message, 'err'));
})();
</script>

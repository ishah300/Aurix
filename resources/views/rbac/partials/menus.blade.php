<div class="la-wrap">
    @include('aurix::rbac.partials.ui-styles')
    @include('aurix::rbac.partials.js-helpers')

    <div class="la-shell">
        @include('aurix::rbac.partials.brand')
        <div class="la-card">
            <div class="la-head"><h2>Menus</h2></div>
            <div class="la-body">
                <div class="la-nav">
                    <a href="{{ $setupPageUrl }}" class="la-link">Setup</a>
                    <a href="{{ $appearancePageUrl }}" class="la-link">Appearance</a>
                    <a href="{{ $rolesPageUrl }}" class="la-link">Roles</a>
                    <a href="{{ $usersPageUrl }}" class="la-link">Users</a>
                    <a href="{{ route('aurix.rbac.menus') }}" class="la-link active">Menus</a>
                    @if(!empty($postsPageUrl))
                        <a href="{{ $postsPageUrl }}" class="la-link">Posts</a>
                    @endif
                </div>

                <div class="la-grid menus">
                    <div class="la-field"><label for="mName">Menu Title</label><input id="mName" class="la-input" type="text" placeholder="Menu title"></div>
                    <div class="la-field"><label for="mSlug">Menu Slug</label><input id="mSlug" class="la-input" type="text" placeholder="menu-slug"></div>
                    <div class="la-field"><label for="mRoute">Route</label><input id="mRoute" class="la-input" type="text" placeholder="/menu-route"></div>
                    <div class="la-field"><label for="mSort">Menu Sort Order</label><input id="mSort" class="la-input" type="number" min="0" value="0"></div>
                    <div class="la-field"><label for="mParent">Menu Parent</label><select id="mParent" class="la-select"><option value="">None</option></select></div>
                    <div><button id="saveMenuBtn" class="la-btn primary" type="button">Save</button></div>
                </div>

                <div class="la-tools">
                    <div class="la-btn-row">
                        <button id="reloadTreeBtn" class="la-btn" type="button">Reload Tree</button>
                        <button id="saveTreeBtn" class="la-btn dark" type="button">Save Drag Order</button>
                    </div>
                </div>
                <div id="menuTreeStatus" class="la-status">Drag menu rows to reorder or drop into child zones to create parent/child menus.</div>
                <div class="la-tree-wrap">
                    <div id="menuRootDrop" class="la-tree-drop-root">Drop here to move as root menu</div>
                    <ul id="menuTree" class="la-tree"></ul>
                </div>

                <div class="la-tools">
                    <input id="menusSearch" class="la-input search" type="text" placeholder="Search......">
                    <select id="menusPageSize" class="la-select page-size">
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
                            <th>Menu ID</th>
                            <th>Menu Title</th>
                            <th>Menu Slug</th>
                            <th>Route</th>
                            <th>Menu Parent ID</th>
                            <th>Menu Sort Order</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody id="menusBody"></tbody>
                    </table>
                </div>
                <div id="menusFoot" class="la-foot"></div>
                <div id="menusStatus" class="la-status">Loading menus...</div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const apiBase = @json($apiPrefix);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || @json(csrf_token());
    const escapeHtml = window.Aurix?.escapeHtml ?? ((value) => value ?? '');
    const body = document.getElementById('menusBody');
    const foot = document.getElementById('menusFoot');
    const status = document.getElementById('menusStatus');
    const treeStatus = document.getElementById('menuTreeStatus');
    const search = document.getElementById('menusSearch');
    const perPageSel = document.getElementById('menusPageSize');
    const parentSel = document.getElementById('mParent');
    const treeEl = document.getElementById('menuTree');
    const rootDropEl = document.getElementById('menuRootDrop');

    let menus = [];
    let editingId = null;
    let draggingMenuId = null;
    let hasPendingTreeChanges = false;

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

    const setTreeStatus = (text, type = 'info') => {
        treeStatus.className = `la-status${type === 'ok' ? ' ok' : type === 'err' ? ' err' : ''}`;
        treeStatus.textContent = text;
    };

    const normalizeParent = (value) => (value === null || value === undefined || value === '' ? null : Number(value));
    const parentName = (id) => menus.find((m) => Number(m.id) === Number(id))?.name || '-';
    const byId = (id) => menus.find((m) => Number(m.id) === Number(id)) || null;
    const orderedSiblings = (parentId) => {
        const key = normalizeParent(parentId);
        return menus
            .filter((m) => normalizeParent(m.parent_id) === key)
            .sort((a, b) => (Number(a.sort_order ?? 0) - Number(b.sort_order ?? 0)) || (Number(a.id) - Number(b.id)));
    };

    const assignSortForParent = (parentId) => {
        orderedSiblings(parentId).forEach((item, index) => {
            item.sort_order = index + 1;
        });
    };

    const isDescendant = (candidateParentId, childId) => {
        let cursor = normalizeParent(candidateParentId);
        const child = Number(childId);
        const seen = new Set();

        while (cursor !== null) {
            if (cursor === child) return true;
            if (seen.has(cursor)) return true;
            seen.add(cursor);
            const parent = byId(cursor);
            cursor = parent ? normalizeParent(parent.parent_id) : null;
        }

        return false;
    };

    const moveMenu = (dragId, targetParentId, targetIndex = null) => {
        const drag = byId(dragId);
        if (!drag) return;

        const nextParent = normalizeParent(targetParentId);
        const oldParent = normalizeParent(drag.parent_id);

        if (nextParent !== null && nextParent === Number(dragId)) {
            return setTreeStatus('A menu cannot be parent of itself.', 'err');
        }

        if (nextParent !== null && isDescendant(nextParent, dragId)) {
            return setTreeStatus('Invalid move: a child cannot become parent of its ancestor.', 'err');
        }

        const targetSiblings = orderedSiblings(nextParent).filter((m) => Number(m.id) !== Number(dragId));
        const insertAt = targetIndex === null ? targetSiblings.length : Math.max(0, Math.min(targetIndex, targetSiblings.length));
        targetSiblings.splice(insertAt, 0, drag);

        drag.parent_id = nextParent;
        targetSiblings.forEach((item, index) => {
            item.sort_order = index + 1;
        });
        assignSortForParent(oldParent);

        hasPendingTreeChanges = true;
        renderTree();
        render();
        setTreeStatus('Menu hierarchy changed. Click "Save Drag Order" to persist.', 'ok');
    };

    const serializeTreePayload = () => ({
        items: menus.map((m) => ({
            id: Number(m.id),
            parent_id: normalizeParent(m.parent_id),
            sort_order: Number(m.sort_order ?? 0),
        })),
    });

    const rebuildParentOptions = () => {
        const current = parentSel.value;
        parentSel.innerHTML = '<option value="">None</option>';
        menus.forEach((m) => {
            const opt = document.createElement('option');
            opt.value = String(m.id);
            opt.textContent = `${m.name} (${m.slug})`;
            parentSel.appendChild(opt);
        });
        parentSel.value = current;
    };

    const buildTree = (parentId = null) => orderedSiblings(parentId).map((menu) => ({
        ...menu,
        children: buildTree(menu.id),
    }));

    const makeDropHandlers = (el, onDrop) => {
        el.addEventListener('dragover', (e) => {
            e.preventDefault();
            el.classList.add('active');
        });
        el.addEventListener('dragleave', () => el.classList.remove('active'));
        el.addEventListener('drop', (e) => {
            e.preventDefault();
            el.classList.remove('active');
            if (draggingMenuId === null) return;
            onDrop(draggingMenuId);
        });
    };

    const renderTree = () => {
        treeEl.innerHTML = '';
        const roots = buildTree(null);

        const renderNodes = (nodes, parentEl) => {
            nodes.forEach((node, index) => {
                const li = document.createElement('li');
                li.className = 'la-tree-item';

                const row = document.createElement('div');
                row.className = 'la-tree-row';
                row.draggable = true;
                row.dataset.id = String(node.id);
                const safeName = escapeHtml(node.name ?? '');
                const safeSlug = escapeHtml(node.slug ?? '');
                const safeSort = escapeHtml(node.sort_order ?? 0);
                const safeId = escapeHtml(node.id ?? '');
                row.innerHTML = `
                    <div class="la-tree-left">
                        <span class="la-drag-handle">&#x2630;</span>
                        <div>
                            <div class="la-tree-title">${safeName}</div>
                            <div class="la-tree-meta">id:${safeId} | slug:${safeSlug} | sort:${safeSort}</div>
                        </div>
                    </div>
                    <button type="button" class="la-btn" data-root-id="${safeId}">Move to Root</button>
                `;

                row.addEventListener('dragstart', () => {
                    draggingMenuId = Number(node.id);
                    row.classList.add('active');
                });
                row.addEventListener('dragend', () => {
                    draggingMenuId = null;
                    row.classList.remove('active');
                });

                makeDropHandlers(row, (dragId) => {
                    const siblings = orderedSiblings(node.parent_id);
                    const targetIndex = siblings.findIndex((s) => Number(s.id) === Number(node.id));
                    moveMenu(dragId, node.parent_id, targetIndex);
                });

                row.querySelector('[data-root-id]')?.addEventListener('click', () => {
                    moveMenu(node.id, null, null);
                });

                row.innerHTML = `
                    <div class="la-tree-left">
                        <span class="la-drag-handle">&#x2630;</span>
                        <div>
                            <div class="la-tree-title">${escapeHtml(node.name)}</div>
                            <div class="la-tree-meta">id:${escapeHtml(node.id)} | slug:${escapeHtml(node.slug)} | sort:${escapeHtml(node.sort_order ?? 0)}</div>
                        </div>
                    </div>
                    <button type="button" class="la-btn" data-root-id="${escapeHtml(node.id)}">Move to Root</button>
                `;

                li.appendChild(row);

                const childDrop = document.createElement('div');
                childDrop.className = 'la-tree-child-drop';
                childDrop.textContent = `Drop here to make child of "${node.name}"`;
                makeDropHandlers(childDrop, (dragId) => {
                    moveMenu(dragId, node.id, null);
                });
                li.appendChild(childDrop);

                if (node.children.length) {
                    const ul = document.createElement('ul');
                    ul.className = 'la-tree-children';
                    renderNodes(node.children, ul);
                    li.appendChild(ul);
                }

                parentEl.appendChild(li);
            });
        };

        renderNodes(roots, treeEl);
    };

    const render = () => {
        const q = (search.value || '').trim().toLowerCase();
        const filtered = q ? menus.filter((m) => (`${m.name} ${m.slug} ${m.route || ''}`).toLowerCase().includes(q)) : menus;

        body.innerHTML = '';
        filtered.forEach((m) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${m.id}</td>
                <td>${m.name}</td>
                <td>${m.slug}</td>
                <td>${m.route || '-'}</td>
                <td>${m.parent_id ?? '-'}</td>
                <td>${m.sort_order ?? 0}</td>
                <td>
                    <div class="la-row-actions">
                        <button class="la-btn edit-btn" data-id="${m.id}">Edit</button>
                        <button class="la-btn danger del-btn" data-id="${m.id}">Delete</button>
                    </div>
                </td>
            `;
            body.appendChild(tr);
        });

        foot.textContent = `Showing 1 to ${filtered.length} out of ${menus.length} results`;

        body.querySelectorAll('.edit-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                const id = Number(btn.dataset.id);
                const m = menus.find((x) => x.id === id);
                if (!m) return;
                editingId = id;
                document.getElementById('mName').value = m.name || '';
                document.getElementById('mSlug').value = m.slug || '';
                document.getElementById('mRoute').value = m.route || '';
                document.getElementById('mSort').value = String(m.sort_order ?? 0);
                parentSel.value = m.parent_id ? String(m.parent_id) : '';
                setStatus(`Editing menu #${id}`);
            });
        });

        body.querySelectorAll('.del-btn').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const id = Number(btn.dataset.id);
                if (!confirm('Delete this menu?')) return;
                try {
                    await req(`${apiBase}/menus/${id}`, { method: 'DELETE' });
                    await load();
                    setStatus('Menu deleted.', 'ok');
                } catch (e) {
                    setStatus(e.message, 'err');
                }
            });
        });
    };

    const load = async () => {
        const perPage = Number(perPageSel.value || 10);
        setStatus('Loading menus...');
        setTreeStatus('Loading menu hierarchy...');
        const json = await req(`${apiBase}/menus?per_page=${perPage}&sort_by=sort_order&sort_dir=asc`);
        menus = json.data || [];
        hasPendingTreeChanges = false;
        rebuildParentOptions();
        renderTree();
        render();
        setStatus(`Loaded ${menus.length} menus.`, 'ok');
        setTreeStatus('Drag menus to change sort order and child relations.', 'ok');
    };

    document.getElementById('saveMenuBtn').addEventListener('click', async () => {
        const name = (document.getElementById('mName').value || '').trim();
        const slug = (document.getElementById('mSlug').value || '').trim();
        const route = (document.getElementById('mRoute').value || '').trim();
        const sort_order = Number(document.getElementById('mSort').value || 0);
        const parent_id = parentSel.value ? Number(parentSel.value) : null;

        if (!name || !slug) return;

        const payload = { menu_title: name, menu_slug: slug, route: route || null, menu_sort_order: sort_order, menu_parent_id: parent_id };

        try {
            if (editingId) {
                await req(`${apiBase}/menus/${editingId}`, { method: 'PUT', body: JSON.stringify(payload) });
                setStatus('Menu updated.', 'ok');
            } else {
                await req(`${apiBase}/menus`, { method: 'POST', body: JSON.stringify(payload) });
                setStatus('Menu created.', 'ok');
            }

            editingId = null;
            document.getElementById('mName').value = '';
            document.getElementById('mSlug').value = '';
            document.getElementById('mRoute').value = '';
            document.getElementById('mSort').value = '0';
            parentSel.value = '';
            await load();
        } catch (e) {
            setStatus(e.message, 'err');
        }
    });

    makeDropHandlers(rootDropEl, (dragId) => moveMenu(dragId, null, null));

    document.getElementById('reloadTreeBtn').addEventListener('click', () => {
        load().catch((e) => setTreeStatus(e.message, 'err'));
    });

    document.getElementById('saveTreeBtn').addEventListener('click', async () => {
        if (!hasPendingTreeChanges) {
            return setTreeStatus('No tree changes to save.', 'ok');
        }

        try {
            await req(`${apiBase}/menus/reorder`, {
                method: 'PUT',
                body: JSON.stringify(serializeTreePayload()),
            });
            hasPendingTreeChanges = false;
            setTreeStatus('Menu drag order saved successfully.', 'ok');
            setStatus('Menu order saved.', 'ok');
            await load();
        } catch (e) {
            setTreeStatus(e.message, 'err');
        }
    });

    search.addEventListener('input', render);
    perPageSel.addEventListener('change', () => load().catch((e) => setStatus(e.message, 'err')));

    load().catch((e) => setStatus(e.message, 'err'));
})();
</script>

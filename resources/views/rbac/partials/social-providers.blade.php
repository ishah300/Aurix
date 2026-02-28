@php
    $providerPreviewRoutes = [
        ['label' => 'Login', 'url' => route('aurix.rbac.appearance.preview', ['screen' => 'login'])],
        ['label' => 'Register', 'url' => route('aurix.rbac.appearance.preview', ['screen' => 'register'])],
        ['label' => 'Forgot Password', 'url' => route('aurix.rbac.appearance.preview', ['screen' => 'forgot'])],
    ];

    $providerIconMap = [];
    foreach ([
        'facebook',
        'linkedin',
        'github',
        'bitbucket',
        'apple',
        'google',
        'twitter',
        'gitlab',
        'slack',
        'microsoft',
        'reddit',
        'twitch',
        'pinterest',
        'tiktok',
    ] as $providerSlug) {
        $providerIconMap[$providerSlug] = trim((string) view('aurix::rbac.partials._provider_icons', ['slug' => $providerSlug])->render());
    }
@endphp

<div class="la-wrap la-wrap-setup">
    @include('aurix::rbac.partials.ui-styles')

    <div class="la-shell">
        <div class="la-card">
            <div class="la-body la-body-setup">
                <div class="la-setup-grid">
                    <aside class="la-setup-sidebar">
                        <h3><span class="text-base font-bold leading-none">Authentication <span class="font-light">Setup</span></span></h3>
                        <div class="la-setup-side-group">Configure</div>
                        <a href="{{ $setupPageUrl }}" class="la-setup-link">
                            <span class="la-setup-link-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M4 11.5L12 5l8 6.5V19a1 1 0 0 1-1 1h-5v-5H10v5H5a1 1 0 0 1-1-1v-7.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
                            </span>
                            <span>Home</span>
                        </a>
                        <a href="{{ route('aurix.rbac.appearance') }}" class="la-setup-link">
                            <span class="la-setup-link-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M12 20a8 8 0 1 1 8-8 4 4 0 0 1-4 4h-1a2 2 0 0 0-2 2v2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="7.5" cy="11" r="1" fill="currentColor"/><circle cx="10.5" cy="8.5" r="1" fill="currentColor"/><circle cx="14" cy="8.5" r="1" fill="currentColor"/></svg>
                            </span>
                            <span>Appearance</span>
                        </a>
                        <a href="{{ route('aurix.rbac.providers') }}" class="la-setup-link active">
                            <span class="la-setup-link-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8"/><path d="M12 8v8M8 12h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            </span>
                            <span>Social Providers</span>
                        </a>
                        <a href="javascript:void(0)" class="la-setup-link disabled">
                            <span class="la-setup-link-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M4 18.5V6.5a1 1 0 0 1 1-1h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M8 8.5h8M8 12h8M8 15.5h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            </span>
                            <span>Language</span>
                        </a>
                        <a href="javascript:void(0)" class="la-setup-link disabled">
                            <span class="la-setup-link-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/><path d="M12 4v2.5M12 17.5V20M4 12h2.5M17.5 12H20M6.3 6.3l1.8 1.8M15.9 15.9l1.8 1.8M17.7 6.3l-1.8 1.8M8.1 15.9l-1.8 1.8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            </span>
                            <span>Settings</span>
                        </a>
                        <div class="la-setup-side-group resources">Resources</div>
                        <a href="https://github.com/imranshah/Aurix" target="_blank" rel="noopener" class="la-setup-link">
                            <span class="la-setup-link-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M9.5 18.5c-3.8 1.2-3.8-1.7-5.3-2.1m10.6 4.2v-3a2.6 2.6 0 0 0-.8-2c2.7-.3 5.5-1.3 5.5-6a4.7 4.7 0 0 0-1.3-3.3 4.4 4.4 0 0 0-.1-3.3s-1-.3-3.4 1.3a11.5 11.5 0 0 0-6.2 0C6.1 3 5.1 3.3 5.1 3.3a4.4 4.4 0 0 0-.1 3.3 4.7 4.7 0 0 0-1.3 3.3c0 4.7 2.8 5.7 5.5 6a2.6 2.6 0 0 0-.8 2v3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <span>Github Repo</span>
                        </a>
                        <a href="https://github.com/imranshah/Aurix#readme" target="_blank" rel="noopener" class="la-setup-link">
                            <span class="la-setup-link-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M7 5.5h10a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-11a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.8"/><path d="M9 9h6M9 12h6M9 15h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            </span>
                            <span>Documentation</span>
                        </a>
                    </aside>

                    <section class="la-setup-main space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <a href="{{ $setupPageUrl }}" class="inline-flex items-center rounded-full border border-slate-200 bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-200">&larr; Back</a>
                            <button type="button" id="openProvidersPreviewBtn" class="inline-flex items-center rounded-full border border-slate-200 bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-200">Preview</button>
                        </div>

                        <div>
                            <h2 class="text-[40px] font-semibold leading-none tracking-tight text-slate-900">Social Providers</h2>
                            <p class="mt-1 text-[15px] text-slate-600">Select the social networks that users can use for authentication</p>
                        </div>

                        <div id="providersStatus" class="hidden rounded-b border-t-4 border-teal-500 bg-teal-100 px-4 py-2 text-teal-900 shadow-md" role="alert">
                            <p id="providersStatusTitle" class="text-sm font-bold leading-tight">Social providers</p>
                            <p id="providersStatusMessage" class="text-xs leading-5">Loading providers...</p>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-white p-3 sm:p-4">
                            <div id="providersGrid" class="grid grid-cols-1 gap-x-8 md:grid-cols-2">
                                <div id="providersLeft"></div>
                                <div id="providersRight"></div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="providersPreviewDrawer" class="fixed inset-0 z-[70] bg-slate-900/40 p-4 opacity-0 pointer-events-none transition-opacity duration-300 ease-out" aria-hidden="true">
    <div id="providersPreviewPanel" class="mx-auto flex h-full w-full max-w-[1200px] flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl transform translate-y-8 opacity-0 transition-all duration-300 ease-out">
        <div class="flex items-center justify-between gap-2 border-b border-slate-200 p-3">
            <div class="flex items-center gap-2">
                <label for="providersPreviewRouteSelect" class="sr-only">Preview Route</label>
                <select id="providersPreviewRouteSelect" class="h-9 rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-700">
                    @foreach($providerPreviewRoutes as $route)
                        <option value="{{ $route['url'] }}">{{ $route['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <a id="providersPreviewOpenNewTab" href="{{ $providerPreviewRoutes[0]['url'] ?? '#' }}" target="_blank" rel="noopener" class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700">Preview in New Tab</a>
                <button id="providersPreviewCloseBtn" type="button" class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700">Close</button>
            </div>
        </div>
        <div class="h-full bg-slate-50">
            <iframe id="providersPreviewFrame" src="{{ $providerPreviewRoutes[0]['url'] ?? '' }}" title="Providers Preview Frame" class="h-full w-full border-0"></iframe>
        </div>
    </div>
</div>

<script>
(() => {
    const apiBase = @json('/' . trim((string) config('aurix.api.prefix', 'api/auth'), '/'));
    const providersIndexUrl = `${apiBase}/providers`;
    const providersSeedUrl = `${apiBase}/providers/seed`;
    const ICON_SVGS = @json($providerIconMap);
    const fallbackIcon = '<svg width="20" height="20" viewBox="0 0 20 20"><circle cx="10" cy="10" r="9" fill="#e5e7eb"/></svg>';

    const status = document.getElementById('providersStatus');
    const statusTitle = document.getElementById('providersStatusTitle');
    const statusMessage = document.getElementById('providersStatusMessage');
    const left = document.getElementById('providersLeft');
    const right = document.getElementById('providersRight');
    const openPreviewBtn = document.getElementById('openProvidersPreviewBtn');
    const previewDrawer = document.getElementById('providersPreviewDrawer');
    const previewCloseBtn = document.getElementById('providersPreviewCloseBtn');
    const previewRouteSelect = document.getElementById('providersPreviewRouteSelect');
    const previewFrame = document.getElementById('providersPreviewFrame');
    const previewOpenNewTab = document.getElementById('providersPreviewOpenNewTab');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const escapeHtml = (v) => String(v ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const setStatus = (message, kind = 'info') => {
        const klass = kind === 'err'
            ? 'block rounded-b border-t-4 border-red-500 bg-red-100 px-4 py-2 text-red-900 shadow-md'
            : kind === 'ok'
                ? 'block rounded-b border-t-4 border-teal-500 bg-teal-100 px-4 py-2 text-teal-900 shadow-md'
                : 'block rounded-b border-t-4 border-slate-400 bg-slate-100 px-4 py-2 text-slate-900 shadow-md';

        status.className = klass;
        if (statusTitle) {
            statusTitle.textContent = kind === 'err' ? 'Request failed' : kind === 'ok' ? 'Social providers loaded' : 'Loading providers';
        }
        if (statusMessage) {
            statusMessage.textContent = message;
        }
    };

    const req = async (url, options = {}) => {
        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                ...(options.headers || {}),
            },
            ...options,
        });

        const text = await response.text();
        let body = null;
        try {
            body = text ? JSON.parse(text) : null;
        } catch (_) {
            body = null;
        }

        if (!response.ok) {
            throw new Error(body?.message || text || `Request failed (${response.status})`);
        }

        return body;
    };

    const sizedIcon = (svgMarkup) => {
        const raw = String(svgMarkup || fallbackIcon);
        return raw.replace(
            /<svg\b([^>]*)>/i,
            '<svg$1 width="20" height="20" style="width:20px;height:20px;max-width:20px;max-height:20px;display:block;" aria-hidden="true">'
        );
    };

    const settingsIcon = `
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M14.5 3.5a2.5 2.5 0 1 0 5 0 2.5 2.5 0 0 0-5 0ZM11 13l7.3-7.3M10 14l-3 7 7-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>`;
    const MODAL_TRANSITION_MS = 300;
    const isPreviewOpen = () => previewDrawer?.getAttribute('aria-hidden') === 'false';
    const withPreviewCacheBust = (url) => {
        if (!url) return '';

        try {
            const parsed = new URL(url, window.location.origin);
            parsed.searchParams.set('_preview', Date.now().toString());
            return parsed.toString();
        } catch (_) {
            const joiner = url.includes('?') ? '&' : '?';
            return `${url}${joiner}_preview=${Date.now()}`;
        }
    };
    const refreshPreview = () => {
        if (!previewFrame || !previewOpenNewTab) return;
        const baseUrl = (previewRouteSelect?.value || previewFrame.getAttribute('src') || '').trim();
        if (!baseUrl) return;

        const nextUrl = withPreviewCacheBust(baseUrl);
        previewFrame.src = nextUrl;
        previewOpenNewTab.href = nextUrl;
    };
    const refreshPreviewIfOpen = () => {
        if (isPreviewOpen()) {
            refreshPreview();
        }
    };

    const renderToggle = (provider) => {
        const label = document.createElement('label');
        label.className = 'relative inline-flex h-6 w-11 items-center';

        const input = document.createElement('input');
        input.type = 'checkbox';
        input.className = 'peer sr-only';
        input.checked = !!provider.enabled;
        input.disabled = !!provider.requires_package || !!provider.coming_soon;

        const slider = document.createElement('span');
        slider.className = 'absolute inset-0 rounded-full bg-slate-200 transition peer-checked:bg-indigo-500 peer-disabled:opacity-60';

        const knob = document.createElement('span');
        knob.className = 'absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5';

        input.addEventListener('change', async () => {
            try {
                await req(`${apiBase}/providers/${provider.id}/toggle`, { method: 'POST' });
                setStatus(`${provider.name} ${input.checked ? 'enabled' : 'disabled'}.`, 'ok');
                await load();
                refreshPreviewIfOpen();
            } catch (error) {
                input.checked = !input.checked;
                setStatus(`Toggle failed: ${error.message}`, 'err');
            }
        });

        label.appendChild(input);
        label.appendChild(slider);
        label.appendChild(knob);

        return label;
    };

    const openSettingsModal = (provider) => {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-[90] flex items-center justify-center bg-slate-900/40 p-4 opacity-0 pointer-events-none transition-opacity duration-300 ease-out';

        const panel = document.createElement('div');
        panel.className = 'w-full max-w-xl rounded-xl border border-slate-200 bg-white p-4 shadow-xl sm:p-5 transform translate-y-4 scale-[.98] opacity-0 transition-all duration-300 ease-out';
        panel.innerHTML = `
            <h3 class="mb-3 text-xl font-semibold text-slate-900">${escapeHtml(provider.name)} Settings</h3>
            <div class="grid gap-2.5">
                <div>
                    <label for="spClientId" class="mb-1 block text-sm font-medium text-slate-700">Client ID</label>
                    <input id="spClientId" class="h-10 w-full rounded-md border border-slate-300 px-3 text-sm text-slate-900" value="${escapeHtml(provider.client_id || '')}">
                </div>
                <div>
                    <label for="spClientSecret" class="mb-1 block text-sm font-medium text-slate-700">Client Secret</label>
                    <input id="spClientSecret" class="h-10 w-full rounded-md border border-slate-300 px-3 text-sm text-slate-900" value="" placeholder="Enter new secret to update">
                </div>
                <div>
                    <label for="spRedirect" class="mb-1 block text-sm font-medium text-slate-700">Redirect URI</label>
                    <input id="spRedirect" class="h-10 w-full rounded-md border border-slate-300 px-3 text-sm text-slate-900" value="${escapeHtml(provider.redirect || '')}">
                </div>
                <div>
                    <label for="spScopes" class="mb-1 block text-sm font-medium text-slate-700">Scopes (space-separated)</label>
                    <input id="spScopes" class="h-10 w-full rounded-md border border-slate-300 px-3 text-sm text-slate-900" value="${escapeHtml(provider.scopes || '')}">
                </div>
            </div>
            <div class="mt-3 flex justify-end gap-2">
                <button id="spCancel" class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700">Cancel</button>
                <button id="spSave" class="inline-flex items-center rounded-md border border-slate-900 bg-slate-900 px-3 py-1.5 text-sm font-medium text-white">Save</button>
            </div>
        `;

        modal.appendChild(panel);
        document.body.appendChild(modal);

        requestAnimationFrame(() => {
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100', 'pointer-events-auto');
            panel.classList.remove('translate-y-4', 'scale-[.98]', 'opacity-0');
            panel.classList.add('translate-y-0', 'scale-100', 'opacity-100');
        });

        let closing = false;
        const close = () => {
            if (closing) return;
            closing = true;

            modal.classList.remove('opacity-100', 'pointer-events-auto');
            modal.classList.add('opacity-0', 'pointer-events-none');
            panel.classList.remove('translate-y-0', 'scale-100', 'opacity-100');
            panel.classList.add('translate-y-4', 'scale-[.98]', 'opacity-0');

            window.setTimeout(() => modal.remove(), MODAL_TRANSITION_MS);
        };
        panel.querySelector('#spCancel')?.addEventListener('click', close);
        modal.addEventListener('click', (event) => {
            if (event.target === modal) close();
        });

        panel.querySelector('#spSave')?.addEventListener('click', async () => {
            const payload = {
                client_id: panel.querySelector('#spClientId')?.value?.trim() || null,
                client_secret: panel.querySelector('#spClientSecret')?.value?.trim() || null,
                redirect: panel.querySelector('#spRedirect')?.value?.trim() || null,
                scopes: panel.querySelector('#spScopes')?.value?.trim() || null,
            };

            try {
                await req(`${apiBase}/providers/${provider.id}`, {
                    method: 'PUT',
                    body: JSON.stringify(payload),
                });
                close();
                setStatus('Provider settings saved.', 'ok');
                await load();
                refreshPreviewIfOpen();
            } catch (error) {
                setStatus(`Failed to save provider: ${error.message}`, 'err');
            }
        });
    };

    const renderProviders = (providers) => {
        left.innerHTML = '';
        right.innerHTML = '';

        providers.forEach((provider, index) => {
            const column = index % 2 === 0 ? left : right;

            const row = document.createElement('div');
            row.className = 'flex items-center justify-between gap-3 border-b border-slate-200 py-3';

            const leftWrap = document.createElement('div');
            leftWrap.className = 'flex min-w-0 items-center gap-2.5';

            const icon = document.createElement('span');
            icon.className = 'inline-flex h-6 w-6 shrink-0 items-center justify-center overflow-hidden';
            icon.innerHTML = sizedIcon(ICON_SVGS[provider.slug] || fallbackIcon);

            const label = document.createElement('span');
            label.className = 'truncate text-base font-semibold text-slate-900';
            label.textContent = provider.name;

            const comingSoonBadge = document.createElement('span');
            comingSoonBadge.className = 'rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600';
            comingSoonBadge.textContent = 'Coming Soon';

            const settingsBtn = document.createElement('button');
            settingsBtn.type = 'button';
            settingsBtn.className = 'inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-500 hover:bg-rose-200 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400';
            settingsBtn.innerHTML = settingsIcon;
            settingsBtn.disabled = !!provider.coming_soon;
            if (!provider.coming_soon) {
                settingsBtn.addEventListener('click', () => openSettingsModal(provider));
            }

            leftWrap.appendChild(icon);
            leftWrap.appendChild(label);
            if (provider.coming_soon) {
                leftWrap.appendChild(comingSoonBadge);
            }
            leftWrap.appendChild(settingsBtn);

            const rightWrap = document.createElement('div');
            rightWrap.className = 'flex items-center gap-2';

            if (provider.requires_package) {
                const badge = document.createElement('span');
                badge.className = 'rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-medium text-amber-700';
                badge.textContent = 'Requires Package';
                rightWrap.appendChild(badge);
            }

            rightWrap.appendChild(renderToggle(provider));
            row.appendChild(leftWrap);
            row.appendChild(rightWrap);
            column.appendChild(row);
        });
    };

    const renderSeedAction = () => {
        left.innerHTML = '<div class="py-4 text-sm text-slate-500">No providers found. Seed the default provider catalog.</div>';

        const actions = document.createElement('div');
        actions.className = 'mt-2';

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'inline-flex items-center rounded-md border border-slate-900 bg-slate-900 px-3 py-1.5 text-sm font-medium text-white';
        button.textContent = 'Seed Default Providers';
        button.addEventListener('click', async () => {
            try {
                await req(providersSeedUrl, { method: 'POST' });
                setStatus('Providers seeded successfully.', 'ok');
                await load();
                refreshPreviewIfOpen();
            } catch (error) {
                setStatus(`Seeding failed: ${error.message}`, 'err');
            }
        });

        actions.appendChild(button);
        left.appendChild(actions);
        right.innerHTML = '';
    };

    const load = async () => {
        try {
            const json = await req(providersIndexUrl);
            const providers = Array.isArray(json?.data) ? json.data : [];

            if (providers.length === 0) {
                renderSeedAction();
                setStatus('No providers found yet.', 'info');
                return;
            }

            renderProviders(providers);
            setStatus('Social providers loaded.', 'ok');
        } catch (error) {
            left.innerHTML = `<div class="py-4 text-sm text-red-600">Failed to load providers: ${escapeHtml(error.message)}</div>`;
            right.innerHTML = '';
            setStatus('Failed to load providers.', 'err');
        }
    };

    const openPreview = () => {
        if (!previewDrawer) return;
        refreshPreview();
        previewDrawer.classList.remove('opacity-0', 'pointer-events-none');
        previewDrawer.classList.add('opacity-100');
        const previewPanel = document.getElementById('providersPreviewPanel');
        if (previewPanel) {
            previewPanel.classList.remove('translate-y-8', 'opacity-0');
            previewPanel.classList.add('translate-y-0', 'opacity-100');
        }
        previewDrawer.setAttribute('aria-hidden', 'false');
    };

    const closePreview = () => {
        if (!previewDrawer) return;
        previewDrawer.classList.remove('opacity-100');
        previewDrawer.classList.add('opacity-0', 'pointer-events-none');
        const previewPanel = document.getElementById('providersPreviewPanel');
        if (previewPanel) {
            previewPanel.classList.remove('translate-y-0', 'opacity-100');
            previewPanel.classList.add('translate-y-8', 'opacity-0');
        }
        previewDrawer.setAttribute('aria-hidden', 'true');
    };

    if (openPreviewBtn) {
        openPreviewBtn.addEventListener('click', openPreview);
    }

    if (previewCloseBtn) {
        previewCloseBtn.addEventListener('click', closePreview);
    }

    if (previewDrawer) {
        previewDrawer.addEventListener('click', (event) => {
            if (event.target === previewDrawer) {
                closePreview();
            }
        });
    }

    if (previewRouteSelect && previewFrame && previewOpenNewTab) {
        previewRouteSelect.addEventListener('change', () => {
            refreshPreview();
        });
    }

    load();
})();
</script>

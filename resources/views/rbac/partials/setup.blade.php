<div class="la-wrap la-wrap-setup">
    @include('aurix::rbac.partials.ui-styles')

    <div class="la-shell">
        <div class="la-card">
            <div class="la-body la-body-setup">
                <div class="la-setup-grid">
                    <aside class="la-setup-sidebar">
                        <h3><span class="text-base font-bold leading-none">Authentication <span class="font-light">Setup</span></span></h3>
                        <div class="la-setup-side-group">Configure</div>
                        <a href="{{ $setupPageUrl }}" class="la-setup-link active">
                            <span class="la-setup-link-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M4 11.5L12 5l8 6.5V19a1 1 0 0 1-1 1h-5v-5H10v5H5a1 1 0 0 1-1-1v-7.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
                            </span>
                            <span>Home</span>
                        </a>
                        <a href="{{ $appearancePageUrl }}" class="la-setup-link">
                            <span class="la-setup-link-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M12 20a8 8 0 1 1 8-8 4 4 0 0 1-4 4h-1a2 2 0 0 0-2 2v2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="7.5" cy="11" r="1" fill="currentColor"/><circle cx="10.5" cy="8.5" r="1" fill="currentColor"/><circle cx="14" cy="8.5" r="1" fill="currentColor"/></svg>
                            </span>
                            <span>Appearance</span>
                        </a>
                        <a href="{{ route('aurix.rbac.providers') }}" class="la-setup-link">
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
                        <a href="https://github.com/ishah300/Aurix" target="_blank" rel="noopener" class="la-setup-link">
                            <span class="la-setup-link-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M9.5 18.5c-3.8 1.2-3.8-1.7-5.3-2.1m10.6 4.2v-3a2.6 2.6 0 0 0-.8-2c2.7-.3 5.5-1.3 5.5-6a4.7 4.7 0 0 0-1.3-3.3 4.4 4.4 0 0 0-.1-3.3s-1-.3-3.4 1.3a11.5 11.5 0 0 0-6.2 0C6.1 3 5.1 3.3 5.1 3.3a4.4 4.4 0 0 0-.1 3.3 4.7 4.7 0 0 0-1.3 3.3c0 4.7 2.8 5.7 5.5 6a2.6 2.6 0 0 0-.8 2v3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <span>Github Repo</span>
                        </a>
                        <a href="https://github.com/ishah300/Aurix#readme" target="_blank" rel="noopener" class="la-setup-link">
                            <span class="la-setup-link-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M7 5.5h10a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-11a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.8"/><path d="M9 9h6M9 12h6M9 15h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            </span>
                            <span>Documentation</span>
                        </a>
                    </aside>

                    <div class="la-setup-main">
                        <div class="la-setup-head">
                            <h2>Aurix Setup</h2>
                            <p>Configure RBAC in one place: bootstrap admin, define menus, assign permissions, and audit route coverage.</p>
                        </div>

                        <div class="la-setup-cards">
                            <a href="{{ $appearancePageUrl }}" class="la-setup-card">
                                <h4>Customize Appearance</h4>
                                <p>Control branding, colors, alignment, favicon, and custom CSS.</p>
                            </a>
                            <a href="{{ $rolesPageUrl }}" class="la-setup-card">
                                <h4>Create Roles</h4>
                                <p>Define business roles like editor, manager, support, and reviewer.</p>
                            </a>
                            <a href="{{ $menusPageUrl }}" class="la-setup-card">
                                <h4>Configure Menus</h4>
                                <p>Create routes/menus, parent-child structure, and drag ordering.</p>
                            </a>
                            <a href="{{ $rolesPageUrl }}" class="la-setup-card">
                                <h4>Assign Access Rights</h4>
                                <p>Map each role to per-menu actions: view, insert, update, and delete.</p>
                            </a>
                            <a href="{{ $usersPageUrl }}" class="la-setup-card">
                                <h4>Manage Users</h4>
                                <p>Create users and assign one or multiple roles directly from the panel.</p>
                            </a>
                        </div>

                        <div class="la-setup-card la-setup-health">
                            <div class="la-setup-health-head">
                                <h4>Setup Health</h4>
                                <span class="la-health-pill">{{ $healthOkCount }}/{{ $healthTotalCount }} checks passing</span>
                            </div>
                            <div class="la-setup-health-list">
                                @foreach($healthItems as $item)
                                    <div class="la-setup-health-item">
                                        <span class="la-health-dot {{ $item['ok'] ? 'ok' : 'err' }}"></span>
                                        <div>
                                            <div class="la-health-title">{{ $item['title'] }}</div>
                                            <div class="la-health-detail">{{ $item['detail'] }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="la-setup-card">
                            <h4>CLI Quick Start</h4>
                            <p class="la-muted">Run these commands in your host Laravel app terminal.</p>
                            <div class="la-code-block">{{ $setupCommand }}</div>
                            <div class="la-code-block">{{ $auditCommand }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

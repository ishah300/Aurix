@php
    $appearancePreviewRoutes = $previewRoutes ?? [['label' => 'Login', 'url' => url('/login')]];
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
                        <a href="{{ route('aurix.rbac.appearance') }}" class="la-setup-link active">
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
                            <h2>Appearance</h2>
                            <p>Change the appearance of your Aurix auth screens: logo, background, colors, favicon, and custom CSS.</p>
                        </div>

                        <div class="au-top-actions">
                            <a href="{{ $setupPageUrl }}" class="la-btn">Back</a>
                            <button type="button" class="la-btn" id="openPreviewBtn">Open Preview</button>
                        </div>

                        <div class="au-tabs">
                    <button class="au-tab active" data-tab="logo" type="button">Logo</button>
                    <button class="au-tab" data-tab="background" type="button">Background</button>
                    <button class="au-tab" data-tab="favicon" type="button">Favicon</button>
                    <button class="au-tab" data-tab="css" type="button">Custom CSS</button>
                </div>

                <div id="appearanceStatus" class="la-status">Loading appearance settings...</div>

                <section class="au-panel" data-panel="logo">
                    <div class="au-panel-grid">
                        <div>
                            <div class="la-field">
                                <label>Logo Mode</label>
                                <div class="la-btn-row">
                                    <button type="button" id="logoModeSvg" class="la-btn">Use SVG Link</button>
                                    <button type="button" id="logoModeUpload" class="la-btn">Upload Image</button>
                                    <button type="button" id="resetLogoBtn" class="la-btn">Reset Aurix Logo</button>
                                </div>
                            </div>
                            <div class="la-field" id="logoSvgWrap">
                                <label for="logoSvg">SVG Link (URL)</label>
                                <input id="logoSvg" type="url" class="la-input" placeholder="https://example.com/logo.svg">
                            </div>
                            <div class="la-field" id="logoUploadWrap" style="display:none;">
                                <label for="logoImageFile">Upload Logo Image</label>
                                <div class="la-btn-row">
                                    <label class="la-btn" for="logoImageFile">Choose Image</label>
                                </div>
                                <input id="logoImageFile" type="file" accept="image/png,image/jpeg,image/gif,image/webp,image/svg+xml" class="au-file-input">
                                <input id="logoImageUrl" type="hidden">
                            </div>
                            <div class="la-field">
                                <label for="logoHeight">Logo Height (px)</label>
                                <input id="logoHeight" type="number" min="16" max="128" class="la-input">
                            </div>
                        </div>
                        <div>
                            <div class="au-preview-box" id="appearancePreview">
                                                    <div id="appearanceLogoPreview" class="au-preview-logo"></div>
                                                    <h3 id="appearanceTitlePreview">Aurix Preview</h3>
                                                    <p>Sample card, input and button preview.</p>
                                                    <input type="text" id="appearanceInputPreview" value="Input preview" class="la-input">
                                                    <button type="button" id="appearanceButtonPreview" class="la-btn primary">Button Style Preview</button>
                                                </div>
                                            </div>
                                        </div>
                </section>

                <section class="au-panel" data-panel="background" style="display:none;">
                    <div class="au-bg-wrap">
                        <div class="la-field au-bg-field">
                            <label for="backgroundColor">Background Color</label>
                            <input id="backgroundColor" type="color" class="au-color-input">
                        </div>

                        <div class="au-bg-divider"></div>

                        <div class="la-field au-bg-field">
                            <label>Background Image</label>
                            <p class="au-bg-sub">Choose a nice background image to use as your authentication background.</p>
                            <label for="backgroundImageFile" class="au-bg-upload" id="backgroundImageDropzone">
                                <span class="au-bg-upload-icon">
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M7 17a4 4 0 01-.5-7.97A5.5 5.5 0 0117 8.5h.5a3.5 3.5 0 011 6.86" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M12 10.5v8m0-8l-3 3m3-3l3 3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <span class="au-bg-upload-title">Click to upload</span>
                                <span class="au-bg-upload-sub">PNG, JPG or GIF</span>
                            </label>
                            <input id="backgroundImageFile" type="file" accept="image/png,image/jpeg,image/gif,image/webp" class="au-file-input">
                            <input id="backgroundImageUrl" type="hidden">
                        </div>

                        <div class="au-bg-divider"></div>

                        <div class="la-field au-bg-field">
                            <label for="backgroundOverlayColor">Image Overlay Color</label>
                            <p class="au-bg-sub">If you use a background image you can specify a color overlay here.</p>
                            <input id="backgroundOverlayColor" type="color" class="au-color-input">
                        </div>

                        <div class="au-bg-divider"></div>

                        <div class="la-field au-bg-field">
                            <label for="backgroundOverlayOpacity">Image Overlay Opacity</label>
                            <p class="au-bg-sub">The opacity of the image overlay color. Set to 0 for no overlay.</p>
                            <div class="au-bg-opacity-value"><span id="backgroundOverlayOpacityValue">50</span>%</div>
                            <input id="backgroundOverlayOpacity" type="range" min="0" max="100" class="au-bg-range">
                            <div class="au-bg-range-legend">
                                <span>0%</span>
                                <span>50%</span>
                                <span>100%</span>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="au-panel" data-panel="favicon" style="display:none;">
                    <div class="au-favicon-row">
                        <div class="au-favicon-title">Favicon Image</div>
                        <p class="au-favicon-sub">Choose the default favicon image. This image will show by default and in light mode.</p>
                        <div class="au-favicon-thumb-wrap">
                            <div class="au-favicon-thumb" id="faviconLightPreview"></div>
                        </div>
                        <div class="au-favicon-actions">
                            <button type="button" class="au-upload-btn" id="faviconLightUploadBtn">Upload</button>
                            <label class="au-file-btn" for="faviconLightFile">Choose an image</label>
                            <input id="faviconLightFile" type="file" accept="image/png,image/svg+xml,image/x-icon,image/vnd.microsoft.icon,image/webp,image/jpeg" class="au-file-input">
                        </div>
                        <input id="faviconLightUrl" type="hidden">
                    </div>

                    <div class="au-favicon-divider"></div>

                    <div class="au-favicon-row">
                        <div class="au-favicon-title">Favicon Dark Mode Image</div>
                        <p class="au-favicon-sub">This is the favicon image will show when user machine is in dark mode.</p>
                        <div class="au-favicon-thumb-wrap">
                            <div class="au-favicon-thumb dark" id="faviconDarkPreview"></div>
                        </div>
                        <div class="au-favicon-actions">
                            <button type="button" class="au-upload-btn" id="faviconDarkUploadBtn">Upload</button>
                            <label class="au-file-btn" for="faviconDarkFile">Choose an image</label>
                            <input id="faviconDarkFile" type="file" accept="image/png,image/svg+xml,image/x-icon,image/vnd.microsoft.icon,image/webp,image/jpeg" class="au-file-input">
                        </div>
                        <input id="faviconDarkUrl" type="hidden">
                    </div>
                </section>

                <section class="au-panel" data-panel="css" style="display:none;">
                    <div class="la-field"><label for="customCss">Custom CSS</label><textarea id="customCss" class="la-textarea" rows="10" placeholder=".la-card{ border-radius:14px; }"></textarea></div>
                </section>

                <input id="textColor" type="hidden">
                <input id="buttonColor" type="hidden">
                <input id="buttonTextColor" type="hidden">
                <input id="inputTextColor" type="hidden">
                <input id="inputBorderColor" type="hidden">
                <input id="headingAlignment" type="hidden">
                <input id="containerAlignment" type="hidden">

                <div class="la-footer">
                    <button id="appearanceSaveBtn" type="button" class="la-btn primary">Save changes</button>
                </div>
            </main>
        </div>
    </div>

    <div id="auPreviewDrawer" class="au-preview-drawer" aria-hidden="true">
        <div class="au-preview-drawer-inner">
            <div class="au-preview-drawer-head">
                <div class="au-preview-drawer-left">
                    <label for="auPreviewRouteSelect" class="sr-only">Preview Route</label>
                    <select id="auPreviewRouteSelect" class="la-select au-preview-route-select">
                        @foreach($appearancePreviewRoutes as $route)
                            <option value="{{ $route['url'] }}">{{ $route['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="au-preview-drawer-actions">
                    <a id="auPreviewOpenNewTab" href="{{ $appearancePreviewRoutes[0]['url'] ?? '#' }}" target="_blank" rel="noopener" class="la-btn">Preview in New Tab</a>
                    <button id="auPreviewCloseBtn" type="button" class="la-btn">Close</button>
                </div>
            </div>
            <div class="au-preview-drawer-body">
                <iframe id="auPreviewFrame" src="{{ $appearancePreviewRoutes[0]['url'] ?? '' }}" title="Aurix Preview Frame"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const apiBase = @json($apiPrefix);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || @json(csrf_token());
    const status = document.getElementById('appearanceStatus');
    const tabs = Array.from(document.querySelectorAll('.au-tab'));
    const panels = Array.from(document.querySelectorAll('.au-panel'));

    const ids = [
        'logoSvg', 'logoImageUrl', 'logoHeight', 'backgroundColor', 'backgroundImageUrl',
        'backgroundOverlayColor', 'backgroundOverlayOpacity', 'textColor', 'buttonColor',
        'buttonTextColor', 'inputTextColor', 'inputBorderColor', 'headingAlignment',
        'containerAlignment', 'faviconLightUrl', 'faviconDarkUrl', 'customCss'
    ];

    const el = Object.fromEntries(ids.map((id) => [id, document.getElementById(id)]));
    const logoModeSvg = document.getElementById('logoModeSvg');
    const logoModeUpload = document.getElementById('logoModeUpload');
    const logoSvgWrap = document.getElementById('logoSvgWrap');
    const logoUploadWrap = document.getElementById('logoUploadWrap');
    const preview = document.getElementById('appearancePreview');
    const sidebarLogoPreview = document.getElementById('sidebarLogoPreview');
    const previewLogo = document.getElementById('appearanceLogoPreview');
    const previewTitle = document.getElementById('appearanceTitlePreview');
    const previewInput = document.getElementById('appearanceInputPreview');
    const previewBtn = document.getElementById('appearanceButtonPreview');
    const resetLogoBtn = document.getElementById('resetLogoBtn');
    const openPreviewBtn = document.getElementById('openPreviewBtn');
    const previewDrawer = document.getElementById('auPreviewDrawer');
    const previewCloseBtn = document.getElementById('auPreviewCloseBtn');
    const previewRouteSelect = document.getElementById('auPreviewRouteSelect');
    const previewFrame = document.getElementById('auPreviewFrame');
    const previewOpenNewTab = document.getElementById('auPreviewOpenNewTab');
    const logoImageFile = document.getElementById('logoImageFile');
    const faviconLightPreview = document.getElementById('faviconLightPreview');
    const faviconDarkPreview = document.getElementById('faviconDarkPreview');
    const faviconLightFile = document.getElementById('faviconLightFile');
    const faviconDarkFile = document.getElementById('faviconDarkFile');
    const faviconLightUploadBtn = document.getElementById('faviconLightUploadBtn');
    const faviconDarkUploadBtn = document.getElementById('faviconDarkUploadBtn');
    const backgroundImageFile = document.getElementById('backgroundImageFile');
    const backgroundImageDropzone = document.getElementById('backgroundImageDropzone');
    const backgroundOverlayOpacityValue = document.getElementById('backgroundOverlayOpacityValue');
    let logoMode = 'svg';
    const defaultLogoMode = @json((string) config('aurix.appearance.defaults.logo_mode', 'upload'));
    const defaultLogoSvg = @json((string) config('aurix.appearance.defaults.logo_svg'));
    const defaultLogoImageUrl = @json((string) config('aurix.appearance.defaults.logo_image_url', ''));
    const defaultFaviconLightUrl = @json((string) config('aurix.appearance.defaults.favicon_light_url', '/vendor/aurix/favicon-light.png'));
    const defaultFaviconDarkUrl = @json((string) config('aurix.appearance.defaults.favicon_dark_url', '/vendor/aurix/favicon-dark.png'));

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

    const faviconThumb = (url, isDark = false) => {
        if (!url || !url.trim()) {
            return `<span class="au-favicon-empty">${isDark ? 'Dark' : 'Light'}</span>`;
        }

        return `<img src="${url}" alt="favicon preview">`;
    };

    const syncFaviconPreview = () => {
        const light = (el.faviconLightUrl.value || '').trim() || defaultFaviconLightUrl;
        const dark = (el.faviconDarkUrl.value || '').trim() || defaultFaviconDarkUrl;
        faviconLightPreview.innerHTML = faviconThumb(light, false);
        faviconDarkPreview.innerHTML = faviconThumb(dark, true);
    };

    const syncBackgroundPreview = () => {
        const image = (el.backgroundImageUrl.value || '').trim();
        const opacity = Number(el.backgroundOverlayOpacity.value || 50);
        if (backgroundOverlayOpacityValue) {
            backgroundOverlayOpacityValue.textContent = String(opacity);
        }

        if (backgroundImageDropzone) {
            if (image) {
                backgroundImageDropzone.classList.add('has-image');
                backgroundImageDropzone.style.backgroundImage = `linear-gradient(rgba(0,0,0,${opacity / 100}), rgba(0,0,0,${opacity / 100})), url('${image}')`;
            } else {
                backgroundImageDropzone.classList.remove('has-image');
                backgroundImageDropzone.style.backgroundImage = '';
            }
        }
    };

    const setTab = (tab) => {
        tabs.forEach((btn) => btn.classList.toggle('active', btn.dataset.tab === tab));
        panels.forEach((panel) => panel.style.display = panel.dataset.panel === tab ? '' : 'none');
    };

    const setLogoMode = (mode) => {
        logoMode = mode === 'upload' ? 'upload' : 'svg';
        logoModeSvg.classList.toggle('primary', logoMode === 'svg');
        logoModeUpload.classList.toggle('primary', logoMode === 'upload');
        logoSvgWrap.style.display = logoMode === 'svg' ? '' : 'none';
        logoUploadWrap.style.display = logoMode === 'upload' ? '' : 'none';
    };

    const collect = () => ({
        logo_mode: logoMode,
        logo_svg: el.logoSvg.value || '',
        logo_image_url: el.logoImageUrl.value || '',
        logo_height: Number(el.logoHeight.value || 32),
        background_color: el.backgroundColor.value || '#f8fafc',
        background_image_url: el.backgroundImageUrl.value || '',
        background_overlay_color: el.backgroundOverlayColor.value || '#000000',
        background_overlay_opacity: Number(el.backgroundOverlayOpacity.value || 50),
        text_color: el.textColor.value || '#0f172a',
        button_color: el.buttonColor.value || '#111827',
        button_text_color: el.buttonTextColor.value || '#ffffff',
        input_text_color: el.inputTextColor.value || '#0f172a',
        input_border_color: el.inputBorderColor.value || '#d1d5db',
        heading_alignment: el.headingAlignment.value || 'left',
        container_alignment: el.containerAlignment.value || 'center',
        favicon_light_url: el.faviconLightUrl.value || '',
        favicon_dark_url: el.faviconDarkUrl.value || '',
        custom_css: el.customCss.value || '',
    });

    const hydrate = (data) => {
        setLogoMode(data.logo_mode || 'svg');
        el.logoSvg.value = data.logo_svg || '';
        el.logoImageUrl.value = data.logo_image_url || '';
        el.logoHeight.value = data.logo_height || 32;
        el.backgroundColor.value = data.background_color || '#f8fafc';
        el.backgroundImageUrl.value = data.background_image_url || '';
        el.backgroundOverlayColor.value = data.background_overlay_color || '#000000';
        el.backgroundOverlayOpacity.value = data.background_overlay_opacity ?? 50;
        el.textColor.value = data.text_color || '#0f172a';
        el.buttonColor.value = data.button_color || '#111827';
        el.buttonTextColor.value = data.button_text_color || '#ffffff';
        el.inputTextColor.value = data.input_text_color || '#0f172a';
        el.inputBorderColor.value = data.input_border_color || '#d1d5db';
        el.headingAlignment.value = data.heading_alignment || 'left';
        el.containerAlignment.value = data.container_alignment || 'center';
        el.faviconLightUrl.value = data.favicon_light_url || '';
        el.faviconDarkUrl.value = data.favicon_dark_url || '';
        el.customCss.value = data.custom_css || '';
        syncFaviconPreview();
        syncBackgroundPreview();
        applyPreview();
    };

    const applyPreview = () => {
        const v = collect();
        preview.style.background = v.background_color;
        preview.style.color = v.text_color;
        previewTitle.style.textAlign = v.heading_alignment;
        previewInput.style.color = v.input_text_color;
        previewInput.style.borderColor = v.input_border_color;
        previewBtn.style.background = v.button_color;
        previewBtn.style.borderColor = v.button_color;
        previewBtn.style.color = v.button_text_color;
        if (v.logo_mode === 'svg' && v.logo_svg.trim() !== '') {
            const svgValue = v.logo_svg.trim();
            const isLegacySvgMarkup = svgValue.startsWith('<svg');
            previewLogo.innerHTML = isLegacySvgMarkup
                ? `<div style="height:${v.logo_height}px; width:${v.logo_height}px;">${svgValue}</div>`
                : `<img src="${svgValue}" alt="logo" style="height:${v.logo_height}px; width:auto;">`;
            if (sidebarLogoPreview) {
                sidebarLogoPreview.innerHTML = isLegacySvgMarkup
                    ? `<div style="height:${Math.max(24, v.logo_height)}px; width:${Math.max(24, v.logo_height)}px;">${svgValue}</div>`
                    : `<img src="${svgValue}" alt="logo" style="height:${Math.max(24, v.logo_height)}px; width:auto;">`;
            }
        } else if (v.logo_image_url.trim() !== '') {
            previewLogo.innerHTML = `<img src="${v.logo_image_url}" alt="logo" style="height:${v.logo_height}px; width:auto;">`;
            if (sidebarLogoPreview) {
                sidebarLogoPreview.innerHTML = `<img src="${v.logo_image_url}" alt="logo" style="height:${Math.max(24, v.logo_height)}px; width:auto;">`;
            }
        } else {
            previewLogo.innerHTML = '';
            if (sidebarLogoPreview) {
                sidebarLogoPreview.innerHTML = '';
            }
        }
    };

    tabs.forEach((btn) => btn.addEventListener('click', () => setTab(btn.dataset.tab)));
    logoModeSvg.addEventListener('click', () => {
        setLogoMode('svg');
        if ((el.logoSvg.value || '').trim() === '') {
            el.logoSvg.value = defaultLogoSvg || '';
        }
        applyPreview();
    });
    logoModeUpload.addEventListener('click', () => { setLogoMode('upload'); applyPreview(); });
    resetLogoBtn.addEventListener('click', () => {
        setLogoMode(defaultLogoMode);
        el.logoSvg.value = defaultLogoSvg || '';
        el.logoImageUrl.value = defaultLogoImageUrl || '';
        applyPreview();
        setStatus('Default Aurix logo restored in editor. Click Save changes to persist.', 'ok');
    });
    const bindFaviconUpload = (fileInput, uploadBtn, targetInput) => {
        let pendingFile = null;

        fileInput.addEventListener('change', () => {
            pendingFile = fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;
            if (pendingFile) {
                setStatus(`${pendingFile.name} selected. Click Upload to apply.`, 'ok');
            }
        });

        uploadBtn.addEventListener('click', () => {
            if (!pendingFile) {
                setStatus('Select an image first.', 'err');
                return;
            }

            const reader = new FileReader();
            reader.onload = () => {
                targetInput.value = String(reader.result || '');
                syncFaviconPreview();
                setStatus(`${pendingFile.name} loaded. Click Save changes to persist.`, 'ok');
            };
            reader.onerror = () => setStatus('Could not read selected image.', 'err');
            reader.readAsDataURL(pendingFile);
        });
    };

    bindFaviconUpload(faviconLightFile, faviconLightUploadBtn, el.faviconLightUrl);
    bindFaviconUpload(faviconDarkFile, faviconDarkUploadBtn, el.faviconDarkUrl);
    if (logoImageFile) {
        logoImageFile.addEventListener('change', () => {
            const file = logoImageFile.files && logoImageFile.files[0] ? logoImageFile.files[0] : null;
            if (!file) {
                return;
            }

            const reader = new FileReader();
            reader.onload = () => {
                el.logoImageUrl.value = String(reader.result || '');
                setLogoMode('upload');
                applyPreview();
                setStatus(`${file.name} loaded as logo image. Click Save changes to persist.`, 'ok');
            };
            reader.onerror = () => setStatus('Could not read selected logo image.', 'err');
            reader.readAsDataURL(file);
        });
    }
    backgroundImageFile.addEventListener('change', () => {
        const file = backgroundImageFile.files && backgroundImageFile.files[0] ? backgroundImageFile.files[0] : null;
        if (!file) {
            return;
        }

        const reader = new FileReader();
        reader.onload = () => {
            el.backgroundImageUrl.value = String(reader.result || '');
            syncBackgroundPreview();
            setStatus(`${file.name} loaded as background image. Click Save changes to persist.`, 'ok');
        };
        reader.onerror = () => setStatus('Could not read selected background image.', 'err');
        reader.readAsDataURL(file);
    });
    el.backgroundOverlayOpacity.addEventListener('input', syncBackgroundPreview);
    openPreviewBtn.addEventListener('click', () => {
        previewDrawer.classList.add('open');
        previewDrawer.setAttribute('aria-hidden', 'false');
        previewFrame.src = previewRouteSelect.value;
        previewOpenNewTab.href = previewRouteSelect.value;
    });
    previewCloseBtn.addEventListener('click', () => {
        previewDrawer.classList.remove('open');
        previewDrawer.setAttribute('aria-hidden', 'true');
    });
    previewDrawer.addEventListener('click', (e) => {
        if (e.target === previewDrawer) {
            previewDrawer.classList.remove('open');
            previewDrawer.setAttribute('aria-hidden', 'true');
        }
    });
    previewRouteSelect.addEventListener('change', () => {
        previewFrame.src = previewRouteSelect.value;
        previewOpenNewTab.href = previewRouteSelect.value;
    });
    ids.forEach((id) => el[id].addEventListener('input', applyPreview));

    document.getElementById('appearanceSaveBtn').addEventListener('click', async () => {
        try {
            setStatus('Saving appearance settings...');
            await req(`${apiBase}/settings/appearance`, { method: 'PUT', body: JSON.stringify(collect()) });
            setStatus('Appearance settings saved. Refresh to apply globally.', 'ok');
        } catch (e) {
            setStatus(e.message, 'err');
        }
    });

    (async () => {
        setTab('logo');
        setStatus('Loading appearance settings...');
        const json = await req(`${apiBase}/settings/appearance`);
        hydrate(json.data || {});
        setStatus('Appearance settings loaded.', 'ok');
    })().catch((e) => setStatus(e.message, 'err'));
})();
</script>

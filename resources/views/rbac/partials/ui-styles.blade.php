@php
    $aurixAppearanceSafe = array_merge((array) config('aurix.appearance.defaults', []), (array) ($aurixAppearance ?? []));
@endphp
<style>
    .la-wrap {
        --la-bg: #f8fafc;
        --la-surface: #ffffff;
        --la-border: {{ $aurixAppearanceSafe['input_border_color'] ?? '#e2e8f0' }};
        --la-border-soft: #eef2f7;
        --la-text: {{ $aurixAppearanceSafe['text_color'] ?? '#0f172a' }};
        --la-sub: #64748b;
        --la-primary: {{ $aurixAppearanceSafe['button_color'] ?? '#111827' }};
        --la-primary-hover: #0b1220;
        --la-danger: #ef4444;
        --la-danger-hover: #dc2626;
        --la-dark: #0f172a;
        --la-success-bg: #ecfdf3;
        --la-success-border: #86efac;
        --la-success-text: #166534;
        --la-error-bg: #fef2f2;
        --la-error-border: #fca5a5;
        --la-error-text: #991b1b;
        font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
        color: var(--la-text);
        background: #f8fafc !important;
        min-height: calc(100vh - 64px);
    }

    .la-shell { max-width: 1240px; margin: 24px auto; padding: 0 16px; }
    .la-wrap-setup .la-shell { max-width: none; width: 100%; margin: 0; padding: 0; }
    .la-card { background: var(--la-surface); border: 1px solid var(--la-border); border-radius: 10px; overflow: hidden; box-shadow: 0 1px 2px rgba(15, 23, 42, .04); }
    .la-body { padding: 18px; }
    .la-brand {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        border: 1px solid var(--la-border);
        border-radius: 999px;
        background: #fff;
        margin-bottom: 12px;
    }
    .la-brand-logo { color: var(--la-text); display: inline-flex; align-items: center; justify-content: center; }
    .la-brand-logo svg { height: 100%; width: 100%; display: block; }
    .la-brand-text { font-size: 13px; font-weight: 500; letter-spacing: .04em; text-transform: uppercase; color: #475569; }

    .la-head {
        background: #f8fafc;
        color: var(--la-text);
        padding: 14px 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .la-head h1 { margin: 0; font-size: 30px; line-height: 1.15; font-weight: 500; letter-spacing: .1px; text-align: {{ $aurixAppearanceSafe['heading_alignment'] ?? 'left' }}; }
    .la-head h2 { margin: 0; font-size: 26px; line-height: 1.15; font-weight: 500; letter-spacing: .1px; text-align: {{ $aurixAppearanceSafe['heading_alignment'] ?? 'left' }}; }
    .la-sub { margin-top: 4px; color: var(--la-sub); font-size: 13px; }

    .la-nav { display: flex; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; }
    .la-link {
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 8px 12px;
        text-decoration: none;
        color: #334155;
        font-weight: 500;
        font-size: 14px;
        background: #fff;
    }
    .la-link.active { background: #f1f5f9; color: #111827; border-color: #d1d5db; }

    .la-grid { display: grid; gap: 10px; align-items: end; }
    .la-grid.users { grid-template-columns: 2fr 2fr 2fr 2fr auto; }
    .la-grid.menus { grid-template-columns: 2fr 2fr 2fr 1fr 1fr auto; }
    .la-grid.posts { grid-template-columns: 1fr auto; align-items: center; }
    @media (max-width: 1200px) { .la-grid.users, .la-grid.menus { grid-template-columns: 1fr; } }
    @media (max-width: 860px) { .la-grid.posts { grid-template-columns: 1fr; } }

    .la-field label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 500; }
    .la-input,
    .la-select,
    .la-textarea {
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 0 12px;
        font-size: 14px;
        color: {{ $aurixAppearanceSafe['input_text_color'] ?? '#0f172a' }};
        background: #fff;
        border-color: {{ $aurixAppearanceSafe['input_border_color'] ?? '#d1d5db' }};
    }
    .la-input,
    .la-select { height: 40px; }
    .la-textarea { min-height: 92px; padding: 10px 12px; resize: vertical; }
    .la-role-grid {
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 8px;
        max-height: 120px;
        overflow: auto;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 6px;
    }
    .la-role-item { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #334155; }

    .la-tools { margin-top: 12px; display: flex; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
    .la-tools.compact { margin-top: 0; margin-bottom: 12px; }
    .la-tools .la-input.search { width: 220px; }
    .la-tools .la-select.page-size { width: 90px; }

    .la-btn {
        border: 1px solid #d1d5db;
        background: #fff;
        color: #334155;
        border-radius: 8px;
        padding: 9px 14px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1.2;
    }
    .la-btn.primary { background: var(--la-primary); border-color: var(--la-primary); color: {{ $aurixAppearanceSafe['button_text_color'] ?? '#ffffff' }}; }
    .la-btn.primary:hover { background: var(--la-primary-hover); }
    .la-btn.dark { background: var(--la-dark); border-color: var(--la-dark); color: #fff; }
    .la-btn.danger { background: var(--la-danger); border-color: var(--la-danger); color: #fff; }
    .la-btn.danger:hover { background: var(--la-danger-hover); border-color: var(--la-danger-hover); }
    .la-btn-row { display: flex; gap: 8px; flex-wrap: wrap; }
    .la-head-search { display: flex; gap: 8px; align-items: center; flex-wrap: nowrap; }
    .la-head-search .la-input { width: 250px; }
    .la-head-search .la-btn { white-space: nowrap; }
    @media (max-width: 860px) {
        .la-head-search { width: 100%; flex-wrap: wrap; }
        .la-head-search .la-input { width: 100%; }
    }
    .la-row-actions { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
    .la-btn.disabled,
    .la-btn:disabled { opacity: .5; cursor: not-allowed; }

    .la-table-wrap { margin-top: 12px; border: 1px solid var(--la-border); border-radius: 8px; overflow: auto; }
    .la-table-wrap.tall { max-height: 58vh; }
    .la-table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .la-table th, .la-table td { padding: 12px 14px; border-bottom: 1px solid var(--la-border-soft); text-align: left; vertical-align: middle; }
    .la-table th { background: #f9fafb; font-weight: 500; color: #374151; }
    .la-table.grid th, .la-table.grid td { border: 1px solid var(--la-border); padding: 10px 12px; }
    .la-table .center { text-align: center; width: 110px; }
    .la-table tr.group td:first-child { background: #141b3b; color: #fff; font-weight: 500; }
    .la-table .child { padding-left: 28px; color: #334155; }
    .la-badge { display: inline-block; padding: 3px 8px; border-radius: 999px; background: #f1f5f9; color: #334155; font-size: 12px; margin-right: 4px; }

    .la-foot { margin-top: 8px; text-align: right; color: #94a3b8; font-size: 13px; }
    .la-status {
        margin-top: 12px;
        padding: 10px 12px;
        border-radius: 8px;
        font-size: 13px;
        background: #f8fafc;
        color: #475569;
        border: 1px solid transparent;
    }
    .la-status.ok { background: var(--la-success-bg); border-color: var(--la-success-border); color: var(--la-success-text); }
    .la-status.err { background: var(--la-error-bg); border-color: var(--la-error-border); color: var(--la-error-text); }
    .la-footer { margin-top: 12px; display: flex; justify-content: flex-end; gap: 8px; flex-wrap: wrap; }

    .la-divider { border: 0; border-top: 1px solid #edf2f7; margin: 22px 0; }
    .la-details { margin-bottom: 14px; border: 1px solid var(--la-border); border-radius: 10px; background: #fff; overflow: hidden; }
    .la-details summary { cursor: pointer; padding: 12px 14px; font-weight: 500; }
    .la-details .la-details-body { padding: 12px 14px; border-top: 1px solid var(--la-border); display: grid; gap: 10px; }
    .la-form-stack { display: grid; gap: 10px; }
    .la-popover { margin-top: 6px; padding: 10px; border: 1px solid var(--la-border); border-radius: 8px; background: #fff; min-width: 320px; }
    .la-muted { color: var(--la-sub); }
    .la-empty-cell { padding: 18px; color: #94a3b8; }
    .la-pagination-wrap { margin-top: 12px; }
    .la-tree-wrap { margin-top: 12px; border: 1px dashed var(--la-border); border-radius: 10px; padding: 12px; background: #fff; }
    .la-tree-drop-root { border: 1px dashed #cbd5e1; border-radius: 8px; padding: 10px 12px; font-size: 13px; color: #475569; margin-bottom: 10px; background: #f8fafc; }
    .la-tree-drop-root.active { border-color: #94a3b8; background: #f1f5f9; }
    .la-tree { list-style: none; margin: 0; padding: 0; }
    .la-tree-item { margin: 6px 0; }
    .la-tree-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        border: 1px solid var(--la-border);
        border-radius: 8px;
        padding: 8px 10px;
        background: #fff;
    }
    .la-tree-row.active { border-color: #94a3b8; background: #f8fafc; }
    .la-tree-left { display: flex; align-items: center; gap: 8px; min-width: 0; }
    .la-drag-handle { font-size: 14px; color: #64748b; cursor: move; user-select: none; }
    .la-tree-title { font-weight: 500; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .la-tree-meta { font-size: 12px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .la-tree-child-drop { margin-top: 6px; margin-left: 28px; border: 1px dashed #d1d5db; border-radius: 8px; padding: 6px 8px; font-size: 12px; color: #64748b; background: #f8fafc; }
    .la-tree-child-drop.active { border-color: #94a3b8; background: #f1f5f9; color: #334155; }
    .la-tree-children { list-style: none; margin: 8px 0 0 20px; padding: 0; border-left: 1px dashed #e2e8f0; padding-left: 12px; }
    .la-mb-0 { margin-bottom: 0; }
    .la-mb-10 { margin-bottom: 10px; }

    .la-toggle {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 22px;
        cursor: pointer;
    }
    .la-toggle-input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }
    .la-toggle-slider {
        position: relative;
        display: block;
        width: 42px;
        height: 22px;
        background: #e5e7eb;
        border-radius: 999px;
        transition: background-color .18s ease;
        box-shadow: inset 0 0 0 1px rgba(148, 163, 184, .6);
    }
    .la-toggle-slider::before {
        content: "";
        position: absolute;
        left: 2px;
        top: 2px;
        width: 18px;
        height: 18px;
        border-radius: 999px;
        background: #ffffff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .25);
        transition: transform .18s ease;
    }
    .la-toggle-input:checked + .la-toggle-slider {
        background: var(--la-primary);
        box-shadow: inset 0 0 0 1px rgba(15, 23, 42, .1);
    }
    .la-toggle-input:checked + .la-toggle-slider::before {
        transform: translateX(20px);
    }
    .la-toggle-input:focus-visible + .la-toggle-slider {
        outline: 2px solid #4f46e5;
        outline-offset: 2px;
    }

    .la-body-setup { padding: 0; }
    .la-setup-grid {
        display: grid;
        grid-template-columns: 220px 1fr;
        gap: 0;
        align-items: stretch;
        min-height: 760px;
    }
    .la-setup-sidebar {
        background: #f8fafc;
        border-right: 1px solid var(--la-border);
        padding: 16px 10px;
    }
    .la-setup-sidebar h3 {
        margin: 0 0 18px;
        color: #111827;
    }
    .la-setup-sidebar h3 > span {
        font-size: 1rem;
        font-weight: 700;
        line-height: 1;
    }
    .la-setup-sidebar h3 > span > span {
        font-weight: 300;
    }
    .la-setup-side-group {
        margin: 12px 0 6px;
        font-size: 12px;
        color: #94a3b8;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .06em;
    }
    .la-setup-side-group.resources { margin-top: 16px; }
    .la-setup-link {
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        color: #6b7280;
        border: 1px solid transparent;
        border-radius: 7px;
        padding: 7px 10px;
        font-weight: 500;
        font-size: 14px;
        margin-bottom: 4px;
    }
    .la-setup-link-icon {
        width: 14px;
        height: 14px;
        color: #9ca3af;
        flex: 0 0 14px;
    }
    .la-setup-link-icon svg {
        width: 14px;
        height: 14px;
        display: block;
    }
    .la-setup-link:hover { background: #f3f4f6; border-color: #e5e7eb; color: #374151; }
    .la-setup-link.active { background: #e5e7eb; border-color: #d1d5db; color: #111827; }
    .la-setup-link.active .la-setup-link-icon { color: #6b7280; }
    .la-setup-link.disabled {
        opacity: .74;
        cursor: default;
        pointer-events: none;
    }

    .la-setup-main { display: grid; gap: 14px; padding: 18px 22px; background: #fff; }
    .la-setup-head h2 {
        margin: 0;
        font-size: 40px;
        line-height: 1;
        font-weight: 600;
    }
    .la-setup-head p { margin: 8px 0 0; color: var(--la-sub); font-size: 16px; }
    .la-setup-cards { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .la-setup-card {
        border: 1px solid var(--la-border);
        border-radius: 12px;
        padding: 14px;
        background: #fff;
        text-decoration: none;
        color: inherit;
    }
    .la-setup-card h4 {
        margin: 0 0 8px;
        font-size: 20px;
        line-height: 1.1;
        font-weight: 500;
    }
    .la-setup-card p { margin: 0; color: #475569; font-size: 14px; }
    .la-setup-card:hover { border-color: #cbd5e1; box-shadow: 0 1px 4px rgba(15, 23, 42, .06); }

    .la-setup-health-head { display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 10px; }
    .la-health-pill {
        display: inline-block;
        border: 1px solid #d1d5db;
        border-radius: 999px;
        background: #f8fafc;
        font-size: 12px;
        font-weight: 500;
        color: #334155;
        padding: 4px 10px;
    }
    .la-setup-health-list { display: grid; gap: 10px; }
    .la-setup-health-item { display: grid; grid-template-columns: 12px 1fr; gap: 10px; align-items: start; }
    .la-health-dot {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        margin-top: 4px;
        background: #cbd5e1;
    }
    .la-health-dot.ok { background: #16a34a; }
    .la-health-dot.err { background: #dc2626; }
    .la-health-title { font-weight: 500; color: #111827; font-size: 14px; }
    .la-health-detail { color: #64748b; font-size: 13px; margin-top: 2px; }
    .la-code-block {
        margin-top: 8px;
        background: #0f172a;
        color: #e2e8f0;
        border-radius: 8px;
        padding: 9px 11px;
        font-size: 12px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, Liberation Mono, monospace;
        overflow-x: auto;
        white-space: nowrap;
    }

    @media (max-width: 1080px) {
        .la-setup-grid { grid-template-columns: 1fr; }
        .la-setup-sidebar { border-right: 0; border-bottom: 1px solid var(--la-border); }
        .la-setup-cards { grid-template-columns: 1fr; }
    }
    .au-app-frame {
        display: grid;
        grid-template-columns: 230px 1fr;
        gap: 24px;
        background: #fff;
        border: 1px solid var(--la-border);
        border-radius: 12px;
        overflow: hidden;
    }
    .au-sidebar {
        padding: 20px 14px;
        border-right: 1px solid var(--la-border);
        background: #fbfcfe;
    }
    .au-side-title { font-size: 24px; font-weight: 500; line-height: 1.1; margin-bottom: 14px; color: #0f172a; }
    .au-side-logo { margin-bottom: 12px; color: #0f172a; min-height: 32px; display: flex; align-items: center; }
    .au-side-logo svg { display: block; width: 100%; height: 100%; }
    .au-side-group { margin: 12px 0 8px; font-size: 12px; color: #94a3b8; font-weight: 500; text-transform: uppercase; letter-spacing: .06em; }
    .au-side-link {
        display: block;
        text-decoration: none;
        color: #475569;
        border: 1px solid transparent;
        border-radius: 8px;
        padding: 9px 10px;
        font-weight: 500;
        margin-bottom: 4px;
    }
    .au-side-link.active { background: #f1f5f9; color: #111827; border-color: #dbe4ef; }
    .au-side-link:hover { background: #f8fafc; border-color: #e2e8f0; }
    .au-main { padding: 22px 22px 18px; }
    .au-top-actions { display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 6px; }
    .au-head h2 { margin: 0; font-size: 36px; line-height: 1.05; font-weight: 500; color: #0f172a; }
    .au-head p { margin: 10px 0 0; color: #64748b; font-size: 16px; }
    .au-tabs {
        margin-top: 16px;
        border-bottom: 1px solid var(--la-border);
        display: flex;
        gap: 4px;
        flex-wrap: wrap;
    }
    .au-tab {
        border: 0;
        background: transparent;
        color: #64748b;
        font-weight: 500;
        font-size: 14px;
        padding: 10px 12px;
        border-bottom: 2px solid transparent;
        cursor: pointer;
    }
    .au-tab.active { color: #4f46e5; border-bottom-color: #4f46e5; }
    .au-panel { padding-top: 14px; }
    .au-panel-grid { display: grid; grid-template-columns: 1.1fr .9fr; gap: 14px; }
    .au-bg-wrap { max-width: 740px; }
    .au-bg-field { margin-bottom: 0; }
    .au-bg-field > label { font-size: 16px; font-weight: 500; color: #111827; }
    .au-bg-sub { margin: 4px 0 10px; color: #94a3b8; font-size: 14px; line-height: 1.5; }
    .au-bg-divider { border-top: 1px solid #e5e7eb; margin: 16px 0; }
    .au-color-input {
        width: 38px;
        height: 22px;
        padding: 0;
        border: 1px solid #9ca3af;
        border-radius: 2px;
        background: transparent;
        cursor: pointer;
    }
    .au-bg-upload {
        position: relative;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        background: #f8fafc;
        min-height: 260px;
        padding: 24px 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: #334155;
        text-align: center;
        text-decoration: none;
        cursor: pointer;
        background-size: cover;
        background-position: center;
        overflow: hidden;
        transition:
            border-color .18s ease,
            box-shadow .18s ease,
            background-color .18s ease,
            transform .18s ease;
    }
    .au-bg-upload::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(to bottom, rgba(15, 23, 42, .14), rgba(15, 23, 42, .62));
        opacity: 0;
        transition: opacity .18s ease;
        pointer-events: none;
    }
    .au-bg-upload > * {
        position: relative;
        z-index: 1;
    }
    .au-bg-upload:hover {
        border-color: #94a3b8;
        background-color: #f1f5f9;
        box-shadow: 0 12px 35px rgba(15, 23, 42, .10);
        transform: translateY(-1px);
    }
    .au-bg-upload.has-image {
        color: #ffffff;
        text-shadow: 0 1px 2px rgba(0, 0, 0, .35);
    }
    .au-bg-upload.has-image::before {
        opacity: 1;
    }
    .au-bg-upload-icon svg { width: 32px; height: 32px; display: block; }
    .au-bg-upload-title { font-size: 15px; font-weight: 600; }
    .au-bg-upload-sub { font-size: 12px; color: #94a3b8; max-width: 260px; }
    .au-bg-upload.has-image .au-bg-upload-sub { color: #e2e8f0; }
    .au-bg-opacity-value { margin: 0 0 8px; font-size: 30px; line-height: 1; font-weight: 500; color: #111827; }
    .au-bg-range {
        width: 100%;
        accent-color: #3b82f6;
    }
    .au-bg-range-legend {
        display: flex;
        justify-content: space-between;
        color: #64748b;
        font-size: 12px;
        margin-top: 4px;
    }
    .au-favicon-row { max-width: 740px; padding: 2px 0 10px; }
    .au-favicon-title { font-size: 16px; font-weight: 500; color: #111827; margin-bottom: 6px; }
    .au-favicon-sub { margin: 0 0 10px; color: #94a3b8; font-size: 13px; line-height: 1.5; }
    .au-favicon-thumb-wrap { margin: 0 0 10px; }
    .au-favicon-thumb {
        width: 52px;
        height: 52px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .au-favicon-thumb.dark { background: #111827; border-color: #111827; }
    .au-favicon-thumb img {
        width: 38px;
        height: 38px;
        object-fit: contain;
        display: block;
    }
    .au-favicon-empty { font-size: 11px; color: #9ca3af; font-weight: 500; }
    .au-favicon-actions { display: flex; gap: 0; align-items: center; margin-bottom: 2px; }
    .au-upload-btn {
        border: 1px solid #d1d5db;
        border-right: 0;
        background: #111827;
        color: #fff;
        border-radius: 8px 0 0 8px;
        height: 36px;
        padding: 0 16px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
    }
    .au-file-btn {
        border: 1px solid #d1d5db;
        border-radius: 0 8px 8px 0;
        background: #f8fafc;
        color: #6b7280;
        height: 36px;
        padding: 0 16px;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
    }
    .au-file-input { display: none; }
    .au-favicon-divider { border-top: 1px solid #e5e7eb; margin: 16px 0; max-width: 740px; }
    .au-preview-box {
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        padding: 14px;
        background: #f8fafc;
    }
    .au-preview-logo { margin-bottom: 8px; min-height: 30px; display: flex; align-items: center; }
    .au-preview-box h3 { margin: 0 0 8px; font-size: 24px; line-height: 1.1; font-weight: 500; }
    .au-preview-box p { margin: 0 0 10px; color: #64748b; }
    .au-preview-box .la-input { margin-bottom: 10px; }
    .au-preview-drawer {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .42);
        display: flex;
        align-items: flex-end;
        justify-content: center;
        z-index: 80;
        opacity: 0;
        pointer-events: none;
        transition: opacity .3s ease-out;
    }
    .au-preview-drawer.open {
        opacity: 1;
        pointer-events: auto;
    }
    .au-preview-drawer-inner {
        width: min(1400px, 96vw);
        height: min(88vh, 820px);
        background: #fff;
        border: 1px solid #dbe4ef;
        border-radius: 10px 10px 0 0;
        overflow: hidden;
        transform: translateY(100%);
        transition: transform .3s ease-out;
        box-shadow: 0 -14px 40px rgba(2, 6, 23, .24);
    }
    .au-preview-drawer.open .au-preview-drawer-inner { transform: translateY(0); }
    .au-preview-drawer-head {
        height: 44px;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 0 10px;
    }
    .au-preview-drawer-left { display: flex; align-items: center; gap: 8px; }
    .au-preview-route-select { width: 220px; height: 32px; }
    .au-preview-drawer-actions { display: flex; align-items: center; gap: 8px; }
    .au-preview-drawer-actions .la-btn { height: 32px; padding: 6px 10px; font-size: 12px; }
    .au-preview-drawer-body { height: calc(100% - 44px); background: #fff; }
    .au-preview-drawer-body iframe { width: 100%; height: 100%; border: 0; background: #fff; }
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }
    @media (max-width: 1100px) {
        .au-app-frame { grid-template-columns: 1fr; }
        .au-sidebar { border-right: 0; border-bottom: 1px solid var(--la-border); }
        .au-panel-grid { grid-template-columns: 1fr; }
    }
</style>

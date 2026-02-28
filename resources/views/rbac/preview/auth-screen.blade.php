@php
    $a = array_merge((array) config('aurix.appearance.defaults', []), (array) ($aurixAppearance ?? []));
    $logoMode = (string) ($a['logo_mode'] ?? 'svg');
    $logoSvg = (string) ($a['logo_svg'] ?? '');
    $logoImageUrl = (string) ($a['logo_image_url'] ?? '');
    $logoHeight = (int) ($a['logo_height'] ?? 32);
    $socialProviders = collect($aurixEnabledSocialProviders ?? [])->values();
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Aurix Preview</title>
    @include('aurix::rbac.partials.favicon-head')
    <style>
        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            background: {{ $a['background_color'] ?? '#f8fafc' }};
            color: {{ $a['text_color'] ?? '#0f172a' }};
            min-height: 100vh;
            display: grid;
            place-items: center;
        }
        .pv-card {
            width: min(420px, 92vw);
            border: 1px solid {{ $a['input_border_color'] ?? '#d1d5db' }};
            border-radius: 12px;
            padding: 18px;
            background: #fff;
            box-shadow: 0 8px 30px rgba(15, 23, 42, .08);
        }
        .pv-logo { display: flex; justify-content: center; margin-bottom: 8px; color: {{ $a['text_color'] ?? '#0f172a' }}; }
        .pv-logo svg { width: {{ $logoHeight }}px; height: {{ $logoHeight }}px; }
        .pv-title { text-align: {{ $a['heading_alignment'] ?? 'left' }}; font-size: 28px; line-height: 1.05; margin: 6px 0 10px; font-weight: 800; }
        .pv-label { font-size: 12px; color: #64748b; margin-bottom: 4px; display: block; }
        .pv-input {
            width: 100%;
            box-sizing: border-box;
            height: 42px;
            border: 1px solid {{ $a['input_border_color'] ?? '#d1d5db' }};
            border-radius: 8px;
            padding: 0 12px;
            color: {{ $a['input_text_color'] ?? '#0f172a' }};
            margin-bottom: 10px;
        }
        .pv-btn {
            width: 100%;
            height: 42px;
            border-radius: 8px;
            border: 1px solid {{ $a['button_color'] ?? '#111827' }};
            background: {{ $a['button_color'] ?? '#111827' }};
            color: {{ $a['button_text_color'] ?? '#ffffff' }};
            font-weight: 700;
        }
        .pv-sub { text-align: center; margin-top: 10px; font-size: 13px; color: #64748b; }
        .pv-divider {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 14px 0 10px;
            font-size: 11px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .05em;
        }
        .pv-divider::before,
        .pv-divider::after {
            content: "";
            flex: 1;
            border-top: 1px solid #e5e7eb;
        }
        .pv-social-list { display: grid; gap: 8px; }
        .pv-social-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background: #fff;
            color: #334155;
            font-size: 13px;
            padding: 9px 10px;
            box-sizing: border-box;
        }
        .pv-social-item span.icon {
            width: 18px;
            height: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="pv-card">
        <div class="pv-logo">
            @if($logoMode === 'upload' && $logoImageUrl !== '')
                <img src="{{ $logoImageUrl }}" alt="Aurix" style="height: {{ $logoHeight }}px; width:auto;">
            @else
                {!! $logoSvg !!}
            @endif
        </div>

        @if($screen === 'register')
            <h1 class="pv-title">Create account</h1>
            <label class="pv-label">Name</label>
            <input class="pv-input" type="text" value="">
            <label class="pv-label">Email</label>
            <input class="pv-input" type="email" value="">
            <label class="pv-label">Password</label>
            <input class="pv-input" type="password" value="">
            <button class="pv-btn" type="button">Create account</button>
        @elseif($screen === 'forgot')
            <h1 class="pv-title">Reset password</h1>
            <label class="pv-label">Email</label>
            <input class="pv-input" type="email" value="">
            <button class="pv-btn" type="button">Send reset link</button>
        @else
            <h1 class="pv-title">Sign in</h1>
            <label class="pv-label">Email address</label>
            <input class="pv-input" type="email" value="">
            <label class="pv-label">Password</label>
            <input class="pv-input" type="password" value="">
            <button class="pv-btn" type="button">Continue</button>
        @endif

        @if($socialProviders->isNotEmpty())
            <div class="pv-divider">Or</div>
            <div class="pv-social-list">
                @foreach($socialProviders as $provider)
                    @php
                        $slug = (string) ($provider['slug'] ?? '');
                        $name = (string) ($provider['name'] ?? ucfirst($slug));
                    @endphp
                    <div class="pv-social-item">
                        <span class="icon">@include('aurix::rbac.partials._provider_icons', ['slug' => $slug])</span>
                        <span>Continue with {{ $name }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="pv-sub">Preview only. Saves are controlled from Appearance settings.</div>
    </div>
</body>
</html>

<?php

declare(strict_types=1);

return [
    'ui' => [
        'enabled' => env('AURIX_UI_ENABLED', true),
        'path' => env('AURIX_UI_PATH', 'auth/rbac'),
        'title' => env('AURIX_UI_TITLE', 'Aurix RBAC Manager'),
        'middleware' => ['web', 'auth', 'can:aurix.manage-rbac'],
    ],

    'api' => [
        'prefix' => env('AURIX_API_PREFIX', 'api/auth'),
        'middleware' => ['web', 'auth'],
    ],

    'posts' => [
        'enabled' => env('AURIX_POSTS_ENABLED', true),
        'path' => env('AURIX_POSTS_PATH', 'posts'),
        'middleware' => ['web', 'auth', 'permission:posts.view'],
    ],

    'menus' => [
        // When true, web routes matching an active menu route are auto-protected by menu permissions.
        'auto_enforce_web_routes' => env('AURIX_MENUS_AUTO_ENFORCE_WEB_ROUTES', true),
    ],

    'database' => [
        'connection' => env('AURIX_DB_CONNECTION'),
    ],

    'tables' => [
        'users' => 'users',
        'roles' => 'roles',
        'permissions' => 'permissions',
        'user_roles' => 'user_roles',
        'role_permissions' => 'role_permissions',
        'role_menu_permissions' => 'role_menu_permissions',
        'menus' => 'menus',
        'posts' => 'auth_posts',
        'settings' => 'aurix_settings',
        'social_providers' => env('AURIX_SOCIAL_PROVIDERS_TABLE', 'aurix_social_providers'),
    ],

    'rbac' => [
        'super_admin_roles' => ['admin', 'super-admin'],
        'manage_roles' => ['admin', 'super-admin'],
        'cache_ttl_seconds' => env('AURIX_RBAC_CACHE_TTL', 300),
    ],

    'pagination' => [
        'default_per_page' => 15,
        'max_per_page' => 100,
    ],

    'social' => [
        // Enable/disable social authentication routes
        'enabled' => env('AURIX_SOCIAL_ENABLED', true),
        // Redirect path after successful social login
        'redirect_after_login' => env('AURIX_SOCIAL_REDIRECT', '/dashboard'),
        // Providers currently available for full configuration/use.
        'available_providers' => ['google', 'github'],
        // Backward-compatible alias for social providers table config.
        'providers_table' => env('AURIX_SOCIAL_PROVIDERS_TABLE', 'aurix_social_providers'),
    ],

    'audit' => [
        // Auth/account routes that are usually framework-owned and not managed via menu permissions.
        'ignore_route_names' => [
            'dashboard',
            'profile.*',
            'verification.*',
            'password.*',
            'logout',
        ],
        'ignore_uri_patterns' => [
            '/dashboard',
            '/profile',
            '/verify-email',
            '/verify-email/*',
            '/email/verification-notification',
            '/confirm-password',
            '/password',
            '/logout',
        ],
    ],

    'appearance' => [
        'defaults' => [
            'logo_mode' => 'upload',
            'logo_svg' => '/vendor/aurix/aurix-logo.svg',
            'logo_image_url' => '/vendor/aurix/aurix-logo.png',
            'logo_height' => 56,
            'background_color' => '#f8fafc',
            'background_image_url' => '',
            'background_overlay_color' => '#000000',
            'background_overlay_opacity' => 50,
            'text_color' => '#0f172a',
            'button_color' => '#111827',
            'button_text_color' => '#ffffff',
            'input_text_color' => '#0f172a',
            'input_border_color' => '#d1d5db',
            'heading_alignment' => 'left',
            'container_alignment' => 'center',
            'favicon_light_url' => '/vendor/aurix/favicon-light.png',
            'favicon_dark_url' => '/vendor/aurix/favicon-dark.png',
            'custom_css' => '',
        ],
    ],
];

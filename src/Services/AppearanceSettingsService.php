<?php

declare(strict_types=1);

namespace Aurix\Services;

use Illuminate\Support\Facades\Schema;
use Aurix\Models\Setting;
use Aurix\Support\AppearanceInputSanitizer;

class AppearanceSettingsService
{
    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $defaults = $this->defaults();
        if (! $this->settingsTableExists()) {
            return $defaults;
        }

        $rows = Setting::query()
            ->whereIn('key', array_keys($defaults))
            ->get(['key', 'value']);

        $stored = [];
        foreach ($rows as $row) {
            $stored[(string) $row->key] = $this->castValue((string) $row->key, (string) ($row->value ?? ''));
        }

        return array_merge($defaults, $stored);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function update(array $payload): array
    {
        $current = $this->all();
        $next = array_merge($current, $this->sanitize($payload));

        if ($this->settingsTableExists()) {
            foreach ($next as $key => $value) {
                Setting::query()->updateOrCreate(
                    ['key' => $key],
                    ['value' => (string) $value]
                );
            }
        }

        return $next;
    }

    /**
     * @return array<string, mixed>
     */
    private function sanitize(array $payload): array
    {
        $payload = AppearanceInputSanitizer::clean($payload);

        $defaults = $this->defaults();
        $out = [];

        foreach ($defaults as $key => $default) {
            if (! array_key_exists($key, $payload)) {
                continue;
            }

            $value = $payload[$key];
            switch ($key) {
                case 'logo_height':
                    $out[$key] = max(16, min(128, (int) $value));
                    break;
                case 'background_overlay_opacity':
                    $out[$key] = max(0, min(100, (int) $value));
                    break;
                case 'logo_mode':
                    $out[$key] = in_array((string) $value, ['upload', 'svg'], true) ? (string) $value : 'svg';
                    break;
                case 'heading_alignment':
                case 'container_alignment':
                    $out[$key] = in_array((string) $value, ['left', 'center', 'right'], true) ? (string) $value : (string) $default;
                    break;
                case 'background_color':
                case 'background_overlay_color':
                case 'text_color':
                case 'button_color':
                case 'button_text_color':
                case 'input_text_color':
                case 'input_border_color':
                    $color = trim((string) $value);
                    $out[$key] = preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $color) ? $color : (string) $default;
                    break;
                case 'custom_css':
                    $out[$key] = (string) $value;
                    break;
                default:
                    $out[$key] = trim((string) $value);
                    break;
            }
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    private function defaults(): array
    {
        return (array) config('aurix.appearance.defaults', []);
    }

    private function castValue(string $key, string $value): mixed
    {
        return match ($key) {
            'logo_height', 'background_overlay_opacity' => (int) $value,
            default => $value,
        };
    }

    private function settingsTableExists(): bool
    {
        return Schema::hasTable(config('aurix.tables.settings', 'aurix_settings'));
    }
}


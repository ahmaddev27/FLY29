<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class SettingsService
{
    private const CACHE_PREFIX = 'system_setting:';
    private const CACHE_TTL = 300; // 5 minutes

    /**
     * Get a setting value (typed). Returns $default if missing.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember(
            self::CACHE_PREFIX . $key,
            self::CACHE_TTL,
            function () use ($key, $default) {
                $setting = SystemSetting::find($key);

                if (! $setting) {
                    return $default;
                }

                // Decrypt password-type values transparently.
                if ($setting->value_type === 'password' && ! empty($setting->value)) {
                    try {
                        return Crypt::decryptString($setting->value);
                    } catch (DecryptException) {
                        return $default;
                    }
                }

                return $setting->typedValue();
            }
        );
    }

    /**
     * Set a setting value and invalidate cache.
     */
    public function set(string $key, mixed $value, ?int $userId = null): SystemSetting
    {
        $setting = SystemSetting::find($key);

        if (! $setting) {
            throw new \InvalidArgumentException("Unknown setting key: {$key}");
        }

        // Skip the masked sentinel — admin didn't change the password.
        if ($setting->value_type === 'password' && $value === '__UNCHANGED__') {
            return $setting;
        }

        $serialized = $this->serialize($value, $setting->value_type);

        $setting->update([
            'value'      => $serialized,
            'updated_by' => $userId,
        ]);

        Cache::forget(self::CACHE_PREFIX . $key);

        return $setting;
    }

    /**
     * Bulk get for category (returns array of typed values).
     *
     * @return array<string, mixed>
     */
    public function getCategory(string $category): array
    {
        $rows = SystemSetting::where('category', $category)->get();
        $out  = [];
        foreach ($rows as $row) {
            $out[$row->key] = $row->typedValue();
        }

        return $out;
    }

    /**
     * Clear all settings cache.
     */
    public function flushCache(): void
    {
        SystemSetting::pluck('key')->each(function (string $key) {
            Cache::forget(self::CACHE_PREFIX . $key);
        });
    }

    private function serialize(mixed $value, string $type): string
    {
        return match ($type) {
            'json'     => json_encode($value, JSON_UNESCAPED_UNICODE),
            'bool'     => $value ? 'true' : 'false',
            'password' => $value === '' ? '' : Crypt::encryptString((string) $value),
            default    => (string) $value,
        };
    }
}

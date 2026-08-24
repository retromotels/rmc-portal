<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /** Per-request cache of all settings. */
    private static ?array $cache = null;

    private static function all_cached(): array
    {
        if (self::$cache === null) {
            try {
                self::$cache = self::query()->pluck('value', 'key')->all();
            } catch (\Throwable $e) {
                self::$cache = []; // table not migrated yet — fall back to defaults
            }
        }
        return self::$cache;
    }

    public static function get(string $key, $default = null)
    {
        return self::all_cached()[$key] ?? $default;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $v = self::get($key, $default ? '1' : '0');
        return in_array((string) $v, ['1', 'true', 'on', 'yes'], true);
    }

    public static function put(string $key, $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
        if (self::$cache !== null) {
            self::$cache[$key] = $value;
        }
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Read a setting. Values written by put() are JSON-encoded (so arrays like
     * the FAQ/About content round-trip); values seeded raw (module flags/content)
     * are returned as-is. Returns $default when unset.
     */
    public static function get(string $key, $default = null)
    {
        $row = static::query()->where('key', $key)->first();
        if (!$row || $row->value === null) {
            return $default;
        }
        $decoded = json_decode($row->value, true);
        // Valid JSON → return decoded; otherwise it's a raw string value.
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $row->value;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $v = self::get($key, $default ? '1' : '0');
        return in_array((string) $v, ['1', 'true', 'on', 'yes'], true);
    }

    public static function put(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => json_encode($value)]);
    }
}

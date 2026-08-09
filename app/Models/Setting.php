<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /** Read a JSON setting (returns $default if unset). */
    public static function get(string $key, $default = null)
    {
        $row = static::query()->where('key', $key)->first();
        if (!$row || $row->value === null) return $default;
        $decoded = json_decode($row->value, true);
        return $decoded === null && $row->value !== 'null' ? $default : $decoded;
    }

    public static function put(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => json_encode($value)]);
    }
}

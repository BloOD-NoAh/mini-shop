<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        $row = static::query()->where('key', $key)->first();
        if (!$row) return $default;
        $val = $row->value;
        $json = json_decode($val, true);
        return json_last_error() === JSON_ERROR_NONE ? $json : $val;
    }

    public static function set(string $key, $value): void
    {
        $stored = is_array($value) || is_object($value) ? json_encode($value) : (string) $value;
        static::updateOrCreate(['key' => $key], ['value' => $stored]);
    }
}


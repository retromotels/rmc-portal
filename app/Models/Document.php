<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Document extends Model
{
    protected $fillable = ['title', 'slug', 'category', 'description', 'body', 'is_published', 'sort'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (self $d) {
            if (!$d->slug || $d->isDirty('title')) {
                $base = Str::slug($d->title) ?: 'document';
                $slug = $base;
                $i = 2;
                while (static::where('slug', $slug)->where('id', '!=', $d->id)->exists()) {
                    $slug = $base . '-' . $i++;
                }
                $d->slug = $slug;
            }
        });
    }

    public function events()
    {
        return $this->hasMany(DocumentEvent::class);
    }

    /** Available placeholders (for the admin editor help text). */
    public static function placeholders(): array
    {
        return [
            'property_name' => 'Property / motel name',
            'trading_name'  => 'Account (owner) name',
            'manager_name'  => 'Manager / contact name',
            'location'      => 'Town / location',
            'address'       => 'Street address (if on file)',
            'state'         => 'State',
            'email'         => 'Contact email',
            'phone'         => 'Contact phone',
            'today'         => "Today's date",
            'year'          => 'Current year',
        ];
    }

    /** Replace {{placeholders}} in the body with a property's details. */
    public function personalise(?User $property): string
    {
        return static::fill($this->body, $property);
    }

    public static function fill(string $body, ?User $property): string
    {
        $a = $property?->sectionData('A') ?? [];
        $b = $property?->sectionData('B') ?? [];

        $phone = $property?->phone ?: ($b['managerMobile'] ?? '');
        $map = [
            'property_name' => $property?->motel ?: ($a['propertyName'] ?? ($property?->name ?? 'Your property')),
            'trading_name'  => $property?->name ?? '',
            'manager_name'  => $b['managerName'] ?? ($property?->name ?? ''),
            'location'      => $property?->loc ?: trim(($a['city'] ?? '') . (isset($a['state']) ? ', ' . $a['state'] : '')),
            'address'       => $a['address'] ?? '',
            'state'         => $a['state'] ?? '',
            'email'         => $property?->email ?? '',
            'phone'         => $phone,
            'phone_line'    => $phone ? ' · ' . $phone : '',
            'today'         => now()->format('j F Y'),
            'year'          => now()->format('Y'),
        ];

        return preg_replace_callback('/\{\{\s*([a-z_]+)\s*\}\}/i', function ($m) use ($map) {
            $key = strtolower($m[1]);
            return array_key_exists($key, $map) ? e($map[$key]) : $m[0];
        }, $body);
    }
}

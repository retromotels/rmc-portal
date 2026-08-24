<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Supplier extends Model
{
    protected $fillable = [
        'name', 'slug', 'category', 'summary', 'description', 'offer_type',
        'offer_headline', 'discount_code', 'link_url', 'link_label', 'terms',
        'contact_email', 'website', 'is_active', 'sort',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (self $s) {
            if (!$s->slug || $s->isDirty('name')) {
                $base = Str::slug($s->name) ?: 'supplier';
                $slug = $base;
                $i = 2;
                while (static::where('slug', $slug)->where('id', '!=', $s->id)->exists()) {
                    $slug = $base . '-' . $i++;
                }
                $s->slug = $slug;
            }
        });
    }

    public function saves()
    {
        return $this->hasMany(SupplierSave::class);
    }

    public function requests()
    {
        return $this->hasMany(SupplierRequest::class);
    }

    public function categoryLabel(): string
    {
        return config('rmc.supplier_categories.' . $this->category, ucfirst((string) $this->category) ?: 'Other');
    }
}

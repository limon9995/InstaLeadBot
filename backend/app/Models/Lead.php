<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'bio',
        'country',
        'gender',
        'age',
        'job',
        'source_keyword',
        'tag',
        'notes',
        'score',
        'is_contacted',
    ];

    protected $casts = [
        'score'        => 'integer',
        'age'          => 'integer',
        'is_contacted' => 'boolean',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    // ─── Scopes ───────────────────────────────────────────────────────────

    public function scopeByCountry(Builder $query, ?string $country): Builder
    {
        return $country ? $query->where('country', $country) : $query;
    }

    public function scopeByGender(Builder $query, ?string $gender): Builder
    {
        return $gender ? $query->where('gender', $gender) : $query;
    }

    public function scopeByTag(Builder $query, ?string $tag): Builder
    {
        return $tag ? $query->where('tag', $tag) : $query;
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $term
            ? $query->where(function ($q) use ($term) {
                $q->where('username', 'like', "%{$term}%")
                  ->orWhere('bio', 'like', "%{$term}%");
            })
            : $query;
    }

    // ─── Accessors ────────────────────────────────────────────────────────

    public function getProfileUrlAttribute(): string
    {
        return "https://www.instagram.com/{$this->username}/";
    }

    public function getTagBadgeColorAttribute(): string
    {
        return match ($this->tag) {
            'hot'  => 'bg-red-100 text-red-800',
            'warm' => 'bg-yellow-100 text-yellow-800',
            'cold' => 'bg-blue-100 text-blue-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}

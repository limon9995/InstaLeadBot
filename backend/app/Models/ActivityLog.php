<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'event',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    public static function record(string $event, string $description = '', array $metadata = []): self
    {
        return static::create([
            'event'       => $event,
            'description' => $description,
            'metadata'    => $metadata ?: null,
        ]);
    }
}

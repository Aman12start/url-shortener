<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Url extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'original_url',
        'short_code',
        'title',
        'clicks_count',
        'last_clicked_at',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'clicks_count' => 'integer',
            'last_clicked_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class);
    }

    public function getShortUrlAttribute(): string
    {
        $baseUrl = rtrim((string) config('url-shortener.base_url'), '/');

        return $baseUrl.'/'.$this->short_code;
    }

    public function isAvailable(): bool
    {
        return $this->is_active && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}

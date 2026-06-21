<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Click extends Model
{
    use HasFactory;

    protected $fillable = [
        'url_id',
        'ip_hash',
        'user_agent_hash',
        'referer',
        'country',
    ];

    public function url(): BelongsTo
    {
        return $this->belongsTo(Url::class);
    }
}

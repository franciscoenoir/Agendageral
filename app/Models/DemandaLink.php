<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandaLink extends Model
{
    protected $fillable = ['demanda_id', 'url', 'label'];

    public function demanda(): BelongsTo
    {
        return $this->belongsTo(Demanda::class);
    }

    public function getDominioAttribute(): string
    {
        $host = parse_url($this->url, PHP_URL_HOST);
        return $host ? preg_replace('/^www\./', '', $host) : $this->url;
    }
}

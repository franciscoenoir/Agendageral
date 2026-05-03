<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pasta extends Model
{
    protected $fillable = ['nome', 'cor'];

    public function demandas(): HasMany
    {
        return $this->hasMany(Demanda::class);
    }
}

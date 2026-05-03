<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    protected $fillable = ['demanda_id', 'texto', 'concluido', 'ordem'];

    protected $casts = ['concluido' => 'boolean'];

    public function demanda()
    {
        return $this->belongsTo(Demanda::class);
    }
}

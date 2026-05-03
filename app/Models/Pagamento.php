<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pagamento extends Model
{
    protected $fillable = ['demanda_id', 'valor', 'data', 'observacao'];

    protected $casts = ['data' => 'date'];

    public function demanda()
    {
        return $this->belongsTo(Demanda::class);
    }
}

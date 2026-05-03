<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lembrete extends Model
{
    protected $fillable = ['texto', 'cor', 'pos_x', 'pos_y'];
}

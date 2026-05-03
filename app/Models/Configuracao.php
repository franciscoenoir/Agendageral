<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracao extends Model
{
    protected $fillable = ['chave', 'valor'];

    protected $table = 'configuracoes';

    public static function get(string $chave, mixed $default = null): mixed
    {
        $config = static::where('chave', $chave)->first();
        return $config ? $config->valor : $default;
    }

    public static function set(string $chave, mixed $valor): void
    {
        static::updateOrCreate(['chave' => $chave], ['valor' => $valor]);
    }
}

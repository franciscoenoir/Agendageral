<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Demanda extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pasta_id', 'titulo', 'categoria', 'urgencia', 'status',
        'data_inicio', 'data_limite', 'responsavel', 'valor',
        'observacoes', 'auto_escalado', 'google_event_id',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_limite' => 'date',
        'auto_escalado' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Demanda $demanda) {
            if ($demanda->status === 'concluido') {
                return;
            }

            $dias = Carbon::today()->diffInDays($demanda->data_limite, false);

            if ($dias <= 0 && $demanda->urgencia !== 'urgente') {
                $demanda->urgencia = 'urgente';
                $demanda->auto_escalado = true;
            } elseif ($dias <= 1 && $demanda->urgencia === 'media') {
                $demanda->urgencia = 'alta';
                $demanda->auto_escalado = true;
            } elseif ($dias <= 2 && $demanda->urgencia === 'baixa') {
                $demanda->urgencia = 'media';
                $demanda->auto_escalado = true;
            }
        });
    }

    public function links(): HasMany
    {
        return $this->hasMany(DemandaLink::class);
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(Pagamento::class);
    }

    public function pasta()
    {
        return $this->belongsTo(Pasta::class);
    }

    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    public function scopeAtrasadas($query)
    {
        return $query->where('data_limite', '<', today())->where('status', 'pendente');
    }

    public function scopeUrgentes($query)
    {
        return $query->where('urgencia', 'urgente')->where('status', 'pendente');
    }

    public function scopeSemana($query)
    {
        return $query->whereBetween('data_limite', [today(), today()->addDays(7)])
                     ->where('status', 'pendente');
    }

    public function getDiasRestantesAttribute(): int
    {
        return (int) Carbon::today()->diffInDays($this->data_limite, false);
    }

    public function getPrazoLabelAttribute(): string
    {
        $dias = $this->dias_restantes;

        if ($dias === 0) return 'Vence hoje';
        if ($dias > 0) return "Em {$dias}d";
        return abs($dias) . 'd atrasada';
    }

    public function isAtrasada(): bool
    {
        return $this->dias_restantes < 0 && $this->status === 'pendente';
    }

    public function googleCalendarUrl(): string
    {
        $inicio = ($this->data_inicio ?? $this->data_limite)->format('Ymd');
        $fim    = $this->data_limite->copy()->addDay()->format('Ymd');

        $details = implode(' | ', array_filter([
            $this->observacoes,
            $this->categoria,
            $this->responsavel,
        ]));

        return 'https://calendar.google.com/calendar/render?' . http_build_query([
            'action'  => 'TEMPLATE',
            'text'    => $this->titulo,
            'dates'   => "{$inicio}/{$fim}",
            'details' => $details,
        ]);
    }
}

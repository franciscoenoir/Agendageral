<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class AlertaDemandaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Collection $demandas) {}

    public function envelope(): Envelope
    {
        $count = $this->demandas->count();
        return new Envelope(subject: "⚠️ Alerta: {$count} demanda(s) requerem atenção");
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.alerta-demanda', with: [
            'demandas'  => $this->demandas,
            'atrasadas' => $this->demandas->filter(fn($d) => $d->isAtrasada()),
            'urgentes'  => $this->demandas->filter(fn($d) => $d->urgencia === 'urgente' && !$d->isAtrasada()),
            'proximas'  => $this->demandas->filter(fn($d) => !$d->isAtrasada() && $d->urgencia !== 'urgente'),
        ]);
    }
}

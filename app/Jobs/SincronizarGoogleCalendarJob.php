<?php

namespace App\Jobs;

use App\Models\Demanda;
use App\Services\GoogleCalendarService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SincronizarGoogleCalendarJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public Demanda $demanda) {}

    public function handle(GoogleCalendarService $service): void
    {
        $demanda = $this->demanda->fresh();
        if (!$demanda) return;

        if ($demanda->google_event_id) {
            $service->atualizarEvento($demanda);
        } else {
            $eventId = $service->criarEvento($demanda);
            if ($eventId) {
                $demanda->updateQuietly(['google_event_id' => $eventId]);
            }
        }
    }
}

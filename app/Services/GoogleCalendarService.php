<?php

namespace App\Services;

use App\Models\Configuracao;
use App\Models\Demanda;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    private string $baseUrl = 'https://www.googleapis.com/calendar/v3';

    private function accessToken(): ?string
    {
        $token = Configuracao::get('google_access_token');
        $expiry = Configuracao::get('google_token_expiry');

        if (!$token) return null;

        if ($expiry && now()->timestamp > (int) $expiry) {
            return $this->refreshToken();
        }

        return $token;
    }

    private function refreshToken(): ?string
    {
        $refreshToken = Configuracao::get('google_refresh_token');
        if (!$refreshToken) return null;

        $response = Http::post('https://oauth2.googleapis.com/token', [
            'client_id'     => Configuracao::get('google_client_id'),
            'client_secret' => Configuracao::get('google_client_secret'),
            'refresh_token' => $refreshToken,
            'grant_type'    => 'refresh_token',
        ]);

        if ($response->failed()) return null;

        $data = $response->json();
        Configuracao::set('google_access_token', $data['access_token']);
        Configuracao::set('google_token_expiry', now()->addSeconds($data['expires_in'])->timestamp);

        return $data['access_token'];
    }

    private function calendarId(): string
    {
        return Configuracao::get('google_calendar_id', 'primary');
    }

    public function criarEvento(Demanda $demanda): ?string
    {
        $token = $this->accessToken();
        if (!$token) return null;

        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/calendars/{$this->calendarId()}/events", $this->buildPayload($demanda));

        if ($response->failed()) {
            Log::error('Google Calendar: erro ao criar evento', ['body' => $response->body()]);
            return null;
        }

        return $response->json('id');
    }

    public function atualizarEvento(Demanda $demanda): bool
    {
        $token = $this->accessToken();
        if (!$token || !$demanda->google_event_id) return false;

        $response = Http::withToken($token)
            ->put("{$this->baseUrl}/calendars/{$this->calendarId()}/events/{$demanda->google_event_id}", $this->buildPayload($demanda));

        return $response->successful();
    }

    public function deletarEvento(string $eventId): bool
    {
        $token = $this->accessToken();
        if (!$token) return false;

        $response = Http::withToken($token)
            ->delete("{$this->baseUrl}/calendars/{$this->calendarId()}/events/{$eventId}");

        return $response->successful();
    }

    private function buildPayload(Demanda $demanda): array
    {
        return [
            'summary'     => "[{$demanda->urgencia}] {$demanda->titulo}",
            'description' => $demanda->observacoes,
            'start'       => ['date' => $demanda->data_limite->toDateString()],
            'end'         => ['date' => $demanda->data_limite->addDay()->toDateString()],
        ];
    }
}

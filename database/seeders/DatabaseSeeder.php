<?php

namespace Database\Seeders;

use App\Models\Configuracao;
use App\Models\Demanda;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'Francisco Enoir',
            'email'    => 'francisco@exemplo.com.br',
            'password' => Hash::make('demandas2024'),
            'role'     => 'admin',
        ]);

        Demanda::create([
            'titulo'      => 'Revisar arquitetura do projeto backend',
            'categoria'   => 'Engenharia',
            'urgencia'    => 'alta',
            'status'      => 'pendente',
            'data_limite' => now()->addDays(5)->toDateString(),
            'responsavel' => 'Francisco',
            'observacoes' => 'Revisar decisões de arquitetura e documentar.',
        ]);

        Demanda::create([
            'titulo'      => 'Consulta médica anual',
            'categoria'   => 'Particular',
            'urgencia'    => 'media',
            'status'      => 'pendente',
            'data_limite' => now()->addDays(14)->toDateString(),
        ]);

        Demanda::create([
            'titulo'      => 'Pagar IPTU',
            'categoria'   => 'Administrativo',
            'urgencia'    => 'baixa',
            'status'      => 'pendente',
            'data_limite' => now()->addDays(30)->toDateString(),
            'observacoes' => 'Verificar desconto para pagamento à vista.',
        ]);

        $chaves = [
            'email_alertas', 'email_dias_aviso',
            'zapi_instance', 'zapi_token', 'zapi_numero', 'zapi_webhook_token',
            'google_client_id', 'google_client_secret', 'google_calendar_id',
            'google_access_token', 'google_refresh_token', 'google_token_expiry',
        ];

        foreach ($chaves as $chave) {
            Configuracao::create([
                'chave' => $chave,
                'valor' => $chave === 'email_dias_aviso' ? '[1]' : null,
            ]);
        }
    }
}

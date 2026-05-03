# Instrução para Claude Code — Sistema de Gerenciamento de Demandas em Laravel

## Visão geral do projeto

Construir um sistema web de gerenciamento de demandas pessoais e profissionais, com autenticação, painel de controle, integração com Google Agenda e bot de entrada via WhatsApp (Z-API). O sistema deve ser moderno, responsivo e funcional para uso diário por um único usuário (ou pequena equipe).

---

## Stack tecnológica

- **Backend:** Laravel 11 (PHP 8.3)
- **Frontend:** Blade + Alpine.js + Tailwind CSS
- **Banco de dados:** SQLite
- **Autenticação:** Laravel Breeze (simples, com login/senha)
- **PDF:** Laravel DomPDF (`barryvdh/laravel-dompdf`)
- **Filas/Jobs:** Laravel Queue (para webhooks do WhatsApp)
- **HTTP Client:** Laravel Http (para Z-API e Google Calendar API)

---

## Estrutura do banco de dados

### Tabela `users`
Gerada pelo Laravel Breeze padrão. Adicionar campo:
- `role` (string, default: `admin`)

### Tabela `demandas`
```
id                  bigint PK auto_increment
titulo              varchar(255) NOT NULL
categoria           enum('Engenharia','Firedrill','Rosa Garden','Particular','Família','Administrativo','Outro')
urgencia            enum('urgente','alta','media','baixa') default 'media'
status              enum('pendente','concluido') default 'pendente'
data_inicio         date NULL
data_limite         date NOT NULL
responsavel         varchar(255) NULL
observacoes         text NULL
auto_escalado       boolean default false
google_event_id     varchar(255) NULL
created_at          timestamp
updated_at          timestamp
deleted_at          timestamp NULL  (SoftDeletes)
```

### Tabela `demanda_links`
```
id              bigint PK
demanda_id      bigint FK demandas.id (cascade delete)
url             varchar(500)
label           varchar(255) NULL
created_at      timestamp
updated_at      timestamp
```

### Tabela `configuracoes`
```
id          bigint PK
chave       varchar(100) UNIQUE
valor       text NULL
created_at  timestamp
updated_at  timestamp
```
Valores iniciais a serem seedados:
- `email_alertas` → e-mail para alertas
- `email_dias_aviso` → JSON array ex: `[1]`
- `zapi_instance` → ID da instância Z-API
- `zapi_token` → token Z-API
- `google_client_id` → Client ID OAuth Google
- `google_client_secret` → Client Secret OAuth Google
- `google_calendar_id` → ID do calendário Google

---

## Models

### `Demanda`
- Relacionamento `hasMany` com `DemandaLink`
- Scope `pendentes()` → where status = pendente
- Scope `atrasadas()` → where data_limite < today AND status = pendente
- Scope `urgentes()` → where urgencia = urgente AND status = pendente
- Scope `semana()` → where data_limite between today and today+7 AND status = pendente
- Accessor `diasRestantes` → diferença entre hoje e data_limite
- Accessor `prazoLabel` → "Vence hoje", "X dias", "Xd atrasada", etc.
- SoftDeletes habilitado
- Boot method para aplicar prioridade automática ao salvar:
  - Se dias_restantes <= 0 e urgencia != urgente → escalar para urgente, auto_escalado = true
  - Se dias_restantes <= 1 e urgencia = media → escalar para alta, auto_escalado = true
  - Se dias_restantes <= 2 e urgencia = baixa → escalar para media, auto_escalado = true

### `DemandaLink`
- Relacionamento `belongsTo` Demanda
- Accessor `dominio` → extrai domínio da URL para exibição

### `Configuracao`
- Método estático `get(chave, default)` → busca valor pelo chave
- Método estático `set(chave, valor)` → cria ou atualiza

---

## Controllers

### `DemandaController` (resource completo)
- `index` → lista com filtros: status, urgencia, categoria, busca, data
- `create` → formulário de nova demanda
- `store` → validação + salva demanda + links + dispara job Google Calendar
- `show` → detalhes da demanda
- `edit` → formulário de edição
- `update` → atualiza demanda + links + sincroniza Google Calendar
- `destroy` → soft delete
- `concluir(id)` → toggle status pendente/concluido
- `exportPdf` → gera PDF semanal com DomPDF

### `AgendaController`
- `index` → view da agenda semanal (7 dias à frente)
- `data` → endpoint JSON retornando demandas dos próximos 7 dias

### `ConfiguracaoController`
- `index` → view de configurações (e-mail, Z-API, Google)
- `update` → salva configurações

### `AlertaController`
- `enviar` → monta e-mail com demandas urgentes/atrasadas/vencendo e envia via Laravel Mail

### `WhatsAppController`
- `webhook(Request $request)` → recebe POST do Z-API, interpreta mensagem e cria demanda
- `responder(demanda)` → envia confirmação via Z-API

### `GoogleController`
- `redirect` → redireciona para OAuth Google
- `callback` → recebe token e salva
- `sincronizar(demanda)` → cria/atualiza evento no Google Calendar

---

## Rotas (`routes/web.php`)

```php
// Auth (Breeze)
require __DIR__.'/auth.php';

// App (autenticado)
Route::middleware('auth')->group(function () {
    Route::get('/', [DemandaController::class, 'index'])->name('dashboard');
    Route::resource('demandas', DemandaController::class);
    Route::patch('demandas/{demanda}/concluir', [DemandaController::class, 'concluir'])->name('demandas.concluir');
    Route::get('demandas-pdf', [DemandaController::class, 'exportPdf'])->name('demandas.pdf');

    Route::get('agenda', [AgendaController::class, 'index'])->name('agenda');
    Route::get('agenda/data', [AgendaController::class, 'data'])->name('agenda.data');

    Route::get('configuracoes', [ConfiguracaoController::class, 'index'])->name('configuracoes');
    Route::put('configuracoes', [ConfiguracaoController::class, 'update'])->name('configuracoes.update');

    Route::post('alertas/enviar', [AlertaController::class, 'enviar'])->name('alertas.enviar');

    Route::get('google/redirect', [GoogleController::class, 'redirect'])->name('google.redirect');
    Route::get('google/callback', [GoogleController::class, 'callback'])->name('google.callback');
});

// Webhook Z-API (sem autenticação, protegido por token)
Route::post('webhook/whatsapp', [WhatsAppController::class, 'webhook'])->name('webhook.whatsapp');
```

---

## Views Blade (com Alpine.js + Tailwind)

### Layout principal (`layouts/app.blade.php`)
- Sidebar com navegação: Dashboard, Agenda, Configurações
- Header com nome do usuário logado e botão sair
- Barra de status de sincronização (se Google Calendar conectado)
- Responsivo (menu hamburguer no mobile)

### `demandas/index.blade.php` — Dashboard principal
- Cards de estatísticas no topo: Total, Urgentes, Atrasadas, Esta semana
- Filtros: todos, atrasadas, urgentes, hoje, esta semana, pendentes, concluídas
- Campo de busca por título/observação/responsável
- Lista de cards de demanda com:
  - Borda colorida esquerda por urgência (vermelho/laranja/azul/verde)
  - Background vermelho claro se atrasada
  - Badges de urgência e status
  - Badge "auto" roxo se auto_escalado
  - Datas de início e limite
  - Categoria e responsável
  - Observações
  - Links clicáveis
  - Botões: Concluir, Editar, Excluir, Google Agenda
- Ordenação automática: urgência → data_limite

### `demandas/create.blade.php` e `demandas/edit.blade.php`
- Formulário completo com todos os campos
- Campo de links dinâmico (Alpine.js para adicionar/remover)
- Seletor de data com calendário nativo HTML5
- Validação client-side e server-side

### `agenda/index.blade.php`
- Grid de 7 colunas (um por dia)
- Coluna de hoje destacada em azul
- Cards mini por demanda com cor por urgência
- Clique no card leva para o detalhe da demanda

### `configuracoes/index.blade.php`
- Seção: Alertas por e-mail (e-mail + checkboxes de antecedência)
- Seção: Google Calendar (botão conectar OAuth + status)
- Seção: Z-API WhatsApp (instância + token + número + URL do webhook)
- Seção: Alterar senha

---

## Serviços (`app/Services/`)

### `ZApiService`
```php
// Enviar mensagem
public function enviarMensagem(string $numero, string $mensagem): bool

// Interpretar mensagem recebida e extrair campos da demanda
public function interpretarMensagem(string $texto): array|null
// Formato esperado:
// /demanda
// Título: ...
// Categoria: ...
// Prazo: dd/mm/yyyy
// Início: dd/mm/yyyy (opcional)
// Urgência: urgente|alta|media|baixa
// Obs: ...
```

### `GoogleCalendarService`
```php
// Criar evento
public function criarEvento(Demanda $demanda): string|null  // retorna event_id

// Atualizar evento
public function atualizarEvento(Demanda $demanda): bool

// Deletar evento
public function deletarEvento(string $eventId): bool
```

### `AlertaService`
```php
// Verificar demandas que precisam de alerta baseado nas configurações
public function demandasParaAlertar(): Collection

// Montar corpo do e-mail
public function montarCorpo(Collection $demandas): string
```

---

## Jobs (`app/Jobs/`)

### `SincronizarGoogleCalendarJob`
- Recebe uma `Demanda`
- Chama `GoogleCalendarService` para criar ou atualizar o evento
- Tratamento de erro com retry automático

### `EnviarAlertaEmailJob`
- Verifica configurações de alerta
- Chama `AlertaService` para montar o e-mail
- Envia via `Mail::to()`

---

## Mail (`app/Mail/`)

### `AlertaDemandaMail`
- Template Blade com lista de atrasadas, urgentes e vencendo em breve
- Formatação limpa para e-mail

---

## Validação (`app/Http/Requests/`)

### `DemandaRequest`
```php
'titulo'       => 'required|string|max:255',
'categoria'    => 'required|in:Engenharia,Firedrill,Rosa Garden,Particular,Família,Administrativo,Outro',
'urgencia'     => 'required|in:urgente,alta,media,baixa',
'data_inicio'  => 'nullable|date',
'data_limite'  => 'required|date',
'responsavel'  => 'nullable|string|max:255',
'observacoes'  => 'nullable|string',
'links'        => 'nullable|array',
'links.*'      => 'url',
```

---

## Autenticação e segurança

- Usar Laravel Breeze com sessão
- Middleware `auth` em todas as rotas do app
- Webhook Z-API protegido por token secreto no header (`X-Webhook-Token`)
- Senhas hasheadas com bcrypt (padrão Laravel)
- CSRF em todos os formulários
- Validação de todas as entradas

---

## Seeders

### `DatabaseSeeder`
- Criar usuário padrão:
  - Nome: Francisco Enoir
  - E-mail: `francisco@exemplo.com.br` *(alterar após instalação)*
  - Senha: `demandas2024` *(alterar após instalação)*
- Criar 3 demandas de exemplo (uma por categoria principal)
- Criar configurações padrão vazias

---

## Comandos Artisan customizados

### `app:verificar-alertas`
- Rodar diariamente via scheduler
- Verifica demandas que precisam de alerta conforme configuração
- Dispara `EnviarAlertaEmailJob`

### Scheduler (`routes/console.php`)
```php
Schedule::command('app:verificar-alertas')->dailyAt('08:00');
```

---

## Integrações

### Z-API (WhatsApp)
1. Cadastrar em [z-api.io](https://z-api.io)
2. Criar instância e conectar número via QR Code
3. Configurar webhook apontando para: `https://seu-dominio.com/webhook/whatsapp`
4. Adicionar `X-Webhook-Token` nas configurações do webhook
5. Preencher instância e token nas Configurações do app

### Google Calendar
1. Acessar [console.cloud.google.com](https://console.cloud.google.com)
2. Criar projeto → Ativar Google Calendar API
3. Criar credenciais OAuth 2.0 (Aplicativo Web)
4. Adicionar URI de redirecionamento: `https://seu-dominio.com/google/callback`
5. Preencher Client ID e Secret nas Configurações do app
6. Clicar em "Conectar Google Calendar" para autorizar

---

## Instalação e setup

```bash
# 1. Criar projeto
composer create-project laravel/laravel demandas
cd demandas

# 2. Instalar dependências
composer require laravel/breeze barryvdh/laravel-dompdf

# 3. Instalar Breeze
php artisan breeze:install blade

# 4. Instalar frontend
npm install && npm run build

# 5. Configurar .env
DB_DATABASE=demandas
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu@gmail.com
MAIL_PASSWORD=sua-senha-app
MAIL_FROM_ADDRESS=seu@gmail.com

# 6. Migrations e seeds
php artisan migrate --seed

# 7. Rodar local
php artisan serve
```

---

## Observações finais para o Claude Code

- Usar **Tailwind CSS** para todo o estilo — não usar Bootstrap
- Usar **Alpine.js** para interatividade no frontend (sem Vue/React)
- Manter o visual limpo e próximo ao app HTML atual: cards com borda colorida esquerda, badges de urgência, agenda semanal em grid
- Todos os textos em **português brasileiro**
- Datas sempre no formato **dd/mm/yyyy** para exibição, `Y-m-d` no banco
- O app deve funcionar bem em **mobile** (responsivo)
- Priorizar **simplicidade** — é um app pessoal, não enterprise
- Comentar os métodos principais em português

# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Sistema de gerenciamento de demandas pessoais/profissionais em Laravel 11. Integra Google Calendar (OAuth) e bot WhatsApp via Z-API. App para uso individual — priorizar simplicidade sobre abstração.

## Dev Commands

```bash
php artisan serve          # inicia o servidor em http://localhost:8000
npm run dev                # watch de assets (terminal separado)
npm run build              # build de produção
php artisan migrate:fresh --seed   # reset completo do banco
php artisan queue:work     # processa jobs (Calendar sync, e-mail alerts)
php artisan schedule:run   # dispara o scheduler manualmente
php artisan app:verificar-alertas  # comando customizado de alertas
php artisan tinker         # REPL
```

> **Obrigatório:** rodar `npm run build` após qualquer alteração em arquivos de view, CSS ou JS antes de reportar a tarefa como concluída.

> Composer não está instalado globalmente. Se precisar: `curl -sS https://getcomposer.org/installer | php -- --install-dir=/tmp --filename=composer && php /tmp/composer <comando>`

## Stack

- **Backend:** Laravel 11 / PHP 8.5
- **Frontend:** Blade + Alpine.js + Tailwind CSS (sem Vue/React/Bootstrap)
- **DB:** SQLite (`database/database.sqlite`)
- **Auth:** Laravel Breeze (sessão)
- **PDF:** `barryvdh/laravel-dompdf`
- **Filas:** Laravel Queue com driver `database`
- **HTTP:** facade `Http` do Laravel (Z-API e Google Calendar API)
- **Node:** v25 (instalado via Homebrew em `/opt/homebrew/bin`)

## Credenciais de acesso (seed)

- **E-mail:** `francisco@exemplo.com.br`
- **Senha:** `demandas2024`

## Architecture

### Model `Demanda`

Modelo central com `SoftDeletes`. O método `boot()` auto-escalona urgência ao salvar (exceto demandas concluídas):

| Condição | Ação |
|---|---|
| `dias_restantes <= 0` e urgência ≠ `urgente` | → `urgente` + `auto_escalado = true` |
| `dias_restantes <= 1` e urgência = `media` | → `alta` + `auto_escalado = true` |
| `dias_restantes <= 2` e urgência = `baixa` | → `media` + `auto_escalado = true` |

Scopes: `pendentes()`, `atrasadas()`, `urgentes()`, `semana()`.
Accessors: `diasRestantes` (int), `prazoLabel` (string legível).
Método: `isAtrasada()` → bool.

Use `saveQuietly()` ou `updateQuietly()` para salvar sem disparar o boot (ex: gravar `google_event_id` no job).

### `Configuracao`

Chave-valor no banco. Nunca use `.env` para credenciais de integração.

```php
Configuracao::get('chave', $default);
Configuracao::set('chave', $valor);
```

Chaves existentes no banco: `email_alertas`, `email_dias_aviso` (JSON array), `zapi_instance`, `zapi_token`, `zapi_numero`, `zapi_webhook_token`, `google_client_id`, `google_client_secret`, `google_calendar_id`, `google_access_token`, `google_refresh_token`, `google_token_expiry`.

### Services (`app/Services/`)

| Service | Responsabilidade |
|---|---|
| `GoogleCalendarService` | Refresh de token OAuth + criar/atualizar/deletar eventos |
| `ZApiService` | Enviar mensagens + parsear texto `/demanda\nTítulo: ...\nPrazo: dd/mm/yyyy` |
| `AlertaService` | Consultar demandas para alerta conforme `email_dias_aviso` |

### Jobs (`app/Jobs/`)

- `SincronizarGoogleCalendarJob` — disparado no `store`/`update` do `DemandaController`; cria ou atualiza evento no Calendar
- `EnviarAlertaEmailJob` — disparado pelo comando `app:verificar-alertas`; usa `AlertaService` + `AlertaDemandaMail`

### Rotas relevantes

| Método | URI | Nome |
|---|---|---|
| `GET` | `/` | `dashboard` |
| `RESOURCE` | `/demandas` | `demandas.*` |
| `PATCH` | `/demandas/{demanda}/concluir` | `demandas.concluir` |
| `GET` | `/demandas-pdf` | `demandas.pdf` |
| `GET/PUT` | `/configuracoes` | `configuracoes` / `configuracoes.update` |
| `GET` | `/agenda`, `/agenda/data` | `agenda`, `agenda.data` |
| `GET` | `/google/redirect`, `/google/callback` | `google.redirect`, `google.callback` |
| `POST` | `/webhook/whatsapp` | `webhook.whatsapp` (sem auth, token no header) |

## Views

```
resources/views/
├── layouts/app.blade.php       # layout com sidebar, header, flash messages
├── demandas/
│   ├── index.blade.php         # dashboard com stats, filtros e lista de cards
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── show.blade.php
│   ├── _form.blade.php         # partial compartilhado por create/edit (Alpine.js para links dinâmicos)
│   └── pdf.blade.php           # template para DomPDF (sem layout principal)
├── agenda/index.blade.php      # grid 7 colunas; carrega dados via fetch /agenda/data
├── configuracoes/index.blade.php
└── emails/alerta-demanda.blade.php  # Markdown mail
```

## Frontend Conventions

- Datas: `dd/mm/yyyy` no display, `Y-m-d` no banco
- Cards de demanda: borda esquerda colorida por urgência (red/orange/blue/green); fundo vermelho claro se atrasada
- Alpine.js para interatividade (menu mobile, links dinâmicos no formulário, fetch da agenda)
- O layout usa `@yield('content')` — não é component/slot

## Scheduler

```php
// routes/console.php
Schedule::command('app:verificar-alertas')->dailyAt('08:00');
```

# Architecture

## Visão Geral

Hosts Manager é uma aplicação **Laravel 13** monolítica que gerencia Virtual Hosts Apache, arquivo hosts do Windows e certificados SSL locais. O banco de dados é **SQLite** — sem necessidade de MySQL/PostgreSQL.

```
┌─────────────────────────────────────────────────────────────┐
│                     Browser (Frontend)                       │
│  Tailwind CSS v4 · Font Awesome 6 · Vite 8 · Dark Mode      │
└──────────────────┬──────────────────────────────────────────┘
                   │ HTTP / Session
┌──────────────────▼──────────────────────────────────────────┐
│                 Laravel 13 (PHP 8.3+)                        │
│                                                              │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌─────────────┐  │
│  │Controllers│  │ Services │  │  Models  │  │  Middleware  │  │
│  └─────┬────┘  └────┬─────┘  └────┬─────┘  └──────┬──────┘  │
│        │            │             │               │          │
│        └────────────┴─────────────┴───────────────┘          │
│                           │                                  │
│                    ┌──────▼──────┐                           │
│                    │   SQLite    │                           │
│                    └─────────────┘                           │
└──────────────────┬──────────────────────────────────────────┘
                   │ shell_exec / exec / COM
┌──────────────────▼──────────────────────────────────────────┐
│                  Windows System                              │
│                                                              │
│  ┌──────────┐  ┌──────────────┐  ┌──────────────────────┐   │
│  │  Apache   │  │  mkcert SSL  │  │  hosts file          │   │
│  │  httpd    │  │  certificates│  │  C:\Windows\System32\ │   │
│  └──────────┘  └──────────────┘  │  drivers\etc\hosts    │   │
│                                  └──────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

## Camadas

### Controllers

Cada controller tem uma responsabilidade única:

| Controller | Rota | Função |
|---|---|---|
| `DashboardController` | `GET /` | Painel com métricas do sistema |
| `VirtualHostController` | `/virtual-hosts/*` | CRUD, batch, toggle, backup, sync |
| `ApacheLogController` | `/logs/*` | Leitura e streaming de error log |
| `SettingsController` | `/settings` | Configurações do sistema |
| `ActivityLogController` | `/audit-log` | Histórico de ações |
| `FileManagerController` | `/files/*` | Navegação de diretórios |

### Services

Camada de lógica de negócios, sem dependência direta de HTTP:

| Service | Responsabilidade |
|---|---|
| `ApacheService` | Ler/escrever config, restart, test (`httpd -t`), status |
| `HostsFileService` | Gerenciar entradas no arquivo hosts do Windows |
| `MkcertService` | Gerar/instalar certificados SSL locais |
| `VhostManagerService` | Orquestrador: coordena Apache + hosts + mkcert |
| `ActivityLogService` | Registrar ações do usuário no banco |
| `ProjectScaffoldService` | Criar estrutura inicial de projetos |

### Models

| Model | Tabela | Descrição |
|---|---|---|
| `VirtualHost` | `virtual_hosts` | Hosts gerenciados |
| `Setting` | `settings` | Config persistida (key-value) |
| `ActivityLog` | `activity_logs` | Audit log (polimórfico: `subject_type`/`subject_id`) |

## Fluxos Principais

### Criar Virtual Host

```
1. Form → VirtualHostController@store
2. ├── Valida (StoreVirtualHostRequest)
3. ├── Cria registro no banco
4. ├── HostsFileService → adiciona 127.0.0.1 dominio.local
5. ├── MkcertService → gera certificado SSL (se SSL habilitado)
6. ├── ApacheService → writeConfig() → atualiza httpd-vhosts.conf
7. ├── ApacheService → restart() → reinicia Apache
8. ├── ActivityLogService → registra ação
9. └── Redirect com flash message
```

### Restart Apache (com fallback)

```
1. ApacheService → restart()
2. ├── Se Apache rodando:
3. │   ├── httpd -k restart (funciona sem admin)
4. │   ├── Se falhar → net stop + net start
5. │   ├── Se falhar → PowerShell elevado (RunAs)
6. │   └── Se falhar → taskkill + httpd direto
7. └── Se Apache parado:
8.     ├── net start
9.     ├── Se falhar → PowerShell elevado
10.    └── Se falhar → httpd direto (COM WScript.Shell)
```

## Segurança

- **Autenticação**: sessão + bcrypt, sem user model
- **Throttle**: 5 tentativas/min no login
- **Validação**: server name com regex `/^[a-z0-9]([a-z0-9.-]*[a-z0-9])?$/`
- **Cache**: status do Apache em cache de 10s (`Cache::remember`)

## Dependências Externas

| Recurso | Como é chamado |
|---|---|
| Apache | `exec('httpd.exe -k restart 2>&1')`, `exec('net start Apache2.4 2>&1')` |
| mkcert | `exec('mkcert.exe ... 2>&1')` |
| hosts file | `File::put()` (Facade) |
| Tasklist | `exec('tasklist /NH /FI "IMAGENAME eq httpd.exe" 2>&1')` |
| PowerShell | `exec('powershell Start-Process ... -Verb RunAs')` |
| COM | `new COM("WScript.Shell")` (fallback para iniciar Apache) |

## Testes

- **PHPUnit 12.x** com SQLite in-memory
- **Mockery** para simular File system, exec, COM
- Cobertura: ApacheService, HostsFileService, MkcertService, VhostManagerService, controllers
- CI: GitHub Actions com matriz PHP 8.3/8.4/8.5

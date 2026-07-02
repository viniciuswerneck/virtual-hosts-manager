# 🖥️ Hosts Manager — Gerenciador de Virtual Hosts Apache

[![Tests](https://github.com/viniciuswerneck/virtual-hosts-manager/actions/workflows/tests.yml/badge.svg)](https://github.com/viniciuswerneck/virtual-hosts-manager/actions/workflows/tests.yml)
[![PHP](https://img.shields.io/badge/PHP-^8.3-777BB4?logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

Aplicação web em **Laravel 13** para gerenciar **Virtual Hosts Apache**, arquivo **hosts do Windows**, certificados **SSL locais (mkcert)** e muito mais — tudo por uma interface gráfica moderna no navegador.

> Desenvolvido para desenvolvedores PHP/web no Windows que utilizam Apache e precisam gerenciar múltiplos domínios locais com HTTPS de forma rápida e prática.

---

## ✨ Funcionalidades

### Gerenciamento de Hosts
| Funcionalidade | Descrição |
|---|---|
| **CRUD de Virtual Hosts** | Criar, editar, visualizar e excluir hosts |
| **SSL Automático** | Geração de certificados via mkcert com um clique |
| **Configuração Apache** | Blocos VirtualHost nas portas 80 e 443 |
| **Arquivo Hosts** | Registro automático de `127.0.0.1 dominio.local` |
| **Validação de Sintaxe** | `httpd -t` antes de reiniciar o Apache |
| **Sincronização** | Importa hosts existentes do Apache para o banco |
| **Exportar / Importar** | JSON individual ou backup ZIP completo |
| **Batch Operations** | Ativar/desativar ou excluir múltiplos hosts de uma vez |

### Versão PHP por Host
Cada virtual host pode ter uma **versão PHP específica** via handler FCGID — perfeito para projetos legados e modernos no mesmo servidor.

### Monitoramento e Logs
| Funcionalidade | Descrição |
|---|---|
| **Apache Error Log** | Visualizador com busca, filtro por severidade, auto-refresh |
| **Interpretação de Logs** | Badges de severidade, timestamp formatado, destaque de busca |
| **Audit Log** | Histórico completo de ações: criação, edição, exclusão, toggle |

### Painel Premium (Dashboard)
- **Cards animados** com contagem de vhosts ativos/inativos/SSL
- **Status do Apache**, PID count, teste de config
- **Disco**: espaço livre/usado/total em tempo real
- **PHP**: versão, memory limit, max execution, upload max
- **Atividades Recentes** com dots coloridos por tipo de ação
- **Últimos Vhosts** com badges PHP/SSL/Template
- **Ações Rápidas**: Apache restart, sync, export, phpMyAdmin

### Utilitários
| Funcionalidade | Descrição |
|---|---|
| **File Manager** | Navegação de diretórios com breadcrumbs e ícones |
| **phpMyAdmin** | Auto-login com credenciais configuradas |
| **Backup completo** | Exporta ZIP com banco + configs — importa de volta |
| **Regenerar SSL** | Recria certificado individual com um clique |
| **Modo Escuro** | Persistência via localStorage com fallback a `prefers-color-scheme` |
| **Interface Responsiva** | Tailwind CSS v4, Font Awesome 6, português brasileiro |

---

## 📋 Índice

- [Requisitos](#-requisitos)
- [Instalação Passo a Passo](#-instalação-passo-a-passo)
- [Configuração](#-configuração)
- [Como Usar](#-como-usar)
- [Comandos Úteis](#-comandos-úteis)
- [Estrutura do Projeto](#-estrutura-do-projeto)
- [Testes](#-testes)
- [Solução de Problemas](#-solução-de-problemas)
- [Tecnologias](#-tecnologias)
- [Roadmap](#-roadmap)
- [FAQ](#-faq)
- [Licença](#-licença)

---

## 📋 Requisitos

| Requisito | Versão | Download |
|---|---|---|
| PHP | ^8.3 | https://windows.php.net/download |
| Composer | 2.x | https://getcomposer.org/download |
| Node.js | 20.x+ | https://nodejs.org |
| Apache HTTP Server | 2.4.x | https://www.apachelounge.com/download/ |
| mkcert | latest | https://github.com/FiloSottile/mkcert/releases |
| Git | 2.x | https://git-scm.com/download/win |
| Windows | 10 ou 11 | — |

### Extensões PHP Necessárias

```ini
extension=openssl
extension=pdo_sqlite
extension=mbstring
extension=fileinfo
extension_dir = "ext"
```

### Caminhos Padrão

| Componente | Caminho |
|---|---|
| Binário Apache | `C:/Apache24/bin/httpd.exe` |
| Config VHosts | `C:/Apache24/conf/extra/httpd-vhosts.conf` |
| Error Log | `C:/Apache24/logs/error.log` |
| mkcert | `C:/mkcert/mkcert.exe` |
| Certificados SSL | `C:/mkcert/` |
| Arquivo Hosts | `C:/Windows/System32/drivers/etc/hosts` |
| Projetos Locais | `D:/www/` |

> Todos os caminhos são configuráveis no `.env` ou pela página de **Configurações**.

---

## 🚀 Instalação Passo a Passo

### 1. PHP

1. Baixe o PHP 8.3+ (ZIP x64 **Thread Safe**) em https://windows.php.net/download
2. Extraia para `C:\php`
3. Adicione `C:\php` ao **PATH** do Windows:
   - `Win + R` → `sysdm.cpl` → Aba **Avançado** → **Variáveis de Ambiente**
   - Em **Variáveis do sistema**, edite `Path` → adicione `C:\php`
4. Copie `C:\php\php.ini-development` para `C:\php\php.ini` e descomente as extensões acima

### 2. Composer

```bash
# Baixe o instalador em https://getcomposer.org/download
# Aponte para C:\php\php.exe durante a instalação
composer --version
```

### 3. Apache

1. Baixe Apache 2.4 em https://www.apachelounge.com/download/
2. Extraia para `C:\Apache24`
3. Edite `C:\Apache24\conf\httpd.conf`:
   - Descomente: `LoadModule rewrite_module modules/mod_rewrite.so`
   - Descomente: `LoadModule ssl_module modules/mod_ssl.so`
   - Altere `ServerName` para `localhost`
   - Adicione ao final: `Include conf/extra/httpd-vhosts.conf`
4. Instale o serviço (PowerShell **como Administrador**):
   ```powershell
   C:\Apache24\bin\httpd.exe -k install
   ```

### 4. mkcert

1. Baixe `mkcert.exe` em https://github.com/FiloSottile/mkcert/releases
2. Crie `C:\mkcert` e cole o executável lá
3. Adicione `C:\mkcert` ao PATH
4. PowerShell **como Administrador**:
   ```powershell
   mkcert -install
   ```

### 5. Clonar e Instalar

```bash
git clone https://github.com/viniciuswerneck/virtual-hosts-manager.git D:\www\localserver
cd D:\www\localserver
composer install
copy .env.example .env
php artisan key:generate
```

### 6. Configurar `.env`

Edite o `.env` com seus caminhos:

```dotenv
APP_NAME=Hosts Manager
APP_URL=http://localhost:8000
ADMIN_PASSWORD='$2y$12$FauADBOBWUmpkzKkF6X4WeR3oBHQrZzlXZWSiTI84Qxalg3ILf.6O'

APACHE_VHOSTS_FILE=C:/Apache24/conf/extra/httpd-vhosts.conf
APACHE_BIN=C:/Apache24/bin/httpd.exe
APACHE_SERVICE=Apache2.4
APACHE_ERROR_LOG=C:/Apache24/logs/error.log
HOSTS_FILE=C:/Windows/System32/drivers/etc/hosts
MKCERT_BIN=C:/mkcert/mkcert.exe
MKCERT_DIR=C:/mkcert
DEFAULT_DOCUMENT_ROOT=D:/www/
```

> Senha padrão: `VTV@fwspm2233`. Para gerar um novo hash:
> ```bash
> php -r "echo password_hash('sua-senha', PASSWORD_BCRYPT);"
> ```

### 7. Migrations + Assets

```bash
php artisan migrate
npm install --ignore-scripts
npm run build
```

### 8. Permissões

PowerShell **como Administrador**:

```batch
fix-permissions.bat
```

---

## ⚙️ Configuração

### Variáveis de Ambiente (`.env`)

| Variável | Padrão | Descrição |
|---|---|---|
| `ADMIN_PASSWORD` | *(bcrypt hash)* | Hash da senha. Vazio = sem autenticação |
| `APACHE_VHOSTS_FILE` | `C:/Apache24/conf/extra/httpd-vhosts.conf` | Arquivo de config dos vhosts |
| `APACHE_BIN` | `C:/Apache24/bin/httpd.exe` | Binário do Apache |
| `APACHE_SERVICE` | `Apache2.4` | Nome do serviço Windows |
| `APACHE_SSL_PORT` | `443` | Porta SSL |
| `APACHE_ERROR_LOG` | `C:/Apache24/logs/error.log` | Caminho do error log |
| `HOSTS_FILE` | `C:/Windows/System32/drivers/etc/hosts` | Arquivo hosts |
| `MKCERT_BIN` | `C:/mkcert/mkcert.exe` | Binário mkcert |
| `MKCERT_DIR` | `C:/mkcert` | Diretório de certificados |
| `DEFAULT_DOCUMENT_ROOT` | `D:/www/` | Raiz padrão para novos vhosts |
| `PHPMYADMIN_URL` | *(vazio)* | URL do phpMyAdmin para auto-login |
| `PHPMYADMIN_USER` | `root` | Usuário MySQL |
| `PHPMYADMIN_PASSWORD` | *(vazio)* | Senha MySQL |
| `VSCODE_EXECUTABLE` | `code` | Path do VSCode |

> Todos os caminhos também podem ser alterados pela interface em **Config** → Settings.

---

## ▶️ Como Usar

### Iniciar o Servidor

```bash
# Desenvolvimento com hot-reload
composer run dev

# Ou apenas o servidor web
php artisan serve
```

Acesse: [http://localhost:8000](http://localhost:8000)

### Login

1. Abra http://localhost:8000
2. Digite a senha: `VTV@fwspm2233`
3. Para desabilitar: deixe `ADMIN_PASSWORD=` vazio no `.env`

### Criar um Virtual Host

1. Clique em **Novo Host** no menu
2. Preencha:
   - **Nome do Servidor**: `meusite.local`
   - **Diretório Raiz**: `D:/www/meusite`
   - **Porta**: `80`
   - **PHP Version**: selecione se usar múltiplas versões
   - **SSL**: marque para HTTPS
   - **GitHub**: link do repositório (opcional)
   - **Observações**: anotações (opcional)
3. Clique em **Criar Virtual Host**
4. O sistema automaticamente:
   - ✅ Adiciona `127.0.0.1 meusite.local` no hosts
   - ✅ Gera certificado SSL via mkcert
   - ✅ Cria bloco VirtualHost no Apache
   - ✅ Reinicia o Apache

Acesse `http://meusite.local` ou `https://meusite.local`.

### Operações em Lote

1. Na listagem, marque os hosts desejados
2. Clique no botão de ação: **Ativar**, **Desativar** ou **Excluir**
3. Confirme a operação

### Backup / Restore

- **Exportar**: Config → Backup → gera ZIP com banco + configs
- **Importar**: Config → Backup → selecione o ZIP para restaurar

### Logs do Apache

1. Menu → **Logs**
2. Use os filtros: severidade, busca textual, quantidade de linhas
3. Ative **Auto-Refresh** para monitoramento em tempo real
4. Clique no ícone de cópia para copiar uma linha ou todas

---

## 🔧 Comandos Úteis

### Composer

| Comando | Descrição |
|---|---|
| `composer run setup` | Instalação completa (deps, .env, key, migrate, npm, build) |
| `composer run dev` | Servidor + queue + logs + Vite (hot-reload) |
| `composer run test` | `config:clear` + `php artisan test` |

### Artisan

| Comando | Descrição |
|---|---|
| `php artisan serve` | Inicia servidor embutido |
| `php artisan migrate` | Executa migrations |
| `php artisan queue:listen` | Processa filas |
| `php artisan config:clear` | Limpa cache de config |
| `php artisan cache:clear` | Limpa cache da app |

### Batch (Windows)

| Script | Descrição | Requer Admin? |
|---|---|---|
| `fix-permissions.bat` | Permissões de escrita nos arquivos do sistema | ✅ |
| `apply-changes.bat` | Reinicia o serviço Apache2.4 | ✅ |

---

## 🗄️ Estrutura do Projeto

```
D:\www\localserver/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── ActivityLogController.php    # Audit log
│   │   │   ├── ApacheLogController.php      # Log viewer + stream
│   │   │   ├── DashboardController.php       # Painel principal
│   │   │   ├── FileManagerController.php     # Navegador de arquivos
│   │   │   ├── SettingsController.php        # Config do sistema
│   │   │   └── VirtualHostController.php     # CRUD + batch + backup
│   │   ├── Middleware/
│   │   │   └── AdminAuth.php                 # Autenticação por sessão
│   │   └── Requests/
│   │       └── StoreVirtualHostRequest.php   # Validação
│   ├── Models/
│   │   ├── ActivityLog.php                   # Log de atividades
│   │   ├── VirtualHost.php                   # Virtual hosts
│   │   └── Setting.php                       # Config persistida
│   └── Services/
│       ├── ActivityLogService.php            # Registro de auditoria
│       ├── ApacheService.php                 # Apache (config, restart, test)
│       ├── HostsFileService.php              # Arquivo hosts
│       ├── MkcertService.php                 # Certificados SSL
│       ├── ProjectScaffoldService.php        # Scaffold de projetos
│       └── VhostManagerService.php           # Orquestrador
├── config/
│   └── virtualhosts.php                      # Config dos caminhos
├── database/
│   └── migrations/                           # Schema SQLite
├── resources/
│   └── views/
│       ├── auth/login.blade.php              # Login
│       ├── layouts/app.blade.php             # Layout principal
│       ├── dashboard/index.blade.php         # Dashboard
│       ├── logs/index.blade.php              # Visualizador de logs
│       ├── settings/index.blade.php          # Configurações
│       ├── virtual-hosts/                    # CRUD vhosts
│       ├── activity-logs/                    # Audit log
│       └── file-manager/                     # File manager
├── routes/web.php                            # Rotas
├── .github/workflows/
│   ├── tests.yml                             # CI (PHP 8.3/8.4/8.5)
│   └── dependabot.yml                        # Automerge
└── AGENTS.md                                 # Instruções para IA
```

---

## 🧪 Testes

```bash
# Todos os testes
composer run test

# Arquivo específico
php artisan test tests/Unit/ApacheServiceTest.php
php artisan test tests/Feature/VirtualHostControllerTest.php

# Método específico
php artisan test --filter test_parse_existing_returns_empty_when_file_missing

# Lint (Pint)
./vendor/bin/pint        # dry run
./vendor/bin/pint --test  # check mode
```

Os testes usam SQLite in-memory e cobrem:
- **Unitários**: ApacheService, HostsFileService, MkcertService, VhostManagerService
- **Funcionais**: VirtualHostController (CRUD, batch, toggle), SettingsController, autenticação

---

## ❓ Solução de Problemas

| Problema | Solução |
|---|---|
| **Permissão negada** | Execute `fix-permissions.bat` como Administrador |
| **Apache não reinicia** | `net stop Apache2.4 && net start Apache2.4` (Admin) |
| **Login aparecendo** | Deixe `ADMIN_PASSWORD=` vazio no `.env` e `config:clear` |
| **Erro 500 ao excluir** | Timeout de restart — o sistema exibe aviso em vez de erro |
| **Senha incorreta** | Gere novo hash com `php -r "echo password_hash('nova-senha', PASSWORD_BCRYPT);"`
| **httpd.exe não encontrado** | Ajuste `APACHE_BIN` no `.env` |
| **Logs não aparecem** | Verifique `APACHE_ERROR_LOG` no `.env` |

---

## 🛠 Tecnologias

| Tecnologia | Versão |
|---|---|
| **PHP** | ^8.3 |
| **Laravel** | 13.x |
| **Banco** | SQLite |
| **Servidor** | Apache 2.4 (Windows) |
| **Frontend** | Tailwind CSS v4, Vite 8.x |
| **Ícones** | Font Awesome 6 |
| **CI** | GitHub Actions (PHP 8.3/8.4/8.5) |
| **Testes** | PHPUnit 12.x, Mockery, Collision |

---

## 🗺 Roadmap

### Curto Prazo
- [ ] Suporte a Nginx (Linux)
- [ ] Dark mode additional refinements
- [ ] Editor de template de VirtualHost

### Médio Prazo
- [ ] API RESTful
- [ ] Multi-usuário com roles
- [ ] Docker development environment

### Longo Prazo
- [ ] Plugin system
- [ ] WebSockets para logs em tempo real
- [ ] CLI nativa (Laravel Zero)

---

## ❓ FAQ

**Precisa de Linux?** Não. O projeto é focado em Windows com Apache.

**Funciona com XAMPP/Wamp?** Sim, ajustando os caminhos no `.env`.

**Dá para usar com PHP 8.4?** Sim. O CI testa PHP 8.3, 8.4 e 8.5.

**O que é o audit log?** Histórico de todas as ações (criar, editar, excluir, ativar/desativar hosts).

**Como funciona o PHP Version Switcher?** Cada virtual host pode declarar uma versão PHP. O Apache usa handler FCGID para cada versão.

---

## 📄 Licença

Este projeto é open-source sob licença **MIT**. Desenvolvido por [Werneck Lab](https://lab.werneck.dev.br/) &copy; 2024-2026.

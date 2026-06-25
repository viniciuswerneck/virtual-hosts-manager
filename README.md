# 🖥️ Hosts Manager - Gerenciador de Virtual Hosts

Aplicação web em Laravel 13 para gerenciar **Virtual Hosts Apache**, arquivo **hosts do Windows** e certificados **SSL locais (mkcert)** — tudo por uma interface gráfica no navegador.

> Desenvolvido para desenvolvedores PHP/web no Windows que utilizam Apache e precisam criar/gerenciar múltiplos domínios locais com HTTPS de forma rápida e prática.

---

## ✨ Funcionalidades

- **CRUD de Virtual Hosts** — Crie, edite, visualize e exclua virtual hosts com domínio, pasta raiz, porta, SSL, observações e link do GitHub.
- **Gerenciamento automático do arquivo hosts** — Adiciona/remove entradas `127.0.0.1 <dominio>` no `C:\Windows\System32\drivers\etc\hosts`.
- **Certificados SSL automáticos** — Gera certificados TLS confiáveis localmente via [mkcert](https://github.com/FiloSottile/mkcert) para cada vhost com SSL habilitado.
- **Configuração Apache automática** — Escreve blocos `<VirtualHost>` corretos para as portas 80 e 443 com diretivas SSL.
- **Reinicialização do Apache** — Restarta o serviço Apache após alterações, com fallback para force-kill se necessário.
- **Validação de sintaxe** — Executa `httpd -t` antes de reiniciar para evitar erros de sintaxe.
- **Sincronização do Apache** — Importa virtual hosts já existentes no Apache para o banco de dados.
- **Regeneração de SSL** — Regere o certificado SSL de um vhost com um clique.
- **Configuração por interface web** — Altere todos os caminhos do sistema (Apache, hosts, mkcert) em tempo de execução pela página de Configurações.
- **Modo escuro** — Tema dark/light com persistência em localStorage e detecção automática da preferência do sistema.
- **Interface responsiva** — Tailwind CSS + Font Awesome, totalmente em português brasileiro.

---

## 📋 Requisitos

| Requisito | Versão |
|---|---|
| PHP | ^8.3 |
| Laravel | ^13.8 |
| Composer | 2.x |
| Node.js / npm | 20.x+ |
| Apache HTTP Server | 2.4.x (ex: `C:\Apache24`) |
| [mkcert](https://github.com/FiloSottile/mkcert) | Última versão |
| Windows | 10 ou 11 |

### Extensões PHP necessárias
`openssl`, `PDO`, `sqlite3`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`

### Caminhos padrão do sistema
- `C:/Apache24/bin/httpd.exe` — Binário do Apache
- `C:/Apache24/conf/extra/httpd-vhosts.conf` — Configuração de virtual hosts
- `C:/mkcert/mkcert.exe` — Binário do mkcert
- `C:/mkcert/` — Diretório de certificados
- `C:/Windows/System32/drivers/etc/hosts` — Arquivo hosts do Windows
- `D:/www/` — Pasta raiz padrão para novos projetos

> 💡 Todos os caminhos são configuráveis via `.env` ou pela interface web de Configurações.

---

## 🚀 Instalação

### 1. Clone o repositório

```bash
git clone https://github.com/seu-usuario/hosts-manager.git D:\www\localserver
cd D:\www\localserver
```

### 2. Instale as dependências PHP

```bash
composer install
```

### 3. Configure o ambiente

```bash
copy .env.example .env
php artisan key:generate
```

Edite o arquivo `.env` e ajuste as variáveis conforme seu ambiente, especialmente os caminhos do Apache, mkcert e hosts.

### 4. Execute as migrations

```bash
php artisan migrate
```

### 5. Instale e compile os assets frontend

```bash
npm install --ignore-scripts
npm run build
```

### 6. Configure as permissões (Windows)

Execute o **PowerShell ou Prompt como Administrador**:

```batch
fix-permissions.bat
```

Este script concede permissões de escrita no arquivo hosts, no arquivo de configuração do Apache e no diretório de certificados do mkcert.

### Setup rápido (tudo em um comando)

```bash
composer run setup
```

---

## ▶️ Executando

### Produção

```bash
php artisan serve
```

Acesse: [http://localhost:8000](http://localhost:8000)

### Desenvolvimento (com hot-reload)

```bash
composer run dev
```

Executa simultaneamente: servidor Laravel + queue worker + logs + Vite dev.

---

## 🔧 Scripts Úteis

### Composer

| Comando | Descrição |
|---|---|
| `composer run setup` | Instala dependências, cria `.env`, gera key, executa migrations, instala npm e compila assets |
| `composer run dev` | Inicia servidor de desenvolvimento com hot-reload |
| `composer run test` | Executa `php artisan config:clear` + `php artisan test` |

### Batch (Windows)

| Script | Descrição | Requer Admin? |
|---|---|---|
| `fix-permissions.bat` | Concede permissões de escrita nos arquivos do sistema | ✅ Sim |
| `apply-changes.bat` | Para e inicia o serviço Apache2.4 | ✅ Sim |

### Artisan

| Comando | Descrição |
|---|---|
| `php artisan serve` | Inicia o servidor web embutido do PHP |
| `php artisan migrate` | Executa as migrations do banco de dados |
| `php artisan config:clear` | Limpa o cache de configuração |
| `php artisan test` | Executa os testes automatizados |

---

## ⚙️ Configuração

### Variáveis de ambiente (`.env`)

| Variável | Padrão | Descrição |
|---|---|---|
| `APACHE_VHOSTS_FILE` | `C:/Apache24/conf/extra/httpd-vhosts.conf` | Caminho do arquivo de configuração de vhosts |
| `APACHE_BIN` | `C:/Apache24/bin/httpd.exe` | Caminho do binário do Apache |
| `APACHE_SERVICE` | `Apache2.4` | Nome do serviço do Windows |
| `HOSTS_FILE` | `C:/Windows/System32/drivers/etc/hosts` | Caminho do arquivo hosts |
| `MKCERT_BIN` | `C:/mkcert/mkcert.exe` | Caminho do binário do mkcert |
| `MKCERT_DIR` | `C:/mkcert` | Diretório de certificados SSL |
| `DEFAULT_DOCUMENT_ROOT` | `D:/www/` | Pasta raiz padrão para novos vhosts |

### Configuração em tempo de execução

Acesse a página **Configurações** no menu superior da aplicação para alterar todos os caminhos acima sem precisar editar o `.env`. As alterações são salvas no banco de dados e aplicadas imediatamente.

---

## 🗄️ Estrutura do Banco de Dados

O projeto utiliza **SQLite** (padrão) com as seguintes tabelas personalizadas:

- **`virtual_hosts`** — `id`, `server_name` (único), `document_root`, `ssl_enabled`, `port`, `notes`, `github_url`
- **`settings`** — `id`, `key` (único), `value`

---

## 🧪 Testes

```bash
composer run test
```

Os testes utilizam SQLite in-memory e estão em `tests/Unit/` e `tests/Feature/`.

---

## 📄 Licença

Este projeto é open-source. Desenvolvido por [Werneck Lab](https://lab.werneck.dev.br/) &copy; 2024-{{ date('Y') }}.

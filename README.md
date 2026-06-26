# 🖥️ Hosts Manager - Gerenciador de Virtual Hosts

Aplicação web em **Laravel 13** para gerenciar **Virtual Hosts Apache**, arquivo **hosts do Windows** e certificados **SSL locais (mkcert)** — tudo por uma interface gráfica no navegador.

> Desenvolvido para desenvolvedores PHP/web no Windows que utilizam Apache e precisam criar/gerenciar múltiplos domínios locais com HTTPS de forma rápida e prática.

---

## 📋 Índice

- [Requisitos](#-requisitos)
- [Passo a Passo - Instalação](#-passo-a-passo---instalação)
- [Configuração](#-configuração)
- [Como Usar](#-como-usar)
- [Scripts Úteis](#-scripts-úteis)
- [Estrutura do Projeto](#-estrutura-do-projeto)
- [Testes](#-testes)
- [Solução de Problemas](#-solução-de-problemas)

---

## 📋 Requisitos

| Requisito | Versão | Onde Baixar |
|---|---|---|
| PHP | ^8.3 | https://windows.php.net/download |
| Composer | 2.x | https://getcomposer.org/download |
| Node.js | 20.x+ | https://nodejs.org |
| Apache HTTP Server | 2.4.x | https://www.apachelounge.com/download/ |
| mkcert | Última | https://github.com/FiloSottile/mkcert/releases |
| Git | 2.x | https://git-scm.com/download/win |
| Windows | 10 ou 11 | — |

### Extensões PHP necessárias

As seguintes extensões devem estar habilitadas no `php.ini`:

```
openssl, PDO, sqlite3, mbstring, tokenizer, xml, ctype, json, fileinfo
```

### Caminhos padrão do sistema

| Componente | Caminho Padrão |
|---|---|
| Apache | `C:/Apache24/bin/httpd.exe` |
| Config Apache | `C:/Apache24/conf/extra/httpd-vhosts.conf` |
| mkcert binário | `C:/mkcert/mkcert.exe` |
| Certificados SSL | `C:/mkcert/` |
| Arquivo hosts | `C:/Windows/System32/drivers/etc/hosts` |
| Projetos locais | `D:/www/` |

> 💡 Todos os caminhos são configuráveis no `.env` ou pela página de Configurações.

---

## 🚀 Passo a Passo - Instalação

### 1. Instalar o PHP

1. Baixe o PHP 8.3+ em https://windows.php.net/download (versão ZIP x64 Thread Safe)
2. Extraia para `C:\php`
3. Adicione `C:\php` ao **PATH** do Windows:
   - Pressione `Win + R`, digite `sysdm.cpl`
   - Aba **Avançado** > **Variáveis de Ambiente**
   - Em **Variáveis do sistema**, encontre `Path`, clique **Editar**
   - Adicione `C:\php`
   - Clique **OK** em todas as janelas
4. Edite `C:\php\php.ini` (copie de `php.ini-development` se não existir):
   - Descomente (remova o `;` na frente) das linhas:
     ```
     extension=openssl
     extension=pdo_sqlite
     extension=mbstring
     extension=fileinfo
     ```
   - Descomente também:
     ```
     extension_dir = "ext"
     ```

### 2. Instalar o Composer

1. Baixe o instalador em https://getcomposer.org/download
2. Execute o instalador, apontando para `C:\php\php.exe`
3. Verifique: abra um novo terminal e digite `composer --version`

### 3. Instalar o Apache

1. Baixe o Apache 2.4 em https://www.apachelounge.com/download/
2. Extraia para `C:\Apache24`
3. Edite `C:\Apache24\conf\httpd.conf`:
   - Descomente a linha: `LoadModule rewrite_module modules/mod_rewrite.so`
   - Descomente a linha: `LoadModule ssl_module modules/mod_ssl.so` (se for usar HTTPS)
   - Altere `ServerName` para: `ServerName localhost`
   - Adicione no final do arquivo:
     ```apache
     Include conf/extra/httpd-vhosts.conf
     ```
4. Instale como serviço do Windows:
   - Abra o **PowerShell como Administrador**
   - Execute: `C:\Apache24\bin\httpd.exe -k install`

### 4. Instalar o mkcert

1. Baixe o `mkcert.exe` em https://github.com/FiloSottile/mkcert/releases
2. Crie a pasta `C:\mkcert` e cole o `mkcert.exe` lá
3. Adicione `C:\mkcert` ao **PATH** do Windows (mesmo procedimento do PHP)
4. Abra o **PowerShell como Administrador** e execute:
   ```bash
   mkcert -install
   ```
   Isso vai instalar a Autoridade Certificadora local no seu sistema.

### 5. Clonar o repositório

Abra o terminal (CMD ou PowerShell) e execute:

```bash
git clone https://github.com/viniciuswerneck/virtual-hosts-manager.git D:\www\localserver
cd D:\www\localserver
```

> Se não tiver o Git instalado, baixe de https://git-scm.com/download/win

### 6. Instalar dependências PHP

```bash
composer install
```

### 7. Configurar o ambiente

```bash
copy .env.example .env
php artisan key:generate
```

Agora edite o arquivo `.env` com um bloco de notas. As variáveis mais importantes:

```dotenv
APP_NAME=Hosts Manager
APP_URL=http://localhost:8000

ADMIN_PASSWORD='$2y$12$FauADBOBWUmpkzKkF6X4WeR3oBHQrZzlXZWSiTI84Qxalg3ILf.6O'

APACHE_VHOSTS_FILE=C:/Apache24/conf/extra/httpd-vhosts.conf
APACHE_BIN=C:/Apache24/bin/httpd.exe
APACHE_SERVICE=Apache2.4
HOSTS_FILE=C:/Windows/System32/drivers/etc/hosts
MKCERT_BIN=C:/mkcert/mkcert.exe
MKCERT_DIR=C:/mkcert
DEFAULT_DOCUMENT_ROOT=D:/www/
```

> 💡 A senha atual é `VTV@fwspm2233`. Para gerar uma nova, execute:
> ```bash
> php -r "echo password_hash('sua-senha-aqui', PASSWORD_BCRYPT);"
> ```
> Copie o hash gerado e coloque no `.env`.

### 8. Executar as migrations

```bash
php artisan migrate
```

Isso cria as tabelas no banco de dados SQLite.

### 9. Instalar dependências Node.js e compilar assets

```bash
npm install --ignore-scripts
npm run build
```

### 10. Aplicar permissões

Abra o **PowerShell ou Prompt como Administrador** e execute:

```batch
fix-permissions.bat
```

Este script concede permissões de escrita nos arquivos que o sistema precisa modificar (hosts, config do Apache, diretório de certificados).

---

## ▶️ Como Usar

### Iniciar o servidor

Toda vez que for usar, você precisa iniciar o servidor web do Laravel.

#### Método 1: Iniciar manualmente

Abra o terminal na pasta `D:\www\localserver` e execute:

```bash
php artisan serve
```

Acesse: [http://localhost:8000](http://localhost:8000)

#### Método 2: Criar um atalho .bat (recomendado)

Crie um arquivo chamado `iniciar.bat` na área de trabalho ou em qualquer lugar com o seguinte conteúdo:

```batch
@echo off
title Hosts Manager
cd /d D:\www\localserver
php artisan serve
pause
```

Clique duas vezes no arquivo para iniciar.

#### Método 3: .bat como Administrador

Algumas operações (como escrever no arquivo hosts) podem exigir permissão de Administrador. Crie um arquivo `iniciar-admin.bat`:

```batch
@echo off
title Hosts Manager (Administrador)
cd /d D:\www\localserver
php artisan serve
pause
```

Para executar como Administrador:
- Clique com o botão direito no arquivo
- Selecione **Executar como administrador**
- Confirme a janela do UAC

> 💡 **Dica:** Crie um atalho para o `iniciar.bat`, vá em Propriedades > Avançado > marque "Executar como administrador". Assim toda vez que abrir, já vai pedir permissão de admin.

#### Método 4: Desenvolvimento com hot-reload

```bash
composer run dev
```

Inicia simultaneamente: servidor Laravel + Vite (hot-reload) + monitor de logs.

### Login

1. Acesse [http://localhost:8000](http://localhost:8000)
2. Se aparecer a tela de login, digite a senha: `VTV@fwspm2233`
3. Pronto! Você está no painel principal

> Se quiser desabilitar a senha, deixe `ADMIN_PASSWORD=` vazio no `.env`.

### Criar um Virtual Host

1. Clique em **Novo Host** no menu superior
2. Preencha:
   - **Nome do Servidor**: `meusite.local` (o domínio que vai acessar)
   - **Diretório Raiz**: `D:/www/meusite` (pasta onde está o projeto)
   - **Porta**: `80` (padrão)
   - **SSL**: marque se quiser HTTPS
   - **GitHub**: link do repositório (opcional)
   - **Observações**: qualquer anotação (opcional)
3. Clique em **Criar Virtual Host**
4. Pronto! O sistema:
   - ✅ Adiciona `127.0.0.1 meusite.local` no arquivo hosts
   - ✅ Gera certificado SSL via mkcert (se SSL ativado)
   - ✅ Cria o bloco VirtualHost no Apache
   - ✅ Reinicia o Apache automaticamente

Acesse `http://meusite.local` (ou `https://meusite.local`) no navegador.

### Editar um Virtual Host

1. Na listagem, clique no ícone de **lápis** (editar) ao lado do host desejado
2. Altere os campos necessários
3. Clique em **Salvar Alterações**
4. O sistema atualiza hosts, certificado e config do Apache

### Excluir um Virtual Host

1. Na listagem, clique no ícone de **lixeira**
2. Confirme a exclusão
3. O sistema remove: entrada do hosts, certificado SSL, config do Apache

### Gerenciar Certificados SSL

- Na listagem, os ícones ✅/❌ mostram se o certificado existe
- Clique no ícone de **certificado** (⭐) para regenerar
- Use **Regenerar Certificado** na página de detalhes

### Sincronizar do Apache

Se você tem virtual hosts configurados manualmente no Apache, clique em **Sincronizar do Apache** para importá-los para o banco de dados.

### Configurações do Sistema

Acesse **Config** no menu superior para alterar caminhos do Apache, mkcert, hosts e pasta padrão — tudo sem editar o `.env`.

---

## 🔧 Scripts Úteis

### Composer

| Comando | Descrição |
|---|---|
| `composer run setup` | Instalação completa: dependências, .env, key, migrations, npm, assets |
| `composer run dev` | Inicia servidor de desenvolvimento com hot-reload |
| `composer run test` | Executa `php artisan config:clear` + `php artisan test` |

### Batch (Windows)

| Script | Descrição | Requer Admin? |
|---|---|---|
| `fix-permissions.bat` | Concede permissões de escrita nos arquivos do sistema | ✅ Sim |
| `apply-changes.bat` | Para e inicia o serviço Apache2.4 | ✅ Sim |

### Arquivos .bat personalizados

#### `iniciar.bat` — Iniciar o servidor
```batch
@echo off
title Hosts Manager
cd /d D:\www\localserver
php artisan serve
pause
```

#### `iniciar-admin.bat` — Iniciar como Administrador
```batch
@echo off
title Hosts Manager (Administrador)
cd /d D:\www\localserver
php artisan serve
pause
```

> 💡 Para o `iniciar-admin.bat` sempre abrir como Administrador: clique direito > Propriedades > Atalho > Avançado > "Executar como administrador".

#### `setup-completo.bat` — Instalação do zero
```batch
@echo off
title Instalando Hosts Manager...
cd /d D:\www\localserver
echo Instalando dependencias PHP...
call composer install
echo.
echo Configurando ambiente...
copy .env.example .env
php artisan key:generate
echo.
echo Executando migrations...
php artisan migrate
echo.
echo Instalando assets...
call npm install --ignore-scripts
call npm run build
echo.
echo Aplicando permissoes (execute como Administrador)...
echo.
echo Pronto! Execute: php artisan serve
pause
```

### Artisan

| Comando | Descrição |
|---|---|
| `php artisan serve` | Inicia o servidor web embutido do PHP |
| `php artisan migrate` | Executa as migrations do banco de dados |
| `php artisan config:clear` | Limpa o cache de configuração |
| `php artisan cache:clear` | Limpa o cache da aplicação |
| `php artisan test` | Executa os testes automatizados |

---

## ⚙️ Configuração

### Variáveis de ambiente (`.env`)

| Variável | Padrão | Descrição |
|---|---|---|
| `ADMIN_PASSWORD` | (bcrypt hash) | Hash da senha de administrador. Deixe vazio para desabilitar |
| `APACHE_VHOSTS_FILE` | `C:/Apache24/conf/extra/httpd-vhosts.conf` | Caminho do arquivo de configuração de vhosts |
| `APACHE_BIN` | `C:/Apache24/bin/httpd.exe` | Caminho do binário do Apache |
| `APACHE_SERVICE` | `Apache2.4` | Nome do serviço do Windows |
| `APACHE_SSL_PORT` | `443` | Porta para VirtualHosts SSL |
| `HOSTS_FILE` | `C:/Windows/System32/drivers/etc/hosts` | Caminho do arquivo hosts |
| `MKCERT_BIN` | `C:/mkcert/mkcert.exe` | Caminho do binário do mkcert |
| `MKCERT_DIR` | `C:/mkcert` | Diretório de certificados SSL |
| `DEFAULT_DOCUMENT_ROOT` | `D:/www/` | Pasta raiz padrão para novos vhosts |

### Configuração pela interface web

Acesse **Config** no menu superior para alterar todos os caminhos acima sem editar o `.env`. As alterações são salvas no banco de dados e aplicadas imediatamente.

### Alterar a senha

```bash
php -r "echo password_hash('minha-nova-senha', PASSWORD_BCRYPT);"
```

Copie o hash gerado e substitua no `.env`:
```dotenv
ADMIN_PASSWORD='$2y$12$hash-gerado-aqui'
```

Depois limpe o cache:
```bash
php artisan config:clear
```

---

## 🗄️ Estrutura do Projeto

```
D:\www\localserver\
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── SettingsController.php    # Configurações do sistema
│   │   │   └── VirtualHostController.php # CRUD de virtual hosts
│   │   ├── Middleware/
│   │   │   └── AdminAuth.php             # Autenticação por senha
│   │   └── Requests/
│   │       └── StoreVirtualHostRequest.php # Validação de formulário
│   ├── Models/
│   │   ├── Setting.php                   # Model de configurações
│   │   └── VirtualHost.php               # Model de virtual hosts
│   └── Services/
│       ├── ApacheService.php             # Gerencia Apache (config, restart, test)
│       ├── HostsFileService.php          # Gerencia arquivo hosts
│       ├── MkcertService.php             # Gerencia certificados SSL
│       └── VhostManagerService.php       # Orquestrador
├── resources/views/
│   ├── auth/login.blade.php              # Tela de login
│   ├── layouts/app.blade.php             # Layout principal
│   ├── settings/index.blade.php          # Página de configurações
│   └── virtual-hosts/
│       ├── index.blade.php               # Listagem de hosts
│       ├── create.blade.php              # Criar host
│       ├── edit.blade.php                # Editar host
│       └── show.blade.php                # Detalhes do host
├── routes/web.php                        # Rotas da aplicação
├── .env                                  # Configuração do ambiente
└── public/favicon.svg                    # Favicon do sistema
```

---

## 🧪 Testes

```bash
composer run test
```

Os testes utilizam SQLite in-memory e cobrem:
- Unitários: ApacheService, HostsFileService, MkcertService, VhostManagerService
- Funcionais: VirtualHostController (CRUD), SettingsController, autenticação

---

## ❓ Solução de Problemas

### "Permissão negada" ao criar/editar host

Execute o PowerShell como **Administrador** e rode:
```bash
fix-permissions.bat
```

Se o problema persistir, execute manualmente:
```bash
icacls C:\Windows\System32\drivers\etc\hosts /grant "%USERNAME%":F
icacls C:\Apache24\conf\extra\httpd-vhosts.conf /grant "%USERNAME%":F
icacls C:\mkcert /grant "%USERNAME%":F
```

### Apache não reinicia automaticamente

Pode ser necessário reiniciar manualmente como Administrador:
```bash
net stop Apache2.4 && net start Apache2.4
```

Ou pelo `apply-changes.bat`:
```bash
apply-changes.bat
```

### Tela de login aparece sem eu ter configurado senha

Isso significa que o `.env` tem um valor em `ADMIN_PASSWORD`. Para desabilitar:
1. Abra o `.env`
2. Deixe `ADMIN_PASSWORD=` vazio
3. Execute: `php artisan config:clear`

### Erro 500 ao excluir/editar host

O servidor pode estar demorando mais que 30 segundos para reiniciar o Apache. O sistema agora captura esse timeout e exibe um aviso em vez de erro. Tente novamente.

### "Senha incorreta"

Se você perdeu a senha, gere uma nova:
```bash
php -r "echo password_hash('nova-senha', PASSWORD_BCRYPT);"
```

Copie o hash e cole no `.env` em `ADMIN_PASSWORD='hash-aqui'`. Depois:
```bash
php artisan config:clear
```

### Httpd.exe não encontrado

Verifique se o Apache está instalado em `C:\Apache24` ou ajuste o caminho no `.env`:
```dotenv
APACHE_BIN=C:/caminho/para/httpd.exe
APACHE_VHOSTS_FILE=C:/caminho/para/httpd-vhosts.conf
```

---

## ✨ Funcionalidades Resumo

- **CRUD de Virtual Hosts** — Crie, edite, visualize e exclua
- **Gerenciamento automático do arquivo hosts**
- **Certificados SSL automáticos** via mkcert
- **Configuração Apache automática** (portas 80 e 443)
- **Reinicialização do Apache** com fallback
- **Validação de sintaxe** (`httpd -t`) antes de reiniciar
- **Sincronização do Apache** — importa hosts existentes
- **Regeneração de SSL** com um clique
- **Exportar/Importar** virtual hosts em JSON
- **Modo escuro** com persistência
- **Busca** com auto-complete por nome, diretório ou observações
- **Interface responsiva** em português brasileiro

---

## 📄 Licença

Este projeto é open-source. Desenvolvido por [Werneck Lab](https://lab.werneck.dev.br/) &copy; 2024-2026 com Laravel.

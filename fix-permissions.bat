@echo off
setlocal enabledelayedexpansion

echo ============================================
echo  Concedendo permissoes para o Hosts Manager
echo ============================================
echo.

set "HOSTS_FILE=C:\Windows\System32\drivers\etc\hosts"
set "APACHE_CONF=C:\Apache24\conf\extra\httpd-vhosts.conf"
set "APACHE_SERVICE=Apache2.4"
set "MKCERT_DIR=C:\mkcert"

if not "%1"=="" set "HOSTS_FILE=%1"
if not "%2"=="" set "APACHE_CONF=%2"
if not "%3"=="" set "APACHE_SERVICE=%3"
if not "%4"=="" set "MKCERT_DIR=%4"

echo Usando:
echo   Hosts:        %HOSTS_FILE%
echo   Apache conf:  %APACHE_CONF%
echo   Servico:      %APACHE_SERVICE%
echo   mkcert dir:   %MKCERT_DIR%
echo.

echo 1. Concedendo permissao de escrita no hosts file...
icacls "%HOSTS_FILE%" /grant "%USERNAME%":(M)
echo.

echo 2. Concedendo permissao de escrita no Apache config...
icacls "%APACHE_CONF%" /grant "%USERNAME%":(F)
echo.

echo 3. Concedendo permissao de escrita no diretorio de certificados...
if not exist "%MKCERT_DIR%" mkdir "%MKCERT_DIR%"
icacls "%MKCERT_DIR%" /grant "%USERNAME%":(F) /T
echo.

echo 4. Configurando servico Apache...
sc config %APACHE_SERVICE% start= auto
echo.

echo ============================================
echo  Permissoes concedidas com sucesso!
echo  Execute como Administrador apenas uma vez.
echo ============================================
echo.
echo Uso: fix-permissions.bat [hosts_file] [apache_conf] [apache_service] [mkcert_dir]
echo Ex:  fix-permissions.bat "C:\Windows\System32\drivers\etc\hosts" "C:\Apache24\conf\extra\httpd-vhosts.conf" "Apache2.4" "C:\mkcert"
pause

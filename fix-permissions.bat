@echo off
echo ============================================
echo  Concedendo permissoes para o Hosts Manager
echo ============================================
echo.

echo 1. Concedendo permissao de escrita no hosts file...
icacls "C:\Windows\System32\drivers\etc\hosts" /grant "%USERNAME%":(M)
echo.

echo 2. Concedendo permissao de escrita no Apache config...
icacls "C:\Apache24\conf\extra\httpd-vhosts.conf" /grant "%USERNAME%":(F)
echo.

echo 3. Concedendo permissao para reiniciar o Apache...
sc config Apache2.4 start= auto
sc privilege Apache2.4 enableSeServiceLogonRight
echo.

echo ============================================
echo  Permissoes concedidas com sucesso!
echo  Execute como Administrador apenas uma vez.
echo ============================================
pause

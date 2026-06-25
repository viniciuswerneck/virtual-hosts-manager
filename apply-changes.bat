@echo off
echo ============================================
echo  Aplicando alteracoes no Hosts Manager
echo ============================================
echo.
echo Este script executa as operacoes que exigem
echo privilegios de Administrador.
echo.

if exist "C:\Apache24\conf\extra\httpd-vhosts.conf" (
    echo Atualizando configuracao do Apache...
    rem A config ja foi atualizada pelo Laravel
)

echo.
echo Atualizando arquivo hosts...
rem As entradas sao gerenciadas pelo Laravel

echo.
echo Reiniciando Apache...
net stop Apache2.4
net start Apache2.4
echo.

echo ============================================
echo  Alteracoes aplicadas com sucesso!
echo ============================================
pause

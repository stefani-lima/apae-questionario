@echo off
chcp 65001 >nul
title Questionario APAE - servidor local
cd /d "%~dp0"

call "%~dp0docker\achar-php.bat"
if errorlevel 1 (
  pause
  exit /b 1
)

if not exist ".env" (
  copy ".env.example" ".env" >nul
  echo Criei o arquivo .env. Coloque a senha do PostgreSQL em DB_PASSWORD, salve, feche o Bloco de Notas
  echo e rode o iniciar.bat de novo.
  notepad ".env"
  exit /b 0
)

php scripts\migrate.php
if errorlevel 1 (
  pause
  exit /b 1
)

echo.
echo Sistema no ar:
echo   Questionario ......... http://localhost:8080
echo   Area da equipe ....... http://localhost:8080/admin/
echo Para encerrar, feche esta janela.
echo.
start "" "http://localhost:8080"
php -S localhost:8080 -t public

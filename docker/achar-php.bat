@echo off
rem Procura o PHP: primeiro no PATH, depois nas pastas comuns do XAMPP e do PHP avulso.
where php >nul 2>nul
if not errorlevel 1 goto :verificar
for %%D in ("C:\xampp\php" "D:\xampp\php" "C:\php" "%ProgramFiles%\php") do (
  if exist "%%~D\php.exe" (
    set "PATH=%%~D;%PATH%"
    echo PHP encontrado em %%~D
    goto :verificar
  )
)
echo [ERRO] O PHP nao foi encontrado.
echo Se o XAMPP estiver em outra pasta, adicione a pasta "php" dele ao PATH do Windows.
exit /b 1

:verificar
php -m | findstr /i "pdo_pgsql" >nul
if errorlevel 1 (
  echo.
  echo [ERRO] A extensao do PostgreSQL esta desativada no PHP.
  for /f "tokens=2* delims=:" %%A in ('php --ini ^| findstr /c:"Loaded Configuration File"') do echo Abra este arquivo: %%A:%%B
  echo Procure as linhas abaixo e apague o ";" do comeco de cada uma, depois salve:
  echo     ;extension=pdo_pgsql
  echo     ;extension=pgsql
  exit /b 1
)
exit /b 0

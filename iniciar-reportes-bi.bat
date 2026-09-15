@echo off
setlocal enabledelayedexpansion
chcp 65001 >nul
cd /d "%~dp0"
title Portafolio de Reportes BI - entorno local

echo.
echo ===========================================================
echo   PORTAFOLIO DE REPORTES BI - entorno local
echo ===========================================================
echo.

rem ---------- 1. PHP ----------
where php >nul 2>&1
if errorlevel 1 (
  echo [X] No encuentro PHP.
  echo.
  echo     Instala Laragon Full desde https://laragon.org/download/
  echo     y vuelve a abrir esta ventana. Laragon deja php en el PATH.
  goto :fin
)

php -r "exit(version_compare(PHP_VERSION,'8.3','ge') ? 0 : 1);"
if errorlevel 1 (
  echo [X] Tu PHP es demasiado antiguo. Se necesita 8.3 o superior.
  php -v
  echo.
  echo     En Laragon: clic derecho en la bandeja - PHP - Version.
  goto :fin
)

for /f "delims=" %%v in ('php -r "echo PHP_VERSION;"') do set PHPVER=%%v
echo [OK] PHP !PHPVER!

rem ---------- 2. Extensiones ----------
rem Ojo: con delayedexpansion cmd se traga el signo de negacion suelto,
rem por eso la condicion se escribe como ===false y no negando la funcion.
php -r "$f=[];foreach(['pdo_sqlite','sqlite3','mbstring','fileinfo','openssl','ctype','tokenizer','dom'] as $e){if(extension_loaded($e)===false){$f[]=$e;}} if(count($f)>0){echo implode(', ',$f); exit(1);} exit(0);" > "%TEMP%\pcbi_ext.txt" 2>&1
if errorlevel 1 (
  echo [X] Faltan extensiones de PHP:
  type "%TEMP%\pcbi_ext.txt"
  echo.
  echo     Abre tu php.ini y quita el punto y coma del inicio de cada
  echo     linea extension=... que aparezca arriba. En Laragon:
  echo     clic derecho en la bandeja - PHP - php.ini
  del "%TEMP%\pcbi_ext.txt" >nul 2>&1
  goto :fin
)
del "%TEMP%\pcbi_ext.txt" >nul 2>&1
echo [OK] Extensiones de PHP completas

rem ---------- 3. Composer ----------
where composer >nul 2>&1
if errorlevel 1 (
  echo [X] No encuentro Composer.
  echo     Instalalo desde https://getcomposer.org/Composer-Setup.exe
  goto :fin
)
echo [OK] Composer

rem ---------- 4. Dependencias ----------
if not exist "vendor\autoload.php" (
  echo.
  echo [..] Instalando dependencias PHP. La primera vez tarda unos minutos.
  call composer install --no-interaction --no-progress
  if errorlevel 1 (
    echo [X] Fallo composer install. Revisa el mensaje de arriba.
    goto :fin
  )
) else (
  echo [OK] Dependencias ya instaladas
)

rem ---------- 5. Configuracion ----------
if not exist ".env" (
  php preparar-local.php
  if errorlevel 1 goto :fin
  php artisan key:generate --no-interaction --quiet
  echo [OK] Archivo .env creado
) else (
  echo [OK] Archivo .env ya existe
)

rem ---------- 6. Base de datos ----------
if not exist "database\database.sqlite" (
  type nul > "database\database.sqlite"
  echo.
  echo [..] Creando la base y cargando los datos de ejemplo.
  php artisan migrate --seed --force --no-interaction
  if errorlevel 1 (
    echo [X] Fallo la carga de la base. Revisa el mensaje de arriba.
    goto :fin
  )
) else (
  echo [OK] Base de datos ya creada
  php artisan migrate --force --no-interaction --quiet
)

rem ---------- 7. Arrancar ----------
echo.
echo ===========================================================
echo   Listo. Abriendo http://localhost:8010
echo.
echo   Administrador:  amoscoso@plasticoscarmen.com  /  password
echo   Usuario RRHH:   rrhh.prueba@plasticoscarmen.com  /  password
echo.
echo   (puerto 8010 para no chocar con el portafolio en el 8000)
echo   Para cerrar el servidor: Ctrl+C en esta ventana.
echo ===========================================================
echo.

start "" http://localhost:8010
php artisan serve --host=127.0.0.1 --port=8010
goto :salir

:fin
echo.
echo ===========================================================
echo   No se pudo arrancar. Corrige lo de arriba y volve a
echo   ejecutar este archivo.
echo ===========================================================
echo.
pause

:salir
endlocal

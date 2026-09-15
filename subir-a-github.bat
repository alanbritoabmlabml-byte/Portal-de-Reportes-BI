@echo off
setlocal
chcp 65001 >nul
cd /d "%~dp0"
set "LOG=%~dp0subida.log"
echo ===== %DATE% %TIME% ===== > "%LOG%"
title Portafolio de Reportes BI - subir a GitHub

echo.
echo ===========================================================
echo   SUBIR EL PROYECTO A GITHUB
echo ===========================================================
echo.

rem Si el zip se descomprimio dentro de otra carpeta, el proyecto real (el
rem que tiene artisan) esta un nivel mas abajo. El .git de esta carpeta de
rem afuera es un repositorio equivocado: se elimina para que no se use.
if not exist "artisan" if exist "portafolio-reportes-bi\artisan" (
  if exist ".git" (
    rmdir /s /q ".git"
    echo [OK] Repositorio equivocado de la carpeta de afuera eliminado
    echo eliminado .git de afuera >> "%LOG%"
  )
  cd /d "%~dp0portafolio-reportes-bi"
)
if not exist "artisan" (
  echo [X] Aqui no esta el proyecto: no encuentro artisan en %CD%
  echo sin artisan en %CD% >> "%LOG%"
  goto :fin
)
echo [OK] Proyecto: %CD%
echo proyecto: %CD% >> "%LOG%"

where git >nul 2>&1
if errorlevel 1 (
  echo [X] No encuentro Git. Instalalo desde https://git-scm.com/download/win
  echo sin git >> "%LOG%"
  goto :fin
)
git --version >> "%LOG%" 2>&1

rem La credencial de Git en Windows (OAuth) no puede crear workflows de
rem GitHub Actions: la carpeta .github se agrega despues desde la web.
if exist ".github" (
  git rm -r -q --cached .github >nul 2>&1
  rmdir /s /q ".github"
  echo [OK] Carpeta .github apartada
)

if not exist ".git" (
  git init >> "%LOG%" 2>&1
  echo [OK] Repositorio iniciado
)
git checkout -q -B main >> "%LOG%" 2>&1

git add -A >> "%LOG%" 2>&1
git rev-parse --verify HEAD >nul 2>&1
if errorlevel 1 (
  git commit -q -m "Portafolio de Reportes BI - version base" >> "%LOG%" 2>&1 || goto :error
  echo [OK] Commit creado
) else (
  git commit -q --amend --no-edit >> "%LOG%" 2>&1 || goto :error
  echo [OK] Commit actualizado
)

echo --- estado --- >> "%LOG%"
echo raiz: >> "%LOG%"
git rev-parse --show-toplevel >> "%LOG%" 2>&1
git log -1 --stat --oneline >> "%LOG%" 2>&1
git ls-files | find /c /v "" >> "%LOG%"

set "REMOTO=https://github.com/alanbritoabmlabml-byte/Portal-de-Reportes-BI.git"
git remote remove origin >nul 2>&1
git remote add origin "%REMOTO%"

echo.
echo [..] Subiendo a %REMOTO%
echo     Si Git te pide iniciar sesion, hazlo en la ventana del navegador que se abre.
echo --- push --- >> "%LOG%"
git push -u --force origin main >> "%LOG%" 2>&1
if errorlevel 1 (
  type "%LOG%"
  goto :error
)

echo.
echo ===========================================================
echo   Listo. El codigo esta en GitHub:
echo   %REMOTO%
echo ===========================================================
echo push ok >> "%LOG%"
echo.
pause
goto :salir

:error
echo.
echo [X] Algo fallo. El detalle quedo en subida.log; avisale a Claude.
echo error >> "%LOG%"

:fin
echo.
pause

:salir
endlocal

@echo off
REM Script para iniciar Tailwind CSS en modo watch
REM Este archivo debe ejecutarse desde la raíz del proyecto

echo ========================================
echo Iniciando Tailwind CSS Compiler...
echo ========================================

REM Verificar si npm está instalado
where npm >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: npm no está instalado o no está en PATH
    exit /b 1
)

REM Instalar dependencias si no existen
if not exist "node_modules" (
    echo Instalando dependencias...
    call npm install
)

REM Iniciar PostCSS con Tailwind en modo watch
echo.
echo Iniciando compilador en modo watch...
echo Presiona Ctrl+C para detener
echo.
echo Monitoreando cambios en: css/input.css
echo Salida: css/css/output.css
echo.

call npm run watch:css

pause

#!/bin/bash
# Script para iniciar Tailwind CSS en modo watch (Linux/Mac)

echo "========================================"
echo "Iniciando Tailwind CSS Compiler..."
echo "========================================"

# Verificar si npm está instalado
if ! command -v npm &> /dev/null; then
    echo "ERROR: npm no está instalado"
    exit 1
fi

# Instalar dependencias si no existen
if [ ! -d "node_modules" ]; then
    echo "Instalando dependencias..."
    npm install
fi

# Iniciar PostCSS con Tailwind en modo watch
echo ""
echo "Iniciando compilador en modo watch..."
echo "Presiona Ctrl+C para detener"
echo ""
echo "Monitoreando cambios en: css/input.css"
echo "Salida: css/css/output.css"
echo ""

npm run watch:css

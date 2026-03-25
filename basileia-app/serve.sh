#!/bin/bash
# Script para rodar o Laravel Serve com reinício automático.
# Isso evita que o servidor morra com erro ECONNREFUSED ao editar arquivos PHP.

echo "Iniciando servidor de desenvolvimento Antigravity (com auto-restart)..."

while true; do
    php artisan serve --port=8000 --host=127.0.0.1
    echo "Servidor parado ou encerrado com erro. Reiniciando em 1 segundo..."
    sleep 1
done

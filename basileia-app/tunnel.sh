#!/bin/bash
# Script para manter o localtunnel vivo
echo "Iniciando localtunnel para o subdomínio twelve-suns-act na porta 8000..."

while true; do
    npx localtunnel --port 8000 --subdomain twelve-suns-act
    echo "Localtunnel caiu. Reiniciando em 5 segundos..."
    sleep 5
done

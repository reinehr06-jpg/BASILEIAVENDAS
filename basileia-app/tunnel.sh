#!/bin/bash
# Script para manter o Cloudflare Tunnel vivo
echo "Iniciando Cloudflare Tunnel para a porta 8000..."

while true; do
    cloudflared tunnel --url http://localhost:8000
    echo "Cloudflare Tunnel caiu. Reiniciando em 5 segundos..."
    sleep 5
done

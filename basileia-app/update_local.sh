#!/bin/bash
# Script para atualizar o ambiente local (migrations e cache)
echo "🚀 Atualizando ambiente local Basileia Vendas..."

php artisan migrate --force
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "✅ Ambiente local atualizado!"

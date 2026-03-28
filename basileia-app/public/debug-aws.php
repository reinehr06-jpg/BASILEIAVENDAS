<?php

/**
 * Script de Diagnóstico Rápido para Basileia Vendas na AWS
 * Este script verifica as dependências do Laravel e reporta erros de boot.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Diagnóstico Basileia Vendas (AWS)</h1>";
echo "<pre>";

// 1. Verificar Versão do PHP
echo "PHP Version: " . PHP_VERSION . " (Mínimo: 8.2 sugerido para Laravel 11/13)\n";
if (version_compare(PHP_VERSION, '8.2', '<')) {
    echo "❌ AVISO: Versão do PHP é inferior a 8.2\n";
} else {
    echo "✅ PHP Version OK\n";
}

// 2. Verificar Extensões Críticas
$extensions = ['bcmath', 'ctype', 'curl', 'dom', 'fileinfo', 'filter', 'hash', 'mbstring', 'openssl', 'pcre', 'pdo', 'session', 'tokenizer', 'xml', 'gd'];
foreach ($extensions as $ext) {
    if (!extension_loaded($ext)) {
        echo "❌ Extensão FALTANDO: $ext\n";
    } else {
        echo "✅ Extensão OK: $ext\n";
    }
}

// 3. Verificar Permissões de Pastas
$paths = [
    '../storage' => 0775,
    '../storage/logs' => 0775,
    '../storage/framework' => 0775,
    '../bootstrap/cache' => 0775,
];

foreach ($paths as $path => $perm) {
    if (!is_writable($path)) {
        echo "❌ NÃO GRAVÁVEL: $path (Verifique permissões chmod)\n";
    } else {
        echo "✅ GRAVÁVEL: $path\n";
    }
}

// 4. Verificar arquivo .env
if (!file_exists('../.env')) {
    echo "❌ ARQUIVO .env NÃO ENCONTRADO na raiz do projeto!\n";
} else {
    echo "✅ .env encontrado\n";
    $env = parse_ini_file('../.env');
    if (empty($env['APP_KEY'])) {
        echo "❌ APP_KEY está vazia no .env! Rode 'php artisan key:generate'\n";
    } else {
        echo "✅ APP_KEY configurada\n";
    }
}

// 5. Tentar carregar o Autoload do Composer
if (!file_exists('../vendor/autoload.php')) {
    echo "❌ vendor/autoload.php NÃO ENCONTRADO! Rode 'composer install' no servidor.\n";
} else {
    echo "✅ Autoload do Composer OK\n";
}

echo "\n--- FIM DO DIAGNÓSTICO ---\n";
echo "</pre>";

<?php

/**
 * Script de Diagnóstico Rápido RESILIENTE para Basileia Vendas na AWS
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Diagnóstico Basileia Vendas (AWS)</h1>";
echo "<h3>Se você vê esta mensagem, o PHP está funcionando corretamente.</h3><pre>";

// 1. PHP Info
echo "PHP Version: " . PHP_VERSION . "\n";
echo "OS: " . PHP_OS . "\n";
echo "User: " . get_current_user() . "\n";
echo "Doc Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n\n";

// 2. Extensões
$extensions = ['bcmath', 'ctype', 'curl', 'dom', 'fileinfo', 'filter', 'hash', 'mbstring', 'openssl', 'pcre', 'pdo', 'session', 'tokenizer', 'xml', 'gd'];
foreach ($extensions as $ext) {
    echo (extension_loaded($ext) ? "✅" : "❌") . " Extensão: $ext\n";
}

// 3. Pastas (Sem usar chmods complexos)
$paths = [
    '../storage' => 'storage',
    '../storage/logs' => 'logs',
    '../bootstrap/cache' => 'cache',
    '../vendor' => 'vendor',
    '../.env' => '.env'
];

foreach ($paths as $path => $label) {
    if (file_exists($path)) {
        echo "✅ Encontrado: $label (" . (is_writable($path) ? "GRAVÁVEL" : "NÃO-GRAVÁVEL") . ")\n";
    } else {
        echo "❌ FALTANDO: $label (Caminho testado: " . realpath($path) . ")\n";
    }
}

// 4. Teste de boot básico do Laravel (Apenas se o vendor existir)
if (file_exists('../vendor/autoload.php')) {
    try {
        require '../vendor/autoload.php';
        echo "✅ Autoload do Composer carregado com sucesso!\n";
    } catch (\Throwable $e) {
        echo "❌ ERRO ao carregar o Autoload: " . $e->getMessage() . "\n";
    }
}

echo "\n--- FIM DO DIAGNÓSTICO ---\n";
echo "</pre>";

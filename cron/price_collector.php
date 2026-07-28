<?php
// Configura ambiente CLI para ignorar limites de tempo e exibir mensagens
set_time_limit(0);
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Define fuso horário padrão do projeto
date_default_timezone_set('America/Sao_Paulo');

echo "=== INICIANDO COLETA AUTOMÁTICA DE PREÇOS ===\n";
echo "Hora de início: " . date('d/m/Y H:i:s') . "\n\n";

// Autoloader PSR-4 Simplificado
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = dirname(__DIR__) . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Carrega configurações
require_once dirname(__DIR__) . '/config/database.php';

try {
    $scraper = new \App\Services\ScraperService();
    echo "Executando raspagem de preços nos supermercados ativos...\n";
    
    $stats = $scraper->collectAll();
    
    echo "\n=== RESULTADO DA COLETA ===\n";
    echo "Mercados processados: " . $stats['markets_processed'] . "\n";
    echo "Preços novos/atualizados: " . $stats['prices_updated'] . "\n";
    
    if (!empty($stats['errors'])) {
        echo "\nFalhas encontradas:\n";
        foreach ($stats['errors'] as $error) {
            echo " - " . $error . "\n";
        }
    }
    
    echo "\nColeta concluída com sucesso às: " . date('d/m/Y H:i:s') . "\n";

} catch (\Exception $e) {
    echo "ERRO FATAL DURANTE A COLETA: " . $e->getMessage() . "\n";
}

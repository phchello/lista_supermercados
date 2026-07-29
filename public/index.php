<?php
// Exibe erros para desenvolvimento
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define fuso horário padrão do projeto
date_default_timezone_set('America/Sao_Paulo');

// Autoloader PSR-4 Simplificado
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = dirname(__DIR__) . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return; // não pertence ao namespace App
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Importa banco de dados para garantir inicialização
require_once dirname(__DIR__) . '/config/database.php';

// Roteamento Flexível (Suporta URLs amigáveis ou parâmetro query ?route=)
$route = $_GET['route'] ?? '';

if (empty($route)) {
    // Tenta obter pela URL path
    $path = $_SERVER['PATH_INFO'] ?? '';
    if (empty($path)) {
        $requestUri = $_SERVER['REQUEST_URI'];
        $scriptName = $_SERVER['SCRIPT_NAME'];
        // Remove pasta de script se a URL amigável for usada
        $path = str_replace(dirname($scriptName), '', $requestUri);
        $path = parse_url($path, PHP_URL_PATH);
    }
    $route = trim($path, '/');
}

// Rota padrão é o Dashboard
if (empty($route)) {
    $route = 'dashboard';
}

// Mapeamento de Rotas para Controllers
$routes = [
    'dashboard' => ['App\Controllers\DashboardController', 'index'],
    'dashboard/sync' => ['App\Controllers\DashboardController', 'sync'],
    'products' => ['App\Controllers\ProductController', 'index'],
    'products/detail' => ['App\Controllers\ProductController', 'detail'],
    'products/save' => ['App\Controllers\ProductController', 'save'],
    'products/delete' => ['App\Controllers\ProductController', 'delete'],
    'markets' => ['App\Controllers\MarketController', 'index'],
    'markets/save' => ['App\Controllers\MarketController', 'save'],
    'markets/toggle' => ['App\Controllers\MarketController', 'toggle'],
    'lists' => ['App\Controllers\ShoppingListController', 'index'],
    'lists/detail' => ['App\Controllers\ShoppingListController', 'detail'],
    'lists/save' => ['App\Controllers\ShoppingListController', 'save'],
    'lists/delete' => ['App\Controllers\ShoppingListController', 'delete'],
    'lists/add-item' => ['App\Controllers\ShoppingListController', 'addItem'],
    'lists/remove-item' => ['App\Controllers\ShoppingListController', 'removeItem'],
    'lists/save-purchase' => ['App\Controllers\ShoppingListController', 'savePurchase'],
    'lists/history' => ['App\Controllers\ShoppingListController', 'history'],
    'lists/history-detail' => ['App\Controllers\ShoppingListController', 'historyDetail'],
    'receipt/upload' => ['App\Controllers\ReceiptController', 'index'],
    'receipt/process' => ['App\Controllers\ReceiptController', 'process'],
    
    // Rotas de API
    'api/products' => ['App\Controllers\ApiController', 'searchProducts'],
    'api/price-history' => ['App\Controllers\ApiController', 'priceHistory'],
    'api/process-ocr' => ['App\Controllers\ApiController', 'processOcrText'],
    'api/upload-xml' => ['App\Controllers\ApiController', 'uploadXml'],
    'api/lists/items' => ['App\Controllers\ApiController', 'getListItemsAjax'],
    'api/lists/add-item' => ['App\Controllers\ApiController', 'addListItemAjax'],
    'api/lists/remove-item' => ['App\Controllers\ApiController', 'removeListItemAjax'],
    'api/lists/update-quantity' => ['App\Controllers\ApiController', 'updateListItemQuantityAjax'],
];

// Despacha para o Controller correspondente
if (array_key_exists($route, $routes)) {
    list($controllerClass, $method) = $routes[$route];
    
    if (class_exists($controllerClass)) {
        $controller = new $controllerClass();
        if (method_exists($controller, $method)) {
            $controller->$method();
            exit;
        }
    }
}

// Rota não encontrada (404)
header("HTTP/1.0 404 Not Found");
echo "<h1>404 - Página Não Encontrada</h1>";
echo "<p>A rota '" . htmlspecialchars($route) . "' não foi mapeada no sistema.</p>";

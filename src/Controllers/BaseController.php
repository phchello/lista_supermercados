<?php
namespace App\Controllers;

class BaseController {
    /**
     * Renderiza uma view envolta em header e footer layouts
     */
    protected function render($viewName, $data = []) {
        // Torna as chaves do array associativo disponíveis como variáveis na View
        extract($data);
        
        $viewFile = dirname(__DIR__) . '/Views/' . $viewName . '.php';
        
        if (file_exists($viewFile)) {
            require dirname(__DIR__) . '/Views/layouts/header.php';
            require $viewFile;
            require dirname(__DIR__) . '/Views/layouts/footer.php';
        } else {
            header("HTTP/1.0 500 Internal Server Error");
            die("View '" . htmlspecialchars($viewName) . "' não encontrada em " . htmlspecialchars($viewFile));
        }
    }

    /**
     * Retorna dados como JSON para API REST
     */
    protected function json($data, $statusCode = 200) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Redireciona para uma rota do sistema
     */
    protected function redirect($route) {
        // Redireciona usando a URL raiz com ?route=
        $baseUrl = dirname($_SERVER['SCRIPT_NAME']);
        // Corrige barra invertida no Windows
        $baseUrl = str_replace('\\', '/', $baseUrl);
        $baseUrl = rtrim($baseUrl, '/');
        
        header("Location: " . $baseUrl . "/?route=" . $route);
        exit;
    }
}

<?php
namespace App\Controllers;

use App\Repositories\ProductRepository;
use App\Repositories\PriceHistoryRepository;
use App\Repositories\MarketRepository;
use App\Repositories\ShoppingListRepository;
use App\Services\OcrService;
use App\Services\NormalizationService;
use App\Services\OptimizationService;

class ApiController extends BaseController {
    private $productRepo;
    private $priceRepo;
    private $marketRepo;
    private $listRepo;
    private $ocrService;
    private $normalizationService;
    private $optimizationService;

    public function __construct() {
        $this->productRepo = new ProductRepository();
        $this->priceRepo = new PriceHistoryRepository();
        $this->marketRepo = new MarketRepository();
        $this->listRepo = new ShoppingListRepository();
        $this->ocrService = new OcrService();
        $this->normalizationService = new NormalizationService();
        $this->optimizationService = new OptimizationService();
    }

    /**
     * Busca produtos por termo para o autocomplete (JSON)
     */
    public function searchProducts() {
        $q = $_GET['q'] ?? '';
        $products = $this->productRepo->all(['search' => $q]);
        
        $result = [];
        foreach ($products as $p) {
            $result[] = [
                'id' => $p['id'],
                'name' => $p['name'],
                'ean' => $p['ean'] ?? '',
                'brand' => $p['brand_name'] ?? '',
                'category' => $p['category_name'] ?? ''
            ];
        }

        $this->json($result);
    }

    /**
     * Retorna o histórico de preços de um produto formatado para o Chart.js
     */
    public function priceHistory() {
        $productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $days = isset($_GET['days']) ? (int)$_GET['days'] : 30;

        if ($productId <= 0) {
            $this->json(['error' => 'ID do produto inválido.'], 400);
        }

        $rawHistory = $this->productRepo->getPriceHistory($productId, $days);
        
        // Estrutura dados para plotar múltiplas linhas (uma para cada mercado)
        $datasets = [];
        $labels = [];
        
        foreach ($rawHistory as $row) {
            $date = date('d/m/Y H:i', strtotime($row['collected_at']));
            $market = $row['market_name'];
            $price = floatval($row['price']);
            
            if (!in_array($date, $labels)) {
                $labels[] = $date;
            }
            
            if (!isset($datasets[$market])) {
                $datasets[$market] = [
                    'label' => $market,
                    'data' => [],
                    'fill' => false,
                    'tension' => 0.1
                ];
            }
            
            $datasets[$market]['data'][$date] = $price;
        }

        // Garante que todos os datasets tenham dados em todas as datas (colocando null onde não há coleta)
        $formattedDatasets = [];
        $colors = [
            '#0d6efd', '#198754', '#dc3545', '#ffc107', '#0dcaf0', 
            '#6610f2', '#fd7e14', '#20c997', '#6f42c1', '#d63384'
        ];
        $colorIndex = 0;

        foreach ($datasets as $marketName => $dataObj) {
            $dataPoints = [];
            foreach ($labels as $label) {
                $dataPoints[] = $dataObj['data'][$label] ?? null;
            }
            
            $color = $colors[$colorIndex % count($colors)];
            $colorIndex++;
            
            $formattedDatasets[] = [
                'label' => $marketName,
                'data' => $dataPoints,
                'borderColor' => $color,
                'backgroundColor' => $color,
                'fill' => false,
                'spanGaps' => true,
                'tension' => 0.2
            ];
        }

        $this->json([
            'labels' => $labels,
            'datasets' => $formattedDatasets
        ]);
    }

    /**
     * Processa texto bruto OCR e retorna produtos estruturados (JSON)
     */
    public function processOcrText() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Método inválido.'], 405);
        }

        // Lê input JSON ou POST comum
        $input = json_decode(file_get_contents('php://output'), true);
        if (!$input) {
            $input = $_POST;
        }

        $text = $input['text'] ?? '';
        
        if (empty($text)) {
            $this->json(['error' => 'Texto OCR vazio.'], 400);
        }

        $items = $this->ocrService->parseOcrText($text);
        
        $this->json([
            'success' => true,
            'items' => $items
        ]);
    }

    /**
     * Confirma a importação dos produtos processados via OCR e salva no BD
     */
    public function uploadXml() {
        // Rota API para salvar itens importados
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Método não permitido.'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || empty($input['items']) || empty($input['market_id'])) {
            $this->json(['error' => 'Dados incompletos para importação.'], 400);
        }

        $marketId = (int)$input['market_id'];
        $date = !empty($input['date']) ? $input['date'] . ' ' . date('H:i:s') : date('Y-m-d H:i:s');
        $items = $input['items'];
        
        $savedCount = 0;

        foreach ($items as $item) {
            $name = trim($item['name']);
            $unitPrice = floatval($item['unit_price']);
            $productId = isset($item['product_id']) ? (int)$item['product_id'] : 0;

            if (empty($name) || $unitPrice <= 0) continue;

            // Se for produto novo, cadastra
            if ($productId <= 0) {
                // Tenta achar similar novamente por segurança
                $similar = $this->normalizationService->findSimilarProduct($name);
                if ($similar) {
                    $productId = $similar['id'];
                } else {
                    $brandId = $this->normalizationService->detectBrand($name);
                    $productId = $this->productRepo->save([
                        'name' => $name,
                        'normalized_name' => $this->normalizationService->cleanString($name),
                        'brand_id' => $brandId,
                        'category_id' => null
                    ]);
                }
            }

            // Grava o preço no histórico
            $this->priceRepo->savePrice($productId, $marketId, $unitPrice, 0, 0.00, $date);
            $savedCount++;
        }

        $this->json([
            'success' => true,
            'message' => "Importação concluída. {$savedCount} preços registrados com sucesso."
        ]);
    }

    /**
     * Retorna itens da lista e seu comparativo de preços via AJAX
     */
    public function getListItemsAjax() {
        $listId = isset($_GET['list_id']) ? (int)$_GET['list_id'] : 0;
        if ($listId <= 0) {
            $this->json(['success' => false, 'error' => 'Lista inválida.'], 400);
        }

        $opt = $this->optimizationService->optimizeList($listId);
        $this->json(['success' => true, 'optimization' => $opt]);
    }

    /**
     * Adiciona um item na lista de compras via AJAX
     */
    public function addListItemAjax() {
        $input = json_decode(file_get_contents('php://input'), true);
        $listId = isset($input['list_id']) ? (int)$input['list_id'] : 0;
        $productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;
        $quantity = isset($input['quantity']) ? floatval($input['quantity']) : 1.0;
        $observation = isset($input['observation']) ? trim($input['observation']) : '';

        if ($listId <= 0 || $productId <= 0 || $quantity <= 0) {
            $this->json(['success' => false, 'error' => 'Dados inválidos.'], 400);
        }

        $this->listRepo->addItem($listId, $productId, $quantity, $observation);
        $opt = $this->optimizationService->optimizeList($listId);
        
        $this->json(['success' => true, 'optimization' => $opt]);
    }

    /**
     * Remove um item da lista de compras via AJAX
     */
    public function removeListItemAjax() {
        $input = json_decode(file_get_contents('php://input'), true);
        $listId = isset($input['list_id']) ? (int)$input['list_id'] : 0;
        $productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;

        if ($listId <= 0 || $productId <= 0) {
            $this->json(['success' => false, 'error' => 'Dados inválidos.'], 400);
        }

        $this->listRepo->removeItem($listId, $productId);
        $opt = $this->optimizationService->optimizeList($listId);
        
        $this->json(['success' => true, 'optimization' => $opt]);
    }

    /**
     * Atualiza a quantidade absoluta de um item via AJAX
     */
    public function updateListItemQuantityAjax() {
        $input = json_decode(file_get_contents('php://input'), true);
        $listId = isset($input['list_id']) ? (int)$input['list_id'] : 0;
        $productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;
        $quantity = isset($input['quantity']) ? floatval($input['quantity']) : 1.0;

        if ($listId <= 0 || $productId <= 0 || $quantity <= 0) {
            $this->json(['success' => false, 'error' => 'Dados inválidos.'], 400);
        }

        $this->listRepo->updateItemQuantity($listId, $productId, $quantity);
        $opt = $this->optimizationService->optimizeList($listId);
        
        $this->json(['success' => true, 'optimization' => $opt]);
    }
}

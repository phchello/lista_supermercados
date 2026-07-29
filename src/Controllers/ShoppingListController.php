<?php
namespace App\Controllers;

use App\Repositories\ShoppingListRepository;
use App\Repositories\ProductRepository;
use App\Repositories\MarketRepository;
use App\Services\OptimizationService;
use App\Services\ExportService;
use App\Services\NormalizationService;

class ShoppingListController extends BaseController {
    private $listRepo;
    private $productRepo;
    private $marketRepo;
    private $optimizationService;
    private $exportService;

    public function __construct() {
        $this->listRepo = new ShoppingListRepository();
        $this->productRepo = new ProductRepository();
        $this->marketRepo = new MarketRepository();
        $this->optimizationService = new OptimizationService();
        $this->exportService = new ExportService();
    }

    public function index() {
        $lists = $this->listRepo->all();
        $this->render('shopping_lists', [
            'title' => 'Listas de Compras',
            'lists' => $lists
        ]);
    }

    public function detail() {
        $id = $_GET['id'] ?? 0;
        $list = $this->listRepo->findById($id);

        if (!$list) {
            $this->redirect('lists');
        }

        // Obtém otimização e comparação
        $optimization = $this->optimizationService->optimizeList($id);
        $allProducts = $this->productRepo->all();
        $markets = $this->marketRepo->all(true);
        $brands = $this->productRepo->getBrands();

        // Agrupa os produtos fisicamente sem marcas na interface
        $normalizationService = new NormalizationService();
        $genericProducts = [];
        foreach ($allProducts as $p) {
            $baseDesc = $normalizationService->getBaseDescription($p['name']);
            $volObj = $normalizationService->extractVolume($p['name']);
            $volStr = $volObj ? $volObj['normalized'] : '';
            
            $genericName = ucwords($baseDesc);
            if ($volStr) {
                $genericName .= ' ' . strtoupper($volStr);
            }
            
            $key = md5(strtolower($genericName));
            if (!isset($genericProducts[$key])) {
                $genericProducts[$key] = [
                    'id' => $p['id'],
                    'name' => $genericName,
                    'category_name' => $p['category_name'],
                    'brands' => $p['brand_name'] ? [$p['brand_name']] : [],
                    'product_ids' => [$p['id']]
                ];
            } else {
                if ($p['brand_name'] && !in_array($p['brand_name'], $genericProducts[$key]['brands'])) {
                    $genericProducts[$key]['brands'][] = $p['brand_name'];
                }
                $genericProducts[$key]['product_ids'][] = $p['id'];
            }
        }
        $genericProducts = array_values($genericProducts);

        // Exportação de Relatórios
        if (isset($_GET['export'])) {
            $format = $_GET['export'];
            $headers = ['Produto', 'Quantidade', 'Observação', 'Mercado Mais Barato', 'Preço Unitário (R$)', 'Total (R$)'];
            
            $exportData = [];
            foreach ($optimization['split_comparison']['items'] as $item) {
                $exportData[] = [
                    $item['product_name'],
                    $item['quantity'],
                    $item['observation'] ?? '',
                    $item['market_name'],
                    number_format($item['unit_price'], 2, ',', '.'),
                    number_format($item['total_price'], 2, ',', '.')
                ];
            }
            
            // Adiciona resumo
            $exportData[] = ['', '', '', '', '', ''];
            $exportData[] = ['Custo Otimizado Total:', '', '', '', '', 'R$ ' . number_format($optimization['split_comparison']['total_cost'], 2, ',', '.')];
            $exportData[] = ['Economia Prevista:', '', '', '', '', 'R$ ' . number_format($optimization['split_comparison']['potential_savings_vs_cheapest'], 2, ',', '.')];

            $filename = 'Lista_' . str_replace(' ', '_', $list['name']);
            if ($format === 'csv') {
                $this->exportService->exportCsv($filename, $headers, $exportData);
            } elseif ($format === 'xls') {
                $this->exportService->exportExcel($filename, $headers, $exportData);
            }
        }

        $this->render('shopping_list_detail', [
            'title' => 'Lista: ' . $list['name'],
            'list' => $list,
            'optimization' => $optimization,
            'allProducts' => $genericProducts, // Passa agrupados por genéricos!
            'brands' => $brands, // Passa marcas
            'markets' => $markets
        ]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
            $name = trim($_POST['name'] ?? '');

            if (!empty($name)) {
                $this->listRepo->save($name, $id);
            }
        }
        $this->redirect('lists');
    }

    public function delete() {
        $id = $_GET['id'] ?? 0;
        if ($id > 0) {
            $this->listRepo->delete($id);
        }
        $this->redirect('lists');
    }

    public function addItem() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $listId = (int)($_POST['list_id'] ?? 0);
            $productId = (int)($_POST['product_id'] ?? 0);
            $quantity = floatval($_POST['quantity'] ?? 1);
            $observation = trim($_POST['observation'] ?? '');

            if ($listId > 0 && $productId > 0 && $quantity > 0) {
                $this->listRepo->addItem($listId, $productId, $quantity, $observation);
            }
            $this->redirect('lists/detail&id=' . $listId);
        }
        $this->redirect('lists');
    }

    public function removeItem() {
        $listId = (int)($_GET['list_id'] ?? 0);
        $productId = (int)($_GET['product_id'] ?? 0);

        if ($listId > 0 && $productId > 0) {
            $this->listRepo->removeItem($listId, $productId);
        }
        $this->redirect('lists/detail&id=' . $listId);
    }

    public function savePurchase() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $listId = (int)($_POST['list_id'] ?? 0);
            $marketId = (int)($_POST['market_id'] ?? 0);
            $totalValue = floatval($_POST['total_value'] ?? 0);
            $savings = floatval($_POST['savings'] ?? 0);
            $purchaseDate = $_POST['purchase_date'] ?? date('Y-m-d');

            if ($listId > 0 && $marketId > 0 && $totalValue > 0) {
                // Monta o snapshot em JSON dos itens
                $items = $this->listRepo->getItems($listId);
                $itemsJson = json_encode($items, JSON_UNESCAPED_UNICODE);

                $this->listRepo->savePurchase($listId, $purchaseDate, $totalValue, $marketId, $savings, $itemsJson);
            }
            $this->redirect('lists/history');
        }
        $this->redirect('lists');
    }

    public function history() {
        $purchases = $this->listRepo->getPurchaseHistory();
        $this->render('purchase_history', [
            'title' => 'Histórico de Compras Realizadas',
            'purchases' => $purchases
        ]);
    }

    public function historyDetail() {
        $id = $_GET['id'] ?? 0;
        $purchase = $this->listRepo->getPurchaseHistoryDetail($id);

        if (!$purchase) {
            $this->redirect('lists/history');
        }

        $items = json_decode($purchase['items_json'], true);

        $this->render('purchase_history_detail', [
            'title' => 'Detalhes da Compra #' . $purchase['id'],
            'purchase' => $purchase,
            'items' => $items
        ]);
    }
}

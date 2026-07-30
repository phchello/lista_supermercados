<?php
namespace App\Controllers;

use App\Repositories\ProductRepository;
use App\Services\NormalizationService;

class ProductController extends BaseController {
    private $productRepo;
    private $normalizationService;

    public function __construct() {
        $this->productRepo = new ProductRepository();
        $this->normalizationService = new NormalizationService();
    }

    public function index() {
        $filters = [
            'search' => $_GET['search'] ?? '',
            'brand_id' => $_GET['brand_id'] ?? '',
            'category_id' => $_GET['category_id'] ?? ''
        ];

        $products = $this->productRepo->all($filters);
        $brands = $this->productRepo->getBrands();
        $categories = $this->productRepo->getCategories();

        $this->render('products', [
            'title' => 'Produtos Cadastrados',
            'products' => $products,
            'brands' => $brands,
            'categories' => $categories,
            'filters' => $filters
        ]);
    }

    public function detail() {
        $id = $_GET['id'] ?? 0;
        $product = $this->productRepo->findById($id);

        if (!$product) {
            $this->redirect('products');
        }

        $latestPrices = $this->productRepo->getLatestPricesForProduct($id);
        $priceHistory = $this->productRepo->getPriceHistory($id);

        // Enriquece cada preço com a URL de pesquisa dinâmica do mercado
        $marketRepo = new \App\Repositories\MarketRepository();
        foreach ($latestPrices as &$lp) {
            $lp['search_url'] = $marketRepo->getSearchUrl($lp['market_id'], $product['name']);
        }
        unset($lp);

        $this->render('product_details', [
            'title' => 'Histórico de ' . $product['name'],
            'product' => $product,
            'latestPrices' => $latestPrices,
            'priceHistory' => $priceHistory
        ]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $name = trim($_POST['name'] ?? '');
            $ean = trim($_POST['ean'] ?? '');
            $brandName = trim($_POST['brand_name'] ?? '');
            $categoryName = trim($_POST['category_name'] ?? '');

            if (empty($name)) {
                $this->redirect('products');
            }

            // Descobre ou cria IDs de marcas/categorias se digitadas em texto
            $brandId = null;
            if (!empty($brandName)) {
                $brandId = $this->productRepo->findOrCreateBrand($brandName);
            }

            $categoryId = null;
            if (!empty($categoryName)) {
                $categoryId = $this->productRepo->findOrCreateCategory($categoryName);
            }

            $normalizedName = $this->normalizationService->cleanString($name);

            $data = [
                'name' => $name,
                'normalized_name' => $normalizedName,
                'ean' => $ean ?: null,
                'brand_id' => $brandId,
                'category_id' => $categoryId
            ];

            if ($id > 0) {
                $data['id'] = $id;
            }

            $this->productRepo->save($data);
        }

        $this->redirect('products');
    }

    public function delete() {
        $id = $_GET['id'] ?? 0;
        if ($id > 0) {
            $this->productRepo->delete($id);
        }
        $this->redirect('products');
    }
}

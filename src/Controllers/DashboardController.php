<?php
namespace App\Controllers;

use App\Repositories\ProductRepository;
use App\Repositories\PriceHistoryRepository;
use App\Repositories\MarketRepository;
use App\Services\ScraperService;

class DashboardController extends BaseController {
    private $productRepo;
    private $priceRepo;
    private $marketRepo;

    public function __construct() {
        $this->productRepo = new ProductRepository();
        $this->priceRepo = new PriceHistoryRepository();
        $this->marketRepo = new MarketRepository();
    }

    public function index() {
        // Dispara uma simulação leve de scraping se o banco de dados estiver vazio para popular o dashboard
        $stats = $this->productRepo->getGlobalStats();
        if ($stats['updates_today'] == 0 && $stats['total_products'] > 0) {
            $scraper = new ScraperService();
            $scraper->collectAll();
            $stats = $this->productRepo->getGlobalStats(); // Atualiza estatísticas
        }

        $promotions = $this->priceRepo->getLatestPromotions(6);
        $recentUpdates = $this->priceRepo->getLatestUpdates(5);
        $markets = $this->marketRepo->getStats();
        
        // Dados para os gráficos do Dashboard
        $bestDays = $this->priceRepo->getBestDayToShop();
        $priceOscillation = $this->priceRepo->getPriceOscillation();

        $this->render('dashboard', [
            'title' => 'Dashboard de Preços',
            'stats' => $stats,
            'promotions' => $promotions,
            'recentUpdates' => $recentUpdates,
            'markets' => $markets,
            'bestDays' => $bestDays,
            'priceOscillation' => $priceOscillation
        ]);
    }
}

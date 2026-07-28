<?php
namespace App\Services;

use App\Repositories\ShoppingListRepository;
use App\Repositories\MarketRepository;

class OptimizationService {
    private $listRepository;
    private $marketRepository;

    public function __construct() {
        $this->listRepository = new ShoppingListRepository();
        $this->marketRepository = new MarketRepository();
    }

    public function optimizeList($listId) {
        $list = $this->listRepository->findById($listId);
        if (!$list) return null;

        $items = $this->listRepository->getItems($listId);
        $prices = $this->listRepository->getPricesForListProducts($listId);
        $markets = $this->marketRepository->all(true); // Apenas ativos

        // Mapeia preços em matriz indexada [product_id][market_id]
        $priceMatrix = [];
        $avgPrices = []; // Armazena média do preço de cada produto para fallback se necessário
        
        foreach ($prices as $p) {
            $priceMatrix[$p['product_id']][$p['market_id']] = [
                'price' => floatval($p['price']),
                'is_promotion' => $p['is_promotion'],
                'discount_percentage' => $p['discount_percentage']
            ];
            
            if (!isset($avgPrices[$p['product_id']])) {
                $avgPrices[$p['product_id']] = [];
            }
            $avgPrices[$p['product_id']][] = floatval($p['price']);
        }

        // Calcula a média real para cada produto
        foreach ($avgPrices as $prodId => $vals) {
            $avgPrices[$prodId] = count($vals) > 0 ? array_sum($vals) / count($vals) : 0;
        }

        // --- 1. COMPARAÇÃO POR MERCADO ÚNICO (Comprar tudo em um só lugar) ---
        $singleMarketSummary = [];
        
        foreach ($markets as $market) {
            $marketId = $market['id'];
            $totalCost = 0.0;
            $itemsFound = 0;
            $itemsMissing = 0;
            $itemsDetail = [];

            foreach ($items as $item) {
                $prodId = $item['product_id'];
                $qty = floatval($item['quantity']);
                
                if (isset($priceMatrix[$prodId][$marketId])) {
                    $itemPrice = $priceMatrix[$prodId][$marketId]['price'];
                    $itemCost = $qty * $itemPrice;
                    $totalCost += $itemCost;
                    $itemsFound++;
                    
                    $itemsDetail[] = [
                        'product_id' => $prodId,
                        'product_name' => $item['product_name'],
                        'quantity' => $qty,
                        'unit_price' => $itemPrice,
                        'total_price' => $itemCost,
                        'is_promotion' => $priceMatrix[$prodId][$marketId]['is_promotion'],
                        'discount_percentage' => $priceMatrix[$prodId][$marketId]['discount_percentage'],
                        'estimated' => false
                    ];
                } else {
                    // Produto sem preço nesse mercado. Para não inviabilizar a comparação,
                    // estimamos pelo preço médio do produto nos outros mercados.
                    $estimatedPrice = $avgPrices[$prodId] ?? 0.0;
                    $itemCost = $qty * $estimatedPrice;
                    $totalCost += $itemCost;
                    $itemsMissing++;
                    
                    $itemsDetail[] = [
                        'product_id' => $prodId,
                        'product_name' => $item['product_name'],
                        'quantity' => $qty,
                        'unit_price' => $estimatedPrice,
                        'total_price' => $itemCost,
                        'is_promotion' => 0,
                        'discount_percentage' => 0,
                        'estimated' => true // Marca que é um preço estimado
                    ];
                }
            }

            $singleMarketSummary[] = [
                'market_id' => $marketId,
                'market_name' => $market['name'],
                'logo_url' => $market['logo_url'],
                'total_cost' => $totalCost,
                'items_found' => $itemsFound,
                'items_missing' => $itemsMissing,
                'items_detail' => $itemsDetail
            ];
        }

        // Ordena mercados do mais barato para o mais caro
        usort($singleMarketSummary, function($a, $b) {
            if ($a['items_missing'] !== $b['items_missing']) {
                return $a['items_missing'] <=> $b['items_missing']; // Menos itens faltantes primeiro
            }
            return $a['total_cost'] <=> $b['total_cost'];
        });

        // --- 2. COMPARAÇÃO DIVIDIDA (Melhor preço de cada item) ---
        $splitItems = [];
        $splitTotalCost = 0.0;
        $splitByMarket = []; // Agrupado por mercado para o roteiro

        foreach ($items as $item) {
            $prodId = $item['product_id'];
            $qty = floatval($item['quantity']);
            
            $cheapestPrice = null;
            $cheapestMarketId = null;
            $cheapestMarketName = 'Indefinido';
            $cheapestLogo = '';
            $isPromo = 0;
            $disc = 0.0;

            // Encontra qual mercado tem o menor preço para este produto
            if (isset($priceMatrix[$prodId])) {
                foreach ($priceMatrix[$prodId] as $mId => $pData) {
                    if ($cheapestPrice === null || $pData['price'] < $cheapestPrice) {
                        $cheapestPrice = $pData['price'];
                        $cheapestMarketId = $mId;
                        $isPromo = $pData['is_promotion'];
                        $disc = $pData['discount_percentage'];
                    }
                }
            }

            // Encontra nome do mercado mais barato
            if ($cheapestMarketId) {
                foreach ($markets as $m) {
                    if ($m['id'] == $cheapestMarketId) {
                        $cheapestMarketName = $m['name'];
                        $cheapestLogo = $m['logo_url'];
                        break;
                    }
                }
            }

            $unitPrice = $cheapestPrice !== null ? $cheapestPrice : ($avgPrices[$prodId] ?? 0.0);
            $totalPrice = $qty * $unitPrice;
            $splitTotalCost += $totalPrice;

            $itemDetail = [
                'product_id' => $prodId,
                'product_name' => $item['product_name'],
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'market_id' => $cheapestMarketId,
                'market_name' => $cheapestMarketName,
                'market_logo' => $cheapestLogo,
                'is_promotion' => $isPromo,
                'discount_percentage' => $disc,
                'estimated' => ($cheapestPrice === null)
            ];

            $splitItems[] = $itemDetail;

            // Agrupa no roteiro por mercado
            if ($cheapestMarketId) {
                if (!isset($splitByMarket[$cheapestMarketId])) {
                    $splitByMarket[$cheapestMarketId] = [
                        'market_name' => $cheapestMarketName,
                        'market_logo' => $cheapestLogo,
                        'total_cost' => 0.0,
                        'items' => []
                    ];
                }
                $splitByMarket[$cheapestMarketId]['items'][] = $itemDetail;
                $splitByMarket[$cheapestMarketId]['total_cost'] += $totalPrice;
            }
        }

        // Calcula economia
        $cheapestSingleMarketCost = !empty($singleMarketSummary) ? $singleMarketSummary[0]['total_cost'] : 0.0;
        $mostExpensiveSingleMarketCost = !empty($singleMarketSummary) ? end($singleMarketSummary)['total_cost'] : 0.0;
        
        $potentialSavings = $cheapestSingleMarketCost - $splitTotalCost;
        $maxSavings = $mostExpensiveSingleMarketCost - $splitTotalCost;

        return [
            'list_name' => $list['name'],
            'list_id' => $list['id'],
            'total_items' => count($items),
            'single_market_comparison' => $singleMarketSummary,
            'split_comparison' => [
                'total_cost' => $splitTotalCost,
                'potential_savings_vs_cheapest' => max(0, $potentialSavings),
                'max_savings_vs_expensive' => max(0, $maxSavings),
                'items' => $splitItems,
                'by_market' => $splitByMarket
            ]
        ];
    }
}

<?php
namespace App\Services;

use App\Repositories\ShoppingListRepository;
use App\Repositories\MarketRepository;
use App\Repositories\ProductRepository;

class OptimizationService {
    private $listRepository;
    private $marketRepository;
    private $productRepository;
    private $normalizationService;

    public function __construct() {
        $this->listRepository = new ShoppingListRepository();
        $this->marketRepository = new MarketRepository();
        $this->productRepository = new ProductRepository();
        $this->normalizationService = new NormalizationService();
    }

    public function optimizeList($listId) {
        $list = $this->listRepository->findById($listId);
        if (!$list) return null;

        $items = $this->listRepository->getItems($listId);
        
        // Carrega todos os produtos ativos e suas marcas
        $allProducts = $this->productRepository->all();
        
        // Mapeia marcas e suas preferências (1 a 5)
        $brands = $this->productRepository->getBrands();
        $brandPreferences = [];
        foreach ($brands as $b) {
            $brandPreferences[$b['id']] = isset($b['preference']) ? intval($b['preference']) : 3;
        }

        // Agrupa os produtos da base por nome sem marca (base description) e volume físico
        $genericMap = [];
        foreach ($allProducts as $p) {
            $baseDesc = $this->normalizationService->getBaseDescription($p['name']);
            $volObj = $this->normalizationService->extractVolume($p['name']);
            $volStr = $volObj ? $volObj['normalized'] : 'geral';
            
            $genericMap[$baseDesc][$volStr][] = $p;
        }

        // Carrega todos os preços mais recentes coletados
        $rawPrices = $this->productRepository->getAllLatestPrices();
        
        // Mapeia preços em matriz indexada [product_id][market_id]
        $priceMatrix = [];
        $avgPrices = []; // Armazena média do preço de cada produto para fallback se necessário
        
        foreach ($rawPrices as $p) {
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
            $avgPrices[$prodId] = count($vals) > 0 ? array_sum($vals) / count($vals) : 0.0;
        }

        $markets = $this->marketRepository->all(true); // Apenas ativos

        // --- 1. COMPARAÇÃO POR MERCADO ÚNICO (Comprar tudo em um só lugar) ---
        $singleMarketSummary = [];
        
        foreach ($markets as $market) {
            $marketId = $market['id'];
            $totalCost = 0.0;
            $itemsFound = 0;
            $itemsMissing = 0;
            $itemsDetail = [];

            foreach ($items as $item) {
                $qty = floatval($item['quantity']);
                
                // Obtém alternativas de marcas do produto correspondente
                $origProduct = $this->productRepository->findById($item['product_id']);
                $baseDesc = $this->normalizationService->getBaseDescription($item['product_name']);
                $volObj = $this->normalizationService->extractVolume($item['product_name']);
                $volStr = $volObj ? $volObj['normalized'] : 'geral';
                
                $candidates = $genericMap[$baseDesc][$volStr] ?? [$origProduct];
                
                // Encontra a melhor opção de marca com base no menor preço percebido (Ponderado por gosto da marca)
                $bestProductOption = null;
                $bestPerceivedPrice = null;
                $bestActualPrice = null;
                
                foreach ($candidates as $cand) {
                    $candId = $cand['id'];
                    
                    if (isset($priceMatrix[$candId][$marketId])) {
                        $actualPrice = $priceMatrix[$candId][$marketId]['price'];
                        
                        // Fator de Ajuste de Preferência:
                        // Nota 5: 20% desconto no preço percebido
                        // Nota 4: 10% desconto
                        // Nota 3: Preço original (neutro)
                        // Nota 2: 10% de acréscimo
                        // Nota 1: 20% de acréscimo
                        $prefScore = $brandPreferences[$cand['brand_id']] ?? 3;
                        $perceivedPrice = $actualPrice * (1 + (3 - $prefScore) * 0.10);
                        
                        if ($bestPerceivedPrice === null || $perceivedPrice < $bestPerceivedPrice) {
                            $bestPerceivedPrice = $perceivedPrice;
                            $bestActualPrice = $actualPrice;
                            $bestProductOption = $cand;
                        }
                    }
                }

                // Se encontrou alguma marca alternativa disponível com preço neste mercado
                if ($bestProductOption !== null) {
                    $prodId = $bestProductOption['id'];
                    $itemCost = $qty * $bestActualPrice;
                    $totalCost += $itemCost;
                    $itemsFound++;
                    
                    $itemsDetail[] = [
                        'product_id' => $prodId,
                        'product_name' => $bestProductOption['name'], // Mostra o nome da marca escolhida
                        'brand_name' => $bestProductOption['brand_name'] ?? 'Sem Marca',
                        'preference_score' => $brandPreferences[$bestProductOption['brand_id']] ?? 3,
                        'quantity' => $qty,
                        'unit_price' => $bestActualPrice,
                        'total_price' => $itemCost,
                        'is_promotion' => $priceMatrix[$prodId][$marketId]['is_promotion'],
                        'discount_percentage' => $priceMatrix[$prodId][$marketId]['discount_percentage'],
                        'estimated' => false
                    ];
                } else {
                    // Produto sem preço. Estima usando a média de preços da marca original
                    $origId = $item['product_id'];
                    $estimatedPrice = $avgPrices[$origId] ?? 0.0;
                    $itemCost = $qty * $estimatedPrice;
                    $totalCost += $itemCost;
                    $itemsMissing++;
                    
                    $itemsDetail[] = [
                        'product_id' => $origId,
                        'product_name' => $item['product_name'],
                        'brand_name' => $item['brand_name'] ?? 'Sem Marca',
                        'preference_score' => $brandPreferences[$origProduct['brand_id'] ?? 0] ?? 3,
                        'quantity' => $qty,
                        'unit_price' => $estimatedPrice,
                        'total_price' => $itemCost,
                        'is_promotion' => 0,
                        'discount_percentage' => 0,
                        'estimated' => true
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

        // --- 2. COMPARAÇÃO DIVIDIDA (Melhor preço e marca para cada item) ---
        $splitItems = [];
        $splitTotalCost = 0.0;
        $splitByMarket = []; // Agrupado por mercado para o roteiro

        foreach ($items as $item) {
            $qty = floatval($item['quantity']);
            
            $origProduct = $this->productRepository->findById($item['product_id']);
            $baseDesc = $this->normalizationService->getBaseDescription($item['product_name']);
            $volObj = $this->normalizationService->extractVolume($item['product_name']);
            $volStr = $volObj ? $volObj['normalized'] : 'geral';
            
            // Alternativas de marca
            $candidates = $genericMap[$baseDesc][$volStr] ?? [$origProduct];
            
            $bestCandidate = null;
            $bestMarketId = null;
            $bestMarketName = 'Indefinido';
            $bestLogo = '';
            $bestPerceivedPrice = null;
            $bestActualPrice = null;
            $isPromo = 0;
            $disc = 0.0;

            // Varre candidatos a substituto em todos os mercados ativos
            foreach ($candidates as $cand) {
                $candId = $cand['id'];
                
                if (isset($priceMatrix[$candId])) {
                    foreach ($priceMatrix[$candId] as $mId => $pData) {
                        $actualPrice = $pData['price'];
                        $prefScore = $brandPreferences[$cand['brand_id']] ?? 3;
                        $perceivedPrice = $actualPrice * (1 + (3 - $prefScore) * 0.10);
                        
                        if ($bestPerceivedPrice === null || $perceivedPrice < $bestPerceivedPrice) {
                            $bestPerceivedPrice = $perceivedPrice;
                            $bestActualPrice = $actualPrice;
                            $bestCandidate = $cand;
                            $bestMarketId = $mId;
                            $isPromo = $pData['is_promotion'];
                            $disc = $pData['discount_percentage'];
                        }
                    }
                }
            }

            // Encontra nome do mercado correspondente
            if ($bestMarketId) {
                foreach ($markets as $m) {
                    if ($m['id'] == $bestMarketId) {
                        $bestMarketName = $m['name'];
                        $bestLogo = $m['logo_url'];
                        break;
                    }
                }
            }

            $unitPrice = $bestActualPrice !== null ? $bestActualPrice : ($avgPrices[$item['product_id']] ?? 0.0);
            $totalPrice = $qty * $unitPrice;
            $splitTotalCost += $totalPrice;

            $itemDetail = [
                'product_id' => $bestCandidate ? $bestCandidate['id'] : $item['product_id'],
                'product_name' => $bestCandidate ? $bestCandidate['name'] : $item['product_name'],
                'brand_name' => $bestCandidate ? ($bestCandidate['brand_name'] ?? 'Sem Marca') : ($item['brand_name'] ?? 'Sem Marca'),
                'preference_score' => $bestCandidate ? ($brandPreferences[$bestCandidate['brand_id']] ?? 3) : 3,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'market_id' => $bestMarketId,
                'market_name' => $bestMarketName,
                'market_logo' => $bestLogo,
                'is_promotion' => $isPromo,
                'discount_percentage' => $disc,
                'estimated' => ($bestActualPrice === null)
            ];

            $splitItems[] = $itemDetail;

            // Agrupa no roteiro por mercado
            if ($bestMarketId) {
                if (!isset($splitByMarket[$bestMarketId])) {
                    $splitByMarket[$bestMarketId] = [
                        'market_name' => $bestMarketName,
                        'market_logo' => $bestLogo,
                        'total_cost' => 0.0,
                        'items' => []
                    ];
                }
                $splitByMarket[$bestMarketId]['items'][] = $itemDetail;
                $splitByMarket[$bestMarketId]['total_cost'] += $totalPrice;
            }
        }

        // Calcula economias
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

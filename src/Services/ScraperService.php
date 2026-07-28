<?php
namespace App\Services;

use App\Repositories\MarketRepository;
use App\Repositories\ProductRepository;
use App\Repositories\PriceHistoryRepository;

interface MarketScraperInterface {
    public function scrapePrices($marketUrl);
}

class ScraperService {
    private $marketRepository;
    private $productRepository;
    private $priceRepository;

    public function __construct() {
        $this->marketRepository = new MarketRepository();
        $this->productRepository = new ProductRepository();
        $this->priceRepository = new PriceHistoryRepository();
    }

    /**
     * Executa a rotina de coleta automática para todos os mercados cadastrados
     */
    public function collectAll() {
        $markets = $this->marketRepository->all(true); // Apenas ativos
        $stats = [
            'markets_processed' => 0,
            'prices_updated' => 0,
            'errors' => []
        ];

        foreach ($markets as $market) {
            try {
                // Tenta simular ou executar raspagem real se houver endpoint
                $updatedCount = $this->simulateScraping($market['id']);
                $stats['prices_updated'] += $updatedCount;
                $stats['markets_processed']++;
            } catch (\Exception $e) {
                $stats['errors'][] = $market['name'] . ': ' . $e->getMessage();
            }
        }

        return $stats;
    }

    /**
     * Realiza uma simulação estatística inteligente de variação de preços
     * Simula flutuações realistas de mercado, promoções e novas coletas
     */
    public function simulateScraping($marketId) {
        $products = $this->productRepository->all();
        $updatedCount = 0;

        foreach ($products as $product) {
            // Nem todo produto é atualizado em toda coleta (simula que alguns estão indisponíveis ou sem alteração)
            if (rand(1, 100) > 90) {
                continue; // 10% de chance de pular
            }

            // Obtém último preço desse produto neste mercado
            $latestPrices = $this->productRepository->getLatestPricesForProduct($product['id']);
            $currentPrice = null;
            
            foreach ($latestPrices as $lp) {
                if ($lp['market_id'] == $marketId && $lp['price'] !== null) {
                    $currentPrice = floatval($lp['price']);
                    break;
                }
            }

            // Se nunca teve preço cadastrado, gera um preço base fictício dependendo do nome
            if ($currentPrice === null) {
                $currentPrice = $this->generateBasePrice($product['name']);
            }

            // Define variação (-10% a +10%)
            $variancePercent = rand(-100, 100) / 1000; // ex: -0.1 a 0.1 (-10% a +10%)
            $newPrice = $currentPrice * (1 + $variancePercent);
            
            // Mantém duas casas decimais
            $newPrice = round($newPrice, 2);

            // Simula promoção (15% de chance de entrar em oferta)
            $isPromotion = 0;
            $discountPercentage = 0.00;
            if (rand(1, 100) <= 15) {
                $isPromotion = 1;
                $discountPercentage = rand(5, 25); // 5% a 25% de desconto
                // Preço promocional
                $newPrice = $newPrice * (1 - ($discountPercentage / 100));
                $newPrice = round($newPrice, 2);
            }

            // Salva no banco de dados
            $this->priceRepository->savePrice(
                $product['id'],
                $marketId,
                $newPrice,
                $isPromotion,
                $discountPercentage
            );

            $updatedCount++;
        }

        return $updatedCount;
    }

    /**
     * Auxiliar para gerar preços iniciais realistas com base no nome do produto
     */
    private function generateBasePrice($productName) {
        $name = strtolower($productName);
        if (strpos($name, 'leite') !== false) {
            return rand(400, 580) / 100; // R$ 4.00 - R$ 5.80
        } elseif (strpos($name, 'arroz') !== false) {
            return rand(2200, 3100) / 100; // R$ 22.00 - R$ 31.00
        } elseif (strpos($name, 'feijao') !== false) {
            return rand(700, 950) / 100; // R$ 7.00 - R$ 9.50
        } elseif (strpos($name, 'omo') !== false || strpos($name, 'lava roupas') !== false) {
            return rand(3200, 4500) / 100; // R$ 32.00 - R$ 45.00
        } elseif (strpos($name, 'ype') !== false || strpos($name, 'detergente') !== false) {
            return rand(180, 260) / 100; // R$ 1.80 - R$ 2.60
        } elseif (strpos($name, 'coca') !== false) {
            return rand(800, 1100) / 100; // R$ 8.00 - R$ 11.00
        } elseif (strpos($name, 'alcatra') !== false || strpos($name, 'carne') !== false) {
            return rand(3500, 5500) / 100; // R$ 35.00 - R$ 55.00
        }
        return rand(500, 2500) / 100; // R$ 5.00 - R$ 25.00 padrão
    }

    /**
     * Protótipo de Scraper real de páginas HTML usando cURL
     * (Pode ser estendido configurando seletores específicos)
     */
    public function scrapeRealHtml($marketUrl, $selectors = []) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $marketUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $html = curl_exec($ch);
        curl_close($ch);

        if (!$html) {
            return false;
        }

        // Exemplo simples de parsing usando DOMDocument / DOMXPath
        $doc = new \DOMDocument();
        @$doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new \DOMXPath($doc);

        $parsedProducts = [];

        // Exemplo hipotético de seletores
        if (isset($selectors['product_box'])) {
            $nodes = $xpath->query($selectors['product_box']);
            foreach ($nodes as $node) {
                // Executa novas querys relativas ao box para nome e preço
                $name = '';
                $price = 0.00;
                
                if (isset($selectors['name'])) {
                    $name = trim($xpath->query($selectors['name'], $node)->item(0)->nodeValue ?? '');
                }
                if (isset($selectors['price'])) {
                    $priceStr = trim($xpath->query($selectors['price'], $node)->item(0)->nodeValue ?? '0');
                    // Limpa string do preço para converter em float
                    $priceStr = preg_replace('/[^\d,\.]/', '', $priceStr);
                    $price = floatval(str_replace(',', '.', $priceStr));
                }

                if (!empty($name) && $price > 0) {
                    $parsedProducts[] = [
                        'name' => $name,
                        'price' => $price
                    ];
                }
            }
        }

        return $parsedProducts;
    }
}

<?php
namespace App\Services;

use App\Repositories\ProductRepository;
use App\Repositories\MarketRepository;
use App\Repositories\PriceHistoryRepository;
use SimpleXMLElement;
use Exception;

class OcrService {
    private $productRepository;
    private $marketRepository;
    private $priceRepository;
    private $normalizationService;

    public function __construct() {
        $this->productRepository = new ProductRepository();
        $this->marketRepository = new MarketRepository();
        $this->priceRepository = new PriceHistoryRepository();
        $this->normalizationService = new NormalizationService();
    }

    /**
     * Faz o parse de arquivo XML NFC-e
     * Retorna array com dados da compra estruturados
     */
    public function parseNfceXml($xmlContent) {
        try {
            // Remove namespaces para facilitar o parsing via SimpleXML
            $xmlContent = preg_replace('/xmlns="[^"]+"/', '', $xmlContent);
            $xmlContent = preg_replace('/xmlns:[^=]+="[^"]+"/', '', $xmlContent);
            
            $xml = new SimpleXMLElement($xmlContent);
            
            // 1. Dados do Emitente (Mercado)
            $emit = $xml->xpath('//emit');
            if (empty($emit)) {
                throw new Exception("Emitente não encontrado no XML.");
            }
            $marketName = (string)$emit[0]->xNome;
            $cnpj = (string)$emit[0]->CNPJ;
            
            // Busca ou cria o mercado
            $market = $this->marketRepository->findByName($marketName);
            if (!$market) {
                $marketId = $this->marketRepository->save([
                    'name' => $marketName,
                    'website_url' => 'CNPJ: ' . $cnpj,
                    'active' => 1
                ]);
            } else {
                $marketId = $market['id'];
            }

            // 2. Data de Emissão
            $ide = $xml->xpath('//ide');
            $dhEmi = isset($ide[0]->dhEmi) ? (string)$ide[0]->dhEmi : date('Y-m-d H:i:s');
            // Formata para data MySQL (YYYY-MM-DD HH:MM:SS)
            $purchaseDate = date('Y-m-d H:i:s', strtotime($dhEmi));

            // 3. Itens do Cupom
            $dets = $xml->xpath('//det');
            $parsedItems = [];
            
            foreach ($dets as $det) {
                $prodNode = $det->prod;
                $ean = (string)$prodNode->cEAN;
                // EAN de balança ou sem código de barras válido
                if ($ean === 'SEM GTIN' || strlen($ean) < 8) {
                    $ean = null;
                }
                
                $name = (string)$prodNode->xProd;
                $qty = floatval((string)$prodNode->qCom);
                $unitPrice = floatval((string)$prodNode->vUnCom);
                $totalPrice = floatval((string)$prodNode->vProd);

                // Processa o produto
                $product = $this->normalizationService->findSimilarProduct($name, $ean);
                $productId = null;

                if ($product) {
                    $productId = $product['id'];
                    // Se o produto não tinha EAN e agora temos, atualiza
                    if (empty($product['ean']) && !empty($ean)) {
                        $product['ean'] = $ean;
                        $this->productRepository->save($product);
                    }
                } else {
                    // Cadastra o produto automaticamente
                    $brandId = $this->normalizationService->detectBrand($name);
                    $productId = $this->productRepository->save([
                        'ean' => $ean,
                        'name' => $name,
                        'normalized_name' => $this->normalizationService->cleanString($name),
                        'brand_id' => $brandId,
                        'category_id' => null, // Associa à categoria nula primeiro
                    ]);
                }

                // Registra o preço no histórico
                $this->priceRepository->savePrice($productId, $marketId, $unitPrice, 0, 0.00, $purchaseDate);

                $parsedItems[] = [
                    'product_id' => $productId,
                    'name' => $name,
                    'ean' => $ean,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice
                ];
            }

            return [
                'success' => true,
                'market_id' => $marketId,
                'market_name' => $marketName,
                'purchase_date' => $purchaseDate,
                'items' => $parsedItems
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Analisa o texto bruto OCR extraído do frontend
     * Tenta identificar itens, quantidades e valores usando expressões regulares
     */
    public function parseOcrText($text) {
        $lines = explode("\n", $text);
        $parsedItems = [];
        
        // Padrões de expressões regulares comuns em cupons fiscais
        // Exemplo: 001 123456 DETERGENTE YPE NEUTRO 500ML 1 UN X 2,19 2,19
        // Exemplo: LEITE INTEGRAL ITALAC 1L  1.000 UN X 4.59
        // Exemplo: CERVEJA SKOL LATA 350ML 3,99
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $matched = false;
            $name = '';
            $qty = 1.00;
            $unitPrice = 0.00;
            $totalPrice = 0.00;

            // Padrão 1: Contendo UN, PCT, KG, etc. e multiplicador X (ex: 2 UN X 4,50 ou 1.000 KG x 12,90)
            if (preg_match('/(.+?)\s+(\d+(?:[\.,]\d+)?)\s*(?:un|unid|kg|g|lt|l|fd|pct|cx)?\s*[xX]\s*(\d+(?:[\.,]\d+)?)\s*(\d+(?:[\.,]\d+)?)/i', $line, $matches)) {
                $name = trim($matches[1]);
                $qty = floatval(str_replace(',', '.', $matches[2]));
                $unitPrice = floatval(str_replace(',', '.', $matches[3]));
                $totalPrice = floatval(str_replace(',', '.', $matches[4]));
                $matched = true;
            } 
            // Padrão 2: Nome seguido de valor no final da linha (ex: PÃO FRANCES KG 14,90 ou COCA COLA 2L 9.90)
            elseif (preg_match('/(.+?)\s+(\d+[\.,]\d{2})$/', $line, $matches)) {
                $name = trim($matches[1]);
                $totalPrice = floatval(str_replace(',', '.', $matches[2]));
                $unitPrice = $totalPrice; // Se não tem quantidade, assume 1
                $qty = 1.00;
                $matched = true;
            }

            if ($matched && strlen($name) > 3) {
                // Limpeza básica no nome de ruídos de numeração (ex: remover '001', '123456' do início)
                $name = preg_replace('/^\d+\s+\d+\s+/', '', $name); // remove índice e código interno
                $name = preg_replace('/^\d+\s+/', '', $name);       // remove índice sozinho
                $name = trim($name);

                // Normaliza o produto buscando na base
                $similar = $this->normalizationService->findSimilarProduct($name);
                $productId = $similar ? $similar['id'] : null;
                $isNew = ($productId === null);

                $parsedItems[] = [
                    'product_id' => $productId,
                    'name' => $name,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'is_new' => $isNew,
                    'suggested_name' => $similar ? $similar['name'] : $name
                ];
            }
        }

        return $parsedItems;
    }
}

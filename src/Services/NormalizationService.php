<?php
namespace App\Services;

use App\Repositories\ProductRepository;

class NormalizationService {
    private $productRepository;
    private $stopWords = ['de', 'com', 'em', 'para', 'do', 'da', 'uht', 'tipo', 'a', 'o', 'e'];

    public function __construct() {
        $this->productRepository = new ProductRepository();
    }

    /**
     * Normaliza uma string de texto (remove acentos, pontuação, caixa baixa)
     */
    public function cleanString($str) {
        $str = mb_strtolower($str, 'UTF-8');
        
        // Remove acentos
        $utf8 = [
            '/[áàâãä]/u'   => 'a',
            '/[éèêë]/u'    => 'e',
            '/[íìîï]/u'    => 'i',
            '/[óòôõö]/u'   => 'o',
            '/[úùûü]/u'    => 'u',
            '/[ç]/u'       => 'c',
            '/[ñ]/u'       => 'n',
        ];
        $str = preg_replace(array_keys($utf8), array_values($utf8), $str);
        
        // Substitui pontuações e símbolos por espaços
        $str = preg_replace('/[^\w\s\d]/u', ' ', $str);
        // Remove espaços duplos
        $str = preg_replace('/\s+/', ' ', $str);
        
        return trim($str);
    }

    /**
     * Extrai peso/volume de um nome de produto
     * Retorna array com ['value' => float, 'unit' => string, 'normalized' => string]
     */
    public function extractVolume($name) {
        $clean = $this->cleanString($name);
        
        // Padrões comuns: 1l, 1 l, 500g, 500 g, 1.5l, 1,5 kg, 350ml
        // Regex captura o valor numérico e a unidade (l, ml, kg, g)
        if (preg_match('/(\d+(?:[\.,]\d+)?)\s*(l|ml|kg|g|uni|un|saco|pct|folhas|unidades)\b/i', $clean, $matches)) {
            $value = floatval(str_replace(',', '.', $matches[1]));
            $unit = strtolower($matches[2]);
            
            // Padronizações de unidades
            if ($unit === 'un') $unit = 'uni';
            
            return [
                'value' => $value,
                'unit' => $unit,
                'normalized' => $value . $unit
            ];
        }
        
        return null;
    }

    /**
     * Tenta identificar a marca a partir do nome do produto comparando com marcas cadastradas
     */
    public function detectBrand($name) {
        $cleanName = $this->cleanString($name);
        $brands = $this->productRepository->getBrands();
        
        foreach ($brands as $brand) {
            $cleanBrand = $this->cleanString($brand['name']);
            if (preg_match('/\b' . preg_quote($cleanBrand, '/') . '\b/i', $cleanName)) {
                return $brand['id'];
            }
        }
        
        return null;
    }

    /**
     * Remove marcas, volumes e stop-words para extrair apenas a descrição essencial do produto
     */
    public function getBaseDescription($name) {
        $clean = $this->cleanString($name);
        
        // Remove marca se detectada
        $brands = $this->productRepository->getBrands();
        foreach ($brands as $brand) {
            $cleanBrand = $this->cleanString($brand['name']);
            $clean = preg_replace('/\b' . preg_quote($cleanBrand, '/') . '\b/i', '', $clean);
        }
        
        // Remove indicação de volume (ex: 1l, 500g)
        $clean = preg_replace('/\b\d+(?:[\.,]\d+)?\s*(l|ml|kg|g|uni|un|saco|pct|folhas|unidades)\b/i', '', $clean);
        
        // Tokeniza e remove stop words
        $tokens = explode(' ', $clean);
        $filteredTokens = array_filter($tokens, function($token) {
            return strlen($token) > 1 && !in_array($token, $this->stopWords);
        });
        
        return implode(' ', $filteredTokens);
    }

    /**
     * Busca um produto existente que seja estatisticamente similar ao informado
     */
    public function findSimilarProduct($name, $ean = null) {
        // 1. Se tem EAN, a busca é exata
        if (!empty($ean)) {
            $prod = $this->productRepository->findByEan($ean);
            if ($prod) return $prod;
        }

        // 2. Tenta busca exata por nome normalizado
        $normalizedInput = $this->cleanString($name);
        $prod = $this->productRepository->findByNormalizedName($normalizedInput);
        if ($prod) return $prod;

        // 3. Busca por similaridade de tokens
        $allProducts = $this->productRepository->all();
        $inputVolume = $this->extractVolume($name);
        $inputBase = $this->getBaseDescription($name);
        $inputTokens = explode(' ', $inputBase);
        
        if (empty($inputTokens)) {
            return null;
        }

        $bestMatch = null;
        $highestSimilarity = 0.0;
        $threshold = 0.70; // 70% de similaridade mínima nos tokens essenciais

        foreach ($allProducts as $product) {
            // Se informamos volume, o volume do produto candidato DEVE coincidir
            $candidateVolume = $this->extractVolume($product['name']);
            if ($inputVolume && $candidateVolume) {
                if ($inputVolume['normalized'] !== $candidateVolume['normalized']) {
                    continue; // Volumes diferentes = produtos diferentes (ex: Coca-Cola 350ml vs 2L)
                }
            } elseif (($inputVolume && !$candidateVolume) || (!$inputVolume && $candidateVolume)) {
                // Um tem volume e o outro não, reduz a chance de ser o mesmo
                continue;
            }

            // Compara tokens essenciais
            $candidateBase = $this->getBaseDescription($product['name']);
            $candidateTokens = explode(' ', $candidateBase);
            
            if (empty($candidateTokens)) continue;

            $intersection = array_intersect($inputTokens, $candidateTokens);
            $totalUniqueTokens = count(array_unique(array_merge($inputTokens, $candidateTokens)));
            
            if ($totalUniqueTokens > 0) {
                $similarity = count($intersection) / $totalUniqueTokens;
                
                // Se a marca coincidir, damos um bônus de similaridade
                $inputBrand = $this->detectBrand($name);
                $candidateBrand = $product['brand_id'];
                if ($inputBrand && $candidateBrand && $inputBrand == $candidateBrand) {
                    $similarity += 0.15;
                }

                if ($similarity > $highestSimilarity && $similarity >= $threshold) {
                    $highestSimilarity = $similarity;
                    $bestMatch = $product;
                }
            }
        }

        return $bestMatch;
    }
}

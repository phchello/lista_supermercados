<?php
namespace App\Repositories;

use Database;
use PDO;

class PriceHistoryRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function savePrice($productId, $marketId, $price, $isPromotion = 0, $discountPercentage = 0.00, $collectedAt = null) {
        $collectedAt = $collectedAt ?: date('Y-m-d H:i:s');
        
        // Verifica se já existe uma coleta idêntica no mesmo dia/hora (para evitar duplicatas exatas na mesma execução)
        $checkStmt = $this->db->prepare("
            SELECT id FROM price_history 
            WHERE product_id = ? AND market_id = ? AND price = ? AND DATE(collected_at) = DATE(?)
            LIMIT 1
        ");
        $checkStmt->execute([$productId, $marketId, $price, $collectedAt]);
        if ($checkStmt->fetch()) {
            return true; // Já registrado hoje com esse preço
        }

        $stmt = $this->db->prepare("
            INSERT INTO price_history (product_id, market_id, price, is_promotion, discount_percentage, collected_at)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $productId,
            $marketId,
            $price,
            $isPromotion,
            $discountPercentage,
            $collectedAt
        ]);
    }

    public function getLatestPromotions($limit = 10) {
        $sql = "
            SELECT 
                ph.*, 
                p.name as product_name, 
                p.image_url,
                m.name as market_name,
                (
                    SELECT AVG(ph2.price) 
                    FROM price_history ph2 
                    WHERE ph2.product_id = p.id
                ) as avg_historical_price
            FROM price_history ph
            JOIN products p ON ph.product_id = p.id
            JOIN markets m ON ph.market_id = m.id
            WHERE ph.is_promotion = 1 AND ph.collected_at = (
                SELECT MAX(ph3.collected_at) 
                FROM price_history ph3 
                WHERE ph3.product_id = p.id AND ph3.market_id = m.id
            ) AND m.active = 1
            ORDER BY ph.discount_percentage DESC, ph.collected_at DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getLatestUpdates($limit = 10) {
        $sql = "
            SELECT ph.*, p.name as product_name, m.name as market_name
            FROM price_history ph
            JOIN products p ON ph.product_id = p.id
            JOIN markets m ON ph.market_id = m.id
            ORDER BY ph.collected_at DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getBestDayToShop() {
        // Análise de média de preço por dia da semana (1 = Domingo, 7 = Sábado em MySQL DAYOFWEEK)
        // Retorna média dos preços coletados agrupados pelo dia de semana em que foram coletados
        $sql = "
            SELECT 
                DAYOFWEEK(collected_at) as day_num,
                AVG(price) as avg_price,
                COUNT(*) as sample_count
            FROM price_history
            GROUP BY DAYOFWEEK(collected_at)
            ORDER BY avg_price ASC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function getBestMonthToShop() {
        $sql = "
            SELECT 
                MONTH(collected_at) as month_num,
                AVG(price) as avg_price
            FROM price_history
            GROUP BY MONTH(collected_at)
            ORDER BY avg_price ASC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function getPriceOscillation() {
        // Retorna a variação máxima de preços dos produtos
        $sql = "
            SELECT 
                p.id,
                p.name as product_name,
                MIN(ph.price) as min_price,
                MAX(ph.price) as max_price,
                AVG(ph.price) as avg_price,
                (MAX(ph.price) - MIN(ph.price)) as diff,
                ROUND(((MAX(ph.price) - MIN(ph.price)) / MIN(ph.price)) * 100, 2) as variation_percentage
            FROM price_history ph
            JOIN products p ON ph.product_id = p.id
            GROUP BY p.id
            HAVING diff > 0
            ORDER BY variation_percentage DESC
            LIMIT 10;
        ";
        return $this->db->query($sql)->fetchAll();
    }
}

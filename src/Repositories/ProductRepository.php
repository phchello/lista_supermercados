<?php
namespace App\Repositories;

use Database;
use PDO;

class ProductRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function all($filters = []) {
        $sql = "
            SELECT 
                p.*, 
                b.name as brand_name, 
                c.name as category_name,
                MIN(ph.price) as min_price,
                MAX(ph.price) as max_price,
                AVG(ph.price) as avg_price,
                (
                    SELECT ph2.price 
                    FROM price_history ph2 
                    JOIN markets m2 ON ph2.market_id = m2.id
                    WHERE ph2.product_id = p.id AND m2.active = 1
                    ORDER BY ph2.price ASC, ph2.collected_at DESC 
                    LIMIT 1
                ) as cheapest_price,
                (
                    SELECT m.name 
                    FROM price_history ph2 
                    JOIN markets m ON ph2.market_id = m.id
                    WHERE ph2.product_id = p.id AND m.active = 1
                    ORDER BY ph2.price ASC, ph2.collected_at DESC 
                    LIMIT 1
                ) as cheapest_market
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN price_history ph ON p.id = ph.product_id AND ph.collected_at IN (
                -- Subquery para pegar apenas o preço mais recente de cada mercado para cada produto
                SELECT MAX(ph3.collected_at) 
                FROM price_history ph3 
                WHERE ph3.product_id = p.id 
                GROUP BY ph3.market_id
            )
            WHERE 1=1
        ";
        
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.ean LIKE ? OR b.name LIKE ? OR c.name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($filters['brand_id'])) {
            $sql .= " AND p.brand_id = ?";
            $params[] = $filters['brand_id'];
        }

        if (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['ean'])) {
            $sql .= " AND p.ean = ?";
            $params[] = $filters['ean'];
        }

        $sql .= " GROUP BY p.id ORDER BY p.name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT p.*, b.name as brand_name, c.name as category_name
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByEan($ean) {
        if (empty($ean)) return null;
        $stmt = $this->db->prepare("
            SELECT p.*, b.name as brand_name, c.name as category_name
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.ean = ?
        ");
        $stmt->execute([$ean]);
        return $stmt->fetch() ?: null;
    }

    public function findByName($name) {
        $stmt = $this->db->prepare("
            SELECT p.*, b.name as brand_name, c.name as category_name
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE LOWER(p.name) = ?
        ");
        $stmt->execute([strtolower(trim($name))]);
        return $stmt->fetch() ?: null;
    }

    public function findByNormalizedName($normalizedName) {
        $stmt = $this->db->prepare("
            SELECT p.*, b.name as brand_name, c.name as category_name
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.normalized_name = ?
        ");
        $stmt->execute([$normalizedName]);
        return $stmt->fetch() ?: null;
    }

    public function save($data) {
        $normalizedName = isset($data['normalized_name']) ? $data['normalized_name'] : strtolower(trim($data['name']));
        
        if (isset($data['id']) && $data['id'] > 0) {
            // Update
            $stmt = $this->db->prepare("
                UPDATE products 
                SET ean = ?, name = ?, normalized_name = ?, brand_id = ?, category_id = ?, image_url = ?
                WHERE id = ?
            ");
            $stmt->execute([
                !empty($data['ean']) ? $data['ean'] : null,
                $data['name'],
                $normalizedName,
                $data['brand_id'] ?: null,
                $data['category_id'] ?: null,
                $data['image_url'] ?? null,
                $data['id']
            ]);
            return $data['id'];
        } else {
            // Insert
            $stmt = $this->db->prepare("
                INSERT INTO products (ean, name, normalized_name, brand_id, category_id, image_url)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                !empty($data['ean']) ? $data['ean'] : null,
                $data['name'],
                $normalizedName,
                $data['brand_id'] ?: null,
                $data['category_id'] ?: null,
                $data['image_url'] ?? null
            ]);
            return $this->db->lastInsertId();
        }
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Gerenciamento de Marcas auxiliares
    public function getBrands() {
        return $this->db->query("SELECT * FROM brands ORDER BY name ASC")->fetchAll();
    }

    public function updateBrandPreference($brandId, $preference) {
        $stmt = $this->db->prepare("UPDATE brands SET preference = ? WHERE id = ?");
        return $stmt->execute([$preference, $brandId]);
    }

    public function findOrCreateBrand($name) {
        $name = trim($name);
        if (empty($name)) return null;

        $stmt = $this->db->prepare("SELECT id FROM brands WHERE LOWER(name) = ?");
        $stmt->execute([strtolower($name)]);
        $brand = $stmt->fetch();

        if ($brand) {
            return $brand['id'];
        }

        $stmt = $this->db->prepare("INSERT INTO brands (name) VALUES (?)");
        $stmt->execute([$name]);
        return $this->db->lastInsertId();
    }

    // Gerenciamento de Categorias auxiliares
    public function getCategories() {
        return $this->db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
    }

    public function findOrCreateCategory($name) {
        $name = trim($name);
        if (empty($name)) return null;

        $stmt = $this->db->prepare("SELECT id FROM categories WHERE LOWER(name) = ?");
        $stmt->execute([strtolower($name)]);
        $cat = $stmt->fetch();

        if ($cat) {
            return $cat['id'];
        }

        $stmt = $this->db->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
        return $this->db->lastInsertId();
    }

    // Preços mais recentes por mercado
    public function getLatestPricesForProduct($productId) {
        $sql = "
            SELECT 
                m.id as market_id, 
                m.name as market_name, 
                m.logo_url,
                ph.price, 
                ph.is_promotion, 
                ph.discount_percentage,
                ph.collected_at
            FROM markets m
            LEFT JOIN price_history ph ON ph.market_id = m.id AND ph.product_id = ? AND ph.collected_at = (
                SELECT MAX(ph2.collected_at) 
                FROM price_history ph2 
                WHERE ph2.product_id = ? AND ph2.market_id = m.id
            )
            WHERE m.active = 1
            ORDER BY ph.price ASC, m.name ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId, $productId]);
        return $stmt->fetchAll();
    }

    // Preços mais recentes de todos os produtos ativos nos mercados
    public function getAllLatestPrices() {
        $sql = "
            SELECT ph.product_id, ph.market_id, ph.price, ph.is_promotion, ph.discount_percentage, ph.collected_at
            FROM price_history ph
            JOIN markets m ON ph.market_id = m.id
            WHERE m.active = 1
              AND ph.collected_at = (
                  SELECT MAX(ph2.collected_at)
                  FROM price_history ph2
                  WHERE ph2.product_id = ph.product_id AND ph2.market_id = ph.market_id
              )
        ";
        return $this->db->query($sql)->fetchAll();
    }

    // Histórico de preços
    public function getPriceHistory($productId, $days = 30) {
        $sql = "
            SELECT ph.*, m.name as market_name
            FROM price_history ph
            JOIN markets m ON ph.market_id = m.id
            WHERE ph.product_id = ? AND ph.collected_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            ORDER BY ph.collected_at ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId, $days]);
        return $stmt->fetchAll();
    }

    public function getGlobalStats() {
        $totalProducts = $this->db->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $totalMarkets = $this->db->query("SELECT COUNT(*) FROM markets WHERE active = 1")->fetchColumn();
        $updatesToday = $this->db->query("SELECT COUNT(*) FROM price_history WHERE DATE(collected_at) = CURDATE()")->fetchColumn();
        
        $promotions = $this->db->query("
            SELECT COUNT(DISTINCT product_id) 
            FROM price_history 
            WHERE is_promotion = 1 AND collected_at >= DATE_SUB(NOW(), INTERVAL 2 DAY)
        ")->fetchColumn();

        return [
            'total_products' => $totalProducts,
            'total_markets' => $totalMarkets,
            'updates_today' => $updatesToday,
            'promotions_today' => $promotions
        ];
    }
}

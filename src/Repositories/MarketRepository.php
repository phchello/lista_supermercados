<?php
namespace App\Repositories;

use Database;
use PDO;

class MarketRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function all($onlyActive = false) {
        $sql = "SELECT * FROM markets";
        if ($onlyActive) {
            $sql .= " WHERE active = 1";
        }
        $sql .= " ORDER BY name ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM markets WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByName($name) {
        $stmt = $this->db->prepare("SELECT * FROM markets WHERE LOWER(name) = ?");
        $stmt->execute([strtolower(trim($name))]);
        return $stmt->fetch() ?: null;
    }

    public function save($data) {
        if (isset($data['id']) && $data['id'] > 0) {
            // Update
            $stmt = $this->db->prepare("
                UPDATE markets 
                SET name = ?, logo_url = ?, website_url = ?, active = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['name'],
                $data['logo_url'] ?? null,
                $data['website_url'] ?? null,
                $data['active'] ?? 1,
                $data['id']
            ]);
            return $data['id'];
        } else {
            // Insert
            $stmt = $this->db->prepare("
                INSERT INTO markets (name, logo_url, website_url, active)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['name'],
                $data['logo_url'] ?? null,
                $data['website_url'] ?? null,
                $data['active'] ?? 1
            ]);
            return $this->db->lastInsertId();
        }
    }

    public function toggleActive($id) {
        $stmt = $this->db->prepare("UPDATE markets SET active = 1 - active WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getStats() {
        // Query to get stats per market
        $sql = "
            SELECT 
                m.id, 
                m.name, 
                m.logo_url, 
                m.active,
                m.website_url,
                (SELECT COUNT(DISTINCT ph.product_id) FROM price_history ph WHERE ph.market_id = m.id) as product_count,
                (SELECT MAX(ph.collected_at) FROM price_history ph WHERE ph.market_id = m.id) as last_update,
                (SELECT AVG(ph.price) FROM price_history ph WHERE ph.market_id = m.id) as avg_price
            FROM markets m
            ORDER BY m.name ASC
        ";
        return $this->db->query($sql)->fetchAll();
    }
}

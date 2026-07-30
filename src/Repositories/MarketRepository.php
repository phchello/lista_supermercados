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

    public function getSearchUrl($marketId, $productName) {
        $stmt = $this->db->prepare("SELECT name FROM markets WHERE id = ?");
        $stmt->execute([$marketId]);
        $market = $stmt->fetch();
        if (!$market) return "https://www.google.com/search?q=" . urlencode($productName);

        $name = mb_strtolower($market['name'], 'UTF-8');
        $q = urlencode($productName);

        if (strpos($name, 'ayumi') !== false) {
            return "https://ayumi.com.br/loja/search/product?q=" . $q;
        } elseif (strpos($name, 'atacadao') !== false) {
            return "https://www.atacadao.com.br/busca?q=" . $q;
        } elseif (strpos($name, 'tenda') !== false) {
            return "https://www.tendaatacado.com.br/pesquisa?termo=" . $q;
        } elseif (strpos($name, 'assai') !== false) {
            return "https://www.assai.com.br/busca?q=" . $q;
        } elseif (strpos($name, 'sonda') !== false) {
            return "https://www.sondadelivery.com.br/sonda/busca?q=" . $q;
        } elseif (strpos($name, 'pao de acucar') !== false) {
            return "https://www.paodeacucar.com/busca?termo=" . $q;
        } elseif (strpos($name, 'carrefour') !== false) {
            return "https://www.carrefour.com.br/busca?termo=" . $q;
        } elseif (strpos($name, 'extra') !== false) {
            return "https://www.clubeextra.com.br/busca?termo=" . $q;
        } elseif (strpos($name, 'dia') !== false) {
            return "https://www.dia.com.br/busca?q=" . $q;
        } elseif (strpos($name, 'roldao') !== false) {
            return "https://roldao.com.br/?s=" . $q;
        } elseif (strpos($name, 'spani') !== false) {
            return "https://spani.com.br/?s=" . $q;
        } elseif (strpos($name, 'cercadao') !== false) {
            return "https://cercadao.com.br/?s=" . $q;
        }
        
        return "https://www.google.com/search?q=" . urlencode($market['name'] . ' ' . $productName);
    }
}

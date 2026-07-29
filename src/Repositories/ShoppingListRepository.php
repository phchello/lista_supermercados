<?php
namespace App\Repositories;

use Database;
use PDO;

class ShoppingListRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function all() {
        $sql = "
            SELECT sli.*, 
                   (SELECT COUNT(*) FROM shopping_list_items WHERE list_id = sli.id) as total_items,
                   (SELECT SUM(quantity) FROM shopping_list_items WHERE list_id = sli.id) as total_quantity
            FROM shopping_lists sli
            ORDER BY sli.created_at DESC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM shopping_lists WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function save($name, $id = null) {
        if ($id && $id > 0) {
            $stmt = $this->db->prepare("UPDATE shopping_lists SET name = ? WHERE id = ?");
            $stmt->execute([$name, $id]);
            return $id;
        } else {
            $stmt = $this->db->prepare("INSERT INTO shopping_lists (name) VALUES (?)");
            $stmt->execute([$name]);
            return $this->db->lastInsertId();
        }
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM shopping_lists WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getItems($listId) {
        $sql = "
            SELECT 
                sli.id as item_id,
                sli.quantity,
                sli.observation,
                p.id as product_id,
                p.name as product_name,
                p.ean,
                b.name as brand_name,
                c.name as category_name
            FROM shopping_list_items sli
            JOIN products p ON sli.product_id = p.id
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE sli.list_id = ?
            ORDER BY p.name ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$listId]);
        return $stmt->fetchAll();
    }

    public function getPricesForListProducts($listId) {
        // Retorna todos os preços mais recentes por mercado de todos os produtos presentes na lista
        $sql = "
            SELECT ph.product_id, ph.market_id, ph.price, ph.is_promotion, ph.discount_percentage, ph.collected_at
            FROM price_history ph
            JOIN markets m ON ph.market_id = m.id
            WHERE m.active = 1 
              AND ph.product_id IN (SELECT product_id FROM shopping_list_items WHERE list_id = ?)
              AND ph.collected_at = (
                  SELECT MAX(ph2.collected_at)
                  FROM price_history ph2
                  WHERE ph2.product_id = ph.product_id AND ph2.market_id = ph.market_id
              )
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$listId]);
        return $stmt->fetchAll();
    }

    public function addItem($listId, $productId, $quantity = 1, $observation = '') {
        // Verifica se o item já existe na lista
        $stmt = $this->db->prepare("SELECT id, quantity FROM shopping_list_items WHERE list_id = ? AND product_id = ?");
        $stmt->execute([$listId, $productId]);
        $existing = $stmt->fetch();

        if ($existing) {
            $newQuantity = $existing['quantity'] + $quantity;
            $stmt = $this->db->prepare("UPDATE shopping_list_items SET quantity = ?, observation = ? WHERE id = ?");
            return $stmt->execute([$newQuantity, $observation, $existing['id']]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO shopping_list_items (list_id, product_id, quantity, observation) VALUES (?, ?, ?, ?)");
            return $stmt->execute([$listId, $productId, $quantity, $observation]);
        }
    }

    public function removeItem($listId, $productId) {
        $stmt = $this->db->prepare("DELETE FROM shopping_list_items WHERE list_id = ? AND product_id = ?");
        return $stmt->execute([$listId, $productId]);
    }

    public function updateItemQuantity($listId, $productId, $quantity) {
        $stmt = $this->db->prepare("UPDATE shopping_list_items SET quantity = ? WHERE list_id = ? AND product_id = ?");
        return $stmt->execute([$quantity, $listId, $productId]);
    }

    public function savePurchase($shoppingListId, $purchaseDate, $totalValue, $marketId, $savings, $itemsJson) {
        $stmt = $this->db->prepare("
            INSERT INTO purchase_history (shopping_list_id, purchase_date, total_value, market_id, savings, items_json)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $shoppingListId ?: null,
            $purchaseDate,
            $totalValue,
            $marketId,
            $savings,
            $itemsJson
        ]);
    }

    public function getPurchaseHistory() {
        $sql = "
            SELECT ph.*, m.name as market_name, sl.name as list_name
            FROM purchase_history ph
            JOIN markets m ON ph.market_id = m.id
            LEFT JOIN shopping_lists sl ON ph.shopping_list_id = sl.id
            ORDER BY ph.purchase_date DESC, ph.created_at DESC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function getPurchaseHistoryDetail($id) {
        $stmt = $this->db->prepare("
            SELECT ph.*, m.name as market_name, sl.name as list_name
            FROM purchase_history ph
            JOIN markets m ON ph.market_id = m.id
            LEFT JOIN shopping_lists sl ON ph.shopping_list_id = sl.id
            WHERE ph.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}

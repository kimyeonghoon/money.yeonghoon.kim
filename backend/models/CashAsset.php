<?php

require_once __DIR__ . '/BaseModel.php';

class CashAsset extends BaseModel {
    protected $table = 'cash_assets';
    protected $fillable = [
        'type',
        'account_name',
        'item_name',
        'balance',
        'display_order'
    ];

    protected $defaults = [
        'type' => '현금'
    ];

    public function create($data) {
        // display_order가 지정되지 않았으면 다음 순서로 설정
        if (!isset($data['display_order'])) {
            $data['display_order'] = $this->getNextDisplayOrder();
        }

        return parent::create($data);
    }

    public function getTotalBalance() {
        $sql = "SELECT SUM(balance) as total FROM {$this->table} WHERE deleted_at IS NULL";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'] ?: 0;
    }

    public function getByType($type) {
        $sql = "SELECT * FROM {$this->table} WHERE type = ? AND deleted_at IS NULL ORDER BY display_order ASC, id ASC";
        $stmt = $this->db->query($sql, [$type]);
        return $stmt->fetchAll();
    }

    public function getTotalByType() {
        $sql = "SELECT type, SUM(balance) as total FROM {$this->table} WHERE deleted_at IS NULL GROUP BY type";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function searchByName($keyword, $limit = null, $offset = null) {
        $searchTerm = "%{$keyword}%";
        $sql = "SELECT * FROM {$this->table} WHERE (item_name LIKE ? OR account_name LIKE ?) AND deleted_at IS NULL ORDER BY created_at DESC";

        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }

        $stmt = $this->db->query($sql, [$searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }

    public function getAllWithPercentage() {
        $totalBalance = $this->getTotalBalance();
        $sql = "SELECT * FROM {$this->table} WHERE deleted_at IS NULL ORDER BY display_order ASC, id ASC";
        $stmt = $this->db->query($sql);
        $assets = $stmt->fetchAll();

        // 비중 계산 추가
        foreach ($assets as &$asset) {
            if ($totalBalance > 0) {
                $asset['percentage'] = round(($asset['balance'] / $totalBalance) * 100, 2);
            } else {
                $asset['percentage'] = 0;
            }
        }

        return $assets;
    }

    public function getByIdWithPercentage($id) {
        $asset = $this->getById($id);
        if (!$asset) {
            return null;
        }

        $totalBalance = $this->getTotalBalance();
        if ($totalBalance > 0) {
            $asset['percentage'] = round(($asset['balance'] / $totalBalance) * 100, 2);
        } else {
            $asset['percentage'] = 0;
        }

        return $asset;
    }

    public function updateDisplayOrders($orderData) {
        try {
            $this->db->beginTransaction();

            foreach ($orderData as $order) {
                $sql = "UPDATE {$this->table} SET display_order = ? WHERE id = ? AND deleted_at IS NULL";
                $this->db->query($sql, [$order['order'], $order['id']]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Order update failed: " . $e->getMessage());
            return false;
        }
    }

    public function getNextDisplayOrder() {
        $sql = "SELECT MAX(display_order) as max_order FROM {$this->table} WHERE deleted_at IS NULL";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return ($result['max_order'] ?? 0) + 1;
    }
}
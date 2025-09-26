<?php

require_once __DIR__ . '/BaseModel.php';

class InvestmentAsset extends BaseModel {
    protected $table = 'investment_assets';
    protected $fillable = [
        'category',
        'account_name',
        'item_name',
        'current_value',
        'deposit_amount',
        'display_order'
    ];

    public function getTotalValue() {
        $sql = "SELECT SUM(current_value) as total FROM {$this->table} WHERE deleted_at IS NULL";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'] ?: 0;
    }

    public function getTotalDeposit() {
        $sql = "SELECT SUM(deposit_amount) as total FROM {$this->table} WHERE deleted_at IS NULL";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'] ?: 0;
    }

    public function getByCategory($category) {
        $sql = "SELECT * FROM {$this->table} WHERE category = ? AND deleted_at IS NULL ORDER BY created_at DESC";
        $stmt = $this->db->query($sql, [$category]);
        return $stmt->fetchAll();
    }

    public function getTotalByCategory() {
        $sql = "SELECT category, SUM(current_value) as total_value, SUM(deposit_amount) as total_deposit FROM {$this->table} WHERE deleted_at IS NULL GROUP BY category";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getReturnRate() {
        $totalDeposit = $this->getTotalDeposit();
        $totalValue = $this->getTotalValue();

        if ($totalDeposit == 0) {
            return 0;
        }

        return (($totalValue - $totalDeposit) / $totalDeposit) * 100;
    }

    public function getReturnRateByCategory() {
        $sql = "SELECT category,
                       SUM(current_value) as total_value,
                       SUM(deposit_amount) as total_deposit,
                       CASE
                           WHEN SUM(deposit_amount) = 0 THEN 0
                           ELSE ((SUM(current_value) - SUM(deposit_amount)) / SUM(deposit_amount)) * 100
                       END as return_rate
                FROM {$this->table}
                WHERE deleted_at IS NULL
                GROUP BY category";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function searchByName($keyword, $limit = null, $offset = null) {
        $searchTerm = "%{$keyword}%";
        $sql = "SELECT * FROM {$this->table} WHERE (item_name LIKE ? OR account_name LIKE ?) AND deleted_at IS NULL ORDER BY display_order ASC, id ASC";

        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }

        $stmt = $this->db->query($sql, [$searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }

    public function create($data) {
        // display_order가 지정되지 않았으면 다음 순서로 설정
        if (!isset($data['display_order'])) {
            $data['display_order'] = $this->getNextDisplayOrder();
        }

        return parent::create($data);
    }

    public function getAll($limit = null, $offset = null) {
        $sql = "SELECT * FROM {$this->table} WHERE deleted_at IS NULL ORDER BY display_order ASC, id ASC";

        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getAllWithPercentage() {
        $totalValue = $this->getTotalValue();
        $sql = "SELECT * FROM {$this->table} WHERE deleted_at IS NULL ORDER BY display_order ASC, id ASC";
        $stmt = $this->db->query($sql);
        $assets = $stmt->fetchAll();

        // 비중 계산 추가
        foreach ($assets as &$asset) {
            if ($totalValue > 0) {
                $asset['percentage'] = round(($asset['current_value'] / $totalValue) * 100, 2);
            } else {
                $asset['percentage'] = 0;
            }
        }

        return $assets;
    }

    public function updateDisplayOrders($orderData) {
        try {
            $this->db->beginTransaction();

            foreach ($orderData as $index => $order) {
                $displayOrder = $index + 1; // 1부터 시작하는 순서
                $sql = "UPDATE {$this->table} SET display_order = ? WHERE id = ? AND deleted_at IS NULL";
                $this->db->query($sql, [$displayOrder, $order['id']]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Investment asset order update failed: " . $e->getMessage());
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
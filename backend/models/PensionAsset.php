<?php

require_once __DIR__ . '/BaseModel.php';

class PensionAsset extends BaseModel {
    protected $table = 'pension_assets';
    protected $fillable = [
        'type',
        'item_name',
        'current_value',
        'deposit_amount'
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

    public function getByType($type) {
        $sql = "SELECT * FROM {$this->table} WHERE type = ? AND deleted_at IS NULL ORDER BY created_at DESC";
        $stmt = $this->db->query($sql, [$type]);
        return $stmt->fetchAll();
    }

    public function getTotalByType() {
        $sql = "SELECT type, SUM(current_value) as total_value, SUM(deposit_amount) as total_deposit FROM {$this->table} WHERE deleted_at IS NULL GROUP BY type";
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

    public function getReturnRateByType() {
        $sql = "SELECT type,
                       SUM(current_value) as total_value,
                       SUM(deposit_amount) as total_deposit,
                       CASE
                           WHEN SUM(deposit_amount) = 0 THEN 0
                           ELSE ((SUM(current_value) - SUM(deposit_amount)) / SUM(deposit_amount)) * 100
                       END as return_rate
                FROM {$this->table}
                WHERE deleted_at IS NULL
                GROUP BY type";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function searchByName($keyword, $limit = null, $offset = null) {
        $searchTerm = "%{$keyword}%";
        $sql = "SELECT * FROM {$this->table} WHERE item_name LIKE ? AND deleted_at IS NULL ORDER BY created_at DESC";

        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }

        $stmt = $this->db->query($sql, [$searchTerm]);
        return $stmt->fetchAll();
    }
}
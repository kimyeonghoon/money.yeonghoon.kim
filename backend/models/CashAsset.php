<?php

require_once __DIR__ . '/BaseModel.php';

class CashAsset extends BaseModel {
    protected $table = 'cash_assets';
    protected $fillable = [
        'type',
        'account_name',
        'item_name',
        'balance'
    ];

    public function getTotalBalance() {
        $sql = "SELECT SUM(balance) as total FROM {$this->table} WHERE deleted_at IS NULL";
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
}
<?php

require_once __DIR__ . '/BaseModel.php';

class FixedExpense extends BaseModel {
    protected $table = 'fixed_expenses';
    protected $fillable = [
        'category',
        'item_name',
        'amount',
        'payment_date',
        'payment_method',
        'is_active'
    ];

    public function getActive($limit = null, $offset = null) {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 AND deleted_at IS NULL ORDER BY payment_date ASC";

        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getTotalMonthlyAmount() {
        $sql = "SELECT SUM(amount) as total FROM {$this->table} WHERE is_active = 1 AND deleted_at IS NULL";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'] ?: 0;
    }

    public function getByCategory($category = null) {
        if ($category) {
            $sql = "SELECT * FROM {$this->table} WHERE category = ? AND is_active = 1 AND deleted_at IS NULL ORDER BY payment_date ASC";
            $stmt = $this->db->query($sql, [$category]);
        } else {
            $sql = "SELECT category, SUM(amount) as total_amount, COUNT(*) as count FROM {$this->table} WHERE is_active = 1 AND deleted_at IS NULL GROUP BY category ORDER BY total_amount DESC";
            $stmt = $this->db->query($sql);
        }

        return $stmt->fetchAll();
    }

    public function getByPaymentDate($date) {
        $sql = "SELECT * FROM {$this->table} WHERE payment_date = ? AND is_active = 1 AND deleted_at IS NULL ORDER BY amount DESC";
        $stmt = $this->db->query($sql, [$date]);
        return $stmt->fetchAll();
    }

    public function getByPaymentMethod($method) {
        $sql = "SELECT * FROM {$this->table} WHERE payment_method = ? AND is_active = 1 AND deleted_at IS NULL ORDER BY payment_date ASC";
        $stmt = $this->db->query($sql, [$method]);
        return $stmt->fetchAll();
    }

    public function getUpcomingPayments($days = 7) {
        $currentDay = date('j');
        $maxDay = date('t');

        if ($currentDay + $days <= $maxDay) {
            $sql = "SELECT * FROM {$this->table}
                    WHERE payment_date BETWEEN ? AND ?
                    AND is_active = 1 AND deleted_at IS NULL
                    ORDER BY payment_date ASC";
            $stmt = $this->db->query($sql, [$currentDay, $currentDay + $days]);
        } else {
            $nextMonthDays = ($currentDay + $days) - $maxDay;
            $sql = "SELECT * FROM {$this->table}
                    WHERE (payment_date >= ? OR payment_date <= ?)
                    AND is_active = 1 AND deleted_at IS NULL
                    ORDER BY payment_date ASC";
            $stmt = $this->db->query($sql, [$currentDay, $nextMonthDays]);
        }

        return $stmt->fetchAll();
    }

    public function searchByName($keyword, $limit = null, $offset = null) {
        $searchTerm = "%{$keyword}%";
        $sql = "SELECT * FROM {$this->table} WHERE (item_name LIKE ? OR category LIKE ?) AND deleted_at IS NULL ORDER BY created_at DESC";

        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }

        $stmt = $this->db->query($sql, [$searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }

    public function toggleActive($id) {
        $sql = "UPDATE {$this->table} SET is_active = NOT is_active WHERE id = ? AND deleted_at IS NULL";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }

    public function getPaymentMethodTotals() {
        $sql = "SELECT payment_method, SUM(amount) as total_amount, COUNT(*) as count FROM {$this->table} WHERE is_active = 1 AND deleted_at IS NULL GROUP BY payment_method ORDER BY total_amount DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
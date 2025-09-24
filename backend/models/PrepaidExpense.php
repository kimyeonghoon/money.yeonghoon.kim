<?php

require_once __DIR__ . '/BaseModel.php';

class PrepaidExpense extends BaseModel {
    protected $table = 'prepaid_expenses';
    protected $fillable = [
        'item_name',
        'amount',
        'payment_date',
        'payment_method',
        'expiry_date',
        'is_active'
    ];

    public function getActive($limit = null, $offset = null) {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 AND deleted_at IS NULL ORDER BY expiry_date ASC";

        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getExpiringSoon($days = 30) {
        $sql = "SELECT * FROM {$this->table}
                WHERE expiry_date IS NOT NULL
                AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                AND is_active = 1 AND deleted_at IS NULL
                ORDER BY expiry_date ASC";

        $stmt = $this->db->query($sql, [$days]);
        return $stmt->fetchAll();
    }

    public function getExpired() {
        $sql = "SELECT * FROM {$this->table}
                WHERE expiry_date IS NOT NULL
                AND expiry_date < CURDATE()
                AND is_active = 1 AND deleted_at IS NULL
                ORDER BY expiry_date DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getTotalActiveAmount() {
        $sql = "SELECT SUM(amount) as total FROM {$this->table} WHERE is_active = 1 AND deleted_at IS NULL";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'] ?: 0;
    }

    public function getByPaymentDate($date) {
        $sql = "SELECT * FROM {$this->table} WHERE payment_date = ? AND is_active = 1 AND deleted_at IS NULL ORDER BY amount DESC";
        $stmt = $this->db->query($sql, [$date]);
        return $stmt->fetchAll();
    }

    public function getByPaymentMethod($method) {
        $sql = "SELECT * FROM {$this->table} WHERE payment_method = ? AND is_active = 1 AND deleted_at IS NULL ORDER BY expiry_date ASC";
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

    public function renewExpiry($id, $newExpiryDate) {
        $sql = "UPDATE {$this->table} SET expiry_date = ? WHERE id = ? AND deleted_at IS NULL";
        $stmt = $this->db->query($sql, [$newExpiryDate, $id]);
        return $stmt->rowCount() > 0;
    }

    public function getExpiryStatus() {
        $sql = "SELECT
                    COUNT(CASE WHEN expiry_date < CURDATE() THEN 1 END) as expired,
                    COUNT(CASE WHEN expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as expiring_soon,
                    COUNT(CASE WHEN expiry_date > DATE_ADD(CURDATE(), INTERVAL 30 DAY) OR expiry_date IS NULL THEN 1 END) as valid
                FROM {$this->table}
                WHERE is_active = 1 AND deleted_at IS NULL";

        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }
}
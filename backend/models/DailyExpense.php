<?php

require_once __DIR__ . '/BaseModel.php';

class DailyExpense extends BaseModel {
    protected $table = 'daily_expenses';
    protected $fillable = [
        'expense_date',
        'total_amount',
        'food_cost',
        'necessities_cost',
        'transportation_cost',
        'other_cost'
    ];

    public function getByDateRange($startDate, $endDate, $limit = null, $offset = null) {
        $sql = "SELECT * FROM {$this->table} WHERE expense_date BETWEEN ? AND ? AND deleted_at IS NULL ORDER BY expense_date DESC";

        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }

        $stmt = $this->db->query($sql, [$startDate, $endDate]);
        return $stmt->fetchAll();
    }

    public function getByMonth($year, $month, $limit = null, $offset = null) {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        return $this->getByDateRange($startDate, $endDate, $limit, $offset);
    }

    public function getMonthlyTotal($year, $month) {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $sql = "SELECT
                    SUM(total_amount) as total,
                    SUM(food_cost) as food_total,
                    SUM(necessities_cost) as necessities_total,
                    SUM(transportation_cost) as transportation_total,
                    SUM(other_cost) as other_total
                FROM {$this->table}
                WHERE expense_date BETWEEN ? AND ? AND deleted_at IS NULL";

        $stmt = $this->db->query($sql, [$startDate, $endDate]);
        return $stmt->fetch();
    }

    public function getYearlyTotal($year) {
        $sql = "SELECT
                    MONTH(expense_date) as month,
                    SUM(total_amount) as total,
                    SUM(food_cost) as food_total,
                    SUM(necessities_cost) as necessities_total,
                    SUM(transportation_cost) as transportation_total,
                    SUM(other_cost) as other_total
                FROM {$this->table}
                WHERE YEAR(expense_date) = ? AND deleted_at IS NULL
                GROUP BY MONTH(expense_date)
                ORDER BY month";

        $stmt = $this->db->query($sql, [$year]);
        return $stmt->fetchAll();
    }

    public function getDailyAverage($startDate, $endDate) {
        $sql = "SELECT
                    AVG(total_amount) as avg_total,
                    AVG(food_cost) as avg_food,
                    AVG(necessities_cost) as avg_necessities,
                    AVG(transportation_cost) as avg_transportation,
                    AVG(other_cost) as avg_other
                FROM {$this->table}
                WHERE expense_date BETWEEN ? AND ? AND deleted_at IS NULL";

        $stmt = $this->db->query($sql, [$startDate, $endDate]);
        return $stmt->fetch();
    }

    public function findByDate($date) {
        $sql = "SELECT * FROM {$this->table} WHERE expense_date = ? AND deleted_at IS NULL";
        $stmt = $this->db->query($sql, [$date]);
        return $stmt->fetch();
    }

    public function getRecentExpenses($days = 7, $limit = null) {
        $sql = "SELECT * FROM {$this->table}
                WHERE expense_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                AND deleted_at IS NULL
                ORDER BY expense_date DESC";

        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        $stmt = $this->db->query($sql, [$days]);
        return $stmt->fetchAll();
    }

    public function getCategoryBreakdown($startDate, $endDate) {
        $sql = "SELECT
                    'food' as category,
                    SUM(food_cost) as amount,
                    AVG(food_cost) as avg_amount
                FROM {$this->table}
                WHERE expense_date BETWEEN ? AND ? AND deleted_at IS NULL AND food_cost > 0
                UNION ALL
                SELECT
                    'necessities' as category,
                    SUM(necessities_cost) as amount,
                    AVG(necessities_cost) as avg_amount
                FROM {$this->table}
                WHERE expense_date BETWEEN ? AND ? AND deleted_at IS NULL AND necessities_cost > 0
                UNION ALL
                SELECT
                    'transportation' as category,
                    SUM(transportation_cost) as amount,
                    AVG(transportation_cost) as avg_amount
                FROM {$this->table}
                WHERE expense_date BETWEEN ? AND ? AND deleted_at IS NULL AND transportation_cost > 0
                UNION ALL
                SELECT
                    'other' as category,
                    SUM(other_cost) as amount,
                    AVG(other_cost) as avg_amount
                FROM {$this->table}
                WHERE expense_date BETWEEN ? AND ? AND deleted_at IS NULL AND other_cost > 0
                ORDER BY amount DESC";

        $stmt = $this->db->query($sql, [$startDate, $endDate, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate]);
        return $stmt->fetchAll();
    }
}
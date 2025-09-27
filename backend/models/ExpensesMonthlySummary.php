<?php

require_once __DIR__ . '/BaseModel.php';

class ExpensesMonthlySummary extends BaseModel {
    protected $table = 'expenses_monthly_summary';
    protected $fillable = [
        'summary_month',
        'total_expenses',
        'total_days',
        'avg_daily_expense',
        'food_total',
        'necessities_total',
        'transportation_total',
        'other_total',
        'max_daily_expense',
        'max_expense_date',
        'min_daily_expense',
        'min_expense_date',
        'notes'
    ];

    public function getByMonth($year, $month) {
        $summaryMonth = sprintf('%04d-%02d-01', $year, $month);
        $sql = "SELECT * FROM {$this->table} WHERE summary_month = ?";
        $stmt = $this->db->query($sql, [$summaryMonth]);
        return $stmt->fetch();
    }

    public function getByYearRange($startYear, $endYear) {
        $sql = "SELECT * FROM {$this->table}
                WHERE YEAR(summary_month) BETWEEN ? AND ?
                ORDER BY summary_month DESC";
        $stmt = $this->db->query($sql, [$startYear, $endYear]);
        return $stmt->fetchAll();
    }

    public function createMonthlySummary($year, $month) {
        $summaryMonth = sprintf('%04d-%02d-01', $year, $month);
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        try {
            $this->db->beginTransaction();

            // 기존 요약이 있으면 삭제 (덮어쓰기)
            $this->deleteByMonth($year, $month);

            // 월별 지출 집계 계산
            $summary = $this->calculateMonthlySummary($startDate, $endDate);
            $summary['summary_month'] = $summaryMonth;

            $result = $this->create($summary);

            $this->db->commit();
            return $result;

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Monthly summary creation failed: " . $e->getMessage());
            return false;
        }
    }

    private function calculateMonthlySummary($startDate, $endDate) {
        // 기본 집계 쿼리
        $sql = "SELECT
                    COUNT(*) as total_days,
                    SUM(total_amount) as total_expenses,
                    SUM(food_cost) as food_total,
                    SUM(necessities_cost) as necessities_total,
                    SUM(transportation_cost) as transportation_total,
                    SUM(other_cost) as other_total,
                    AVG(total_amount) as avg_daily_expense,
                    MAX(total_amount) as max_daily_expense,
                    MIN(CASE WHEN total_amount > 0 THEN total_amount END) as min_daily_expense
                FROM daily_expenses
                WHERE expense_date BETWEEN ? AND ?
                  AND deleted_at IS NULL";

        $stmt = $this->db->query($sql, [$startDate, $endDate]);
        $result = $stmt->fetch();

        // 최대 지출 날짜 조회
        $maxDateSql = "SELECT expense_date FROM daily_expenses
                       WHERE expense_date BETWEEN ? AND ?
                         AND total_amount = ?
                         AND deleted_at IS NULL
                       LIMIT 1";
        $maxDateStmt = $this->db->query($maxDateSql, [
            $startDate,
            $endDate,
            $result['max_daily_expense'] ?: 0
        ]);
        $maxDateResult = $maxDateStmt->fetch();

        // 최소 지출 날짜 조회 (0원 제외)
        $minDateSql = "SELECT expense_date FROM daily_expenses
                       WHERE expense_date BETWEEN ? AND ?
                         AND total_amount = ?
                         AND total_amount > 0
                         AND deleted_at IS NULL
                       LIMIT 1";
        $minDateStmt = $this->db->query($minDateSql, [
            $startDate,
            $endDate,
            $result['min_daily_expense'] ?: 0
        ]);
        $minDateResult = $minDateStmt->fetch();

        return [
            'total_expenses' => $result['total_expenses'] ?: 0,
            'total_days' => $result['total_days'] ?: 0,
            'avg_daily_expense' => round($result['avg_daily_expense'] ?: 0),
            'food_total' => $result['food_total'] ?: 0,
            'necessities_total' => $result['necessities_total'] ?: 0,
            'transportation_total' => $result['transportation_total'] ?: 0,
            'other_total' => $result['other_total'] ?: 0,
            'max_daily_expense' => $result['max_daily_expense'] ?: 0,
            'max_expense_date' => $maxDateResult ? $maxDateResult['expense_date'] : null,
            'min_daily_expense' => $result['min_daily_expense'] ?: 0,
            'min_expense_date' => $minDateResult ? $minDateResult['expense_date'] : null
        ];
    }

    public function deleteByMonth($year, $month) {
        $summaryMonth = sprintf('%04d-%02d-01', $year, $month);
        $sql = "DELETE FROM {$this->table} WHERE summary_month = ?";
        return $this->db->query($sql, [$summaryMonth]);
    }

    public function updateSummary($id, $data) {
        return $this->update($id, $data);
    }

    public function getYearlyComparison($year) {
        $sql = "SELECT
                    MONTH(summary_month) as month,
                    total_expenses,
                    avg_daily_expense,
                    food_total,
                    necessities_total,
                    transportation_total,
                    other_total
                FROM {$this->table}
                WHERE YEAR(summary_month) = ?
                ORDER BY month";
        $stmt = $this->db->query($sql, [$year]);
        return $stmt->fetchAll();
    }
}
<?php

require_once __DIR__ . '/BaseModel.php';

class AssetsMonthlySnapshot extends BaseModel {
    protected $table = 'assets_monthly_snapshot';
    protected $fillable = [
        'snapshot_month',
        'asset_type',
        'total_amount',
        'total_count',
        'cash_amount',
        'savings_amount',
        'investment_amount',
        'pension_amount',
        'notes'
    ];

    public function getByMonth($year, $month) {
        $snapshotMonth = sprintf('%04d-%02d-01', $year, $month);
        $sql = "SELECT * FROM {$this->table} WHERE snapshot_month = ? ORDER BY asset_type";
        $stmt = $this->db->query($sql, [$snapshotMonth]);
        return $stmt->fetchAll();
    }

    public function getByYearRange($startYear, $endYear) {
        $sql = "SELECT * FROM {$this->table}
                WHERE YEAR(snapshot_month) BETWEEN ? AND ?
                ORDER BY snapshot_month DESC, asset_type";
        $stmt = $this->db->query($sql, [$startYear, $endYear]);
        return $stmt->fetchAll();
    }

    public function createMonthlySnapshot($year, $month) {
        $snapshotMonth = sprintf('%04d-%02d-01', $year, $month);

        try {
            $this->db->beginTransaction();

            // 기존 스냅샷이 있으면 삭제 (덮어쓰기)
            $this->deleteByMonth($year, $month);

            // 현금성 자산 집계
            $cashSnapshot = $this->calculateCashAssets($year, $month);
            if ($cashSnapshot) {
                $this->create($cashSnapshot);
            }

            // 투자 자산 집계
            $investmentSnapshot = $this->calculateInvestmentAssets($year, $month);
            if ($investmentSnapshot) {
                $this->create($investmentSnapshot);
            }

            // 연금 자산 집계
            $pensionSnapshot = $this->calculatePensionAssets($year, $month);
            if ($pensionSnapshot) {
                $this->create($pensionSnapshot);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Monthly snapshot creation failed: " . $e->getMessage());
            return false;
        }
    }

    private function calculateCashAssets($year, $month) {
        $endDate = date('Y-m-t', strtotime("{$year}-{$month}-01"));

        $sql = "SELECT
                    COUNT(*) as total_count,
                    SUM(balance) as total_amount
                FROM cash_assets
                WHERE deleted_at IS NULL
                   OR (deleted_at IS NOT NULL AND DATE(deleted_at) > ?)";

        $stmt = $this->db->query($sql, [$endDate]);
        $result = $stmt->fetch();

        if ($result && $result['total_count'] > 0) {
            return [
                'snapshot_month' => sprintf('%04d-%02d-01', $year, $month),
                'asset_type' => '현금성',
                'total_amount' => $result['total_amount'] ?: 0,
                'total_count' => $result['total_count'],
                'cash_amount' => $result['total_amount'] ?: 0,
                'savings_amount' => 0,
                'investment_amount' => 0,
                'pension_amount' => 0
            ];
        }
        return null;
    }

    private function calculateInvestmentAssets($year, $month) {
        $endDate = date('Y-m-t', strtotime("{$year}-{$month}-01"));

        $sql = "SELECT
                    COUNT(*) as total_count,
                    SUM(current_value) as total_amount,
                    SUM(CASE WHEN category = '저축' THEN current_value ELSE 0 END) as savings_amount,
                    SUM(CASE WHEN category IN ('혼합', '주식') THEN current_value ELSE 0 END) as investment_amount
                FROM investment_assets
                WHERE deleted_at IS NULL
                   OR (deleted_at IS NOT NULL AND DATE(deleted_at) > ?)";

        $stmt = $this->db->query($sql, [$endDate]);
        $result = $stmt->fetch();

        if ($result && $result['total_count'] > 0) {
            return [
                'snapshot_month' => sprintf('%04d-%02d-01', $year, $month),
                'asset_type' => '투자',
                'total_amount' => $result['total_amount'] ?: 0,
                'total_count' => $result['total_count'],
                'cash_amount' => 0,
                'savings_amount' => $result['savings_amount'] ?: 0,
                'investment_amount' => $result['investment_amount'] ?: 0,
                'pension_amount' => 0
            ];
        }
        return null;
    }

    private function calculatePensionAssets($year, $month) {
        $endDate = date('Y-m-t', strtotime("{$year}-{$month}-01"));

        $sql = "SELECT
                    COUNT(*) as total_count,
                    SUM(current_value) as total_amount
                FROM pension_assets
                WHERE deleted_at IS NULL
                   OR (deleted_at IS NOT NULL AND DATE(deleted_at) > ?)";

        $stmt = $this->db->query($sql, [$endDate]);
        $result = $stmt->fetch();

        if ($result && $result['total_count'] > 0) {
            return [
                'snapshot_month' => sprintf('%04d-%02d-01', $year, $month),
                'asset_type' => '연금',
                'total_amount' => $result['total_amount'] ?: 0,
                'total_count' => $result['total_count'],
                'cash_amount' => 0,
                'savings_amount' => 0,
                'investment_amount' => 0,
                'pension_amount' => $result['total_amount'] ?: 0
            ];
        }
        return null;
    }

    public function deleteByMonth($year, $month) {
        $snapshotMonth = sprintf('%04d-%02d-01', $year, $month);
        $sql = "DELETE FROM {$this->table} WHERE snapshot_month = ?";
        return $this->db->query($sql, [$snapshotMonth]);
    }

    public function updateSnapshot($id, $data) {
        return $this->update($id, $data);
    }
}
<?php

require_once __DIR__ . '/BaseModel.php';

class ArchiveData extends BaseModel {
    protected $table = 'assets_archive_data';
    protected $fillable = [
        'archive_id',
        'asset_table',
        'asset_data'
    ];

    /**
     * 특정 아카이브의 특정 자산 테이블 데이터 조회
     */
    public function getAssetsByArchiveAndTable($archiveId, $assetTable) {
        $sql = "SELECT asset_data FROM {$this->table}
                WHERE archive_id = ? AND asset_table = ?
                ORDER BY id";
        $stmt = $this->db->query($sql, [$archiveId, $assetTable]);

        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $assets = [];

        foreach ($results as $jsonData) {
            $assets[] = json_decode($jsonData, true);
        }

        return $assets;
    }

    /**
     * 아카이브에 자산 데이터 저장
     */
    public function saveAssetData($archiveId, $assetTable, $assetData) {
        return $this->create([
            'archive_id' => $archiveId,
            'asset_table' => $assetTable,
            'asset_data' => json_encode($assetData)
        ]);
    }

    /**
     * 아카이브의 특정 자산 테이블 데이터 전체 교체
     */
    public function replaceAssetData($archiveId, $assetTable, $assetsArray) {
        try {
            $this->db->beginTransaction();

            // 기존 데이터 삭제
            $sql = "DELETE FROM {$this->table} WHERE archive_id = ? AND asset_table = ?";
            $this->db->query($sql, [$archiveId, $assetTable]);

            // 새 데이터 저장
            foreach ($assetsArray as $asset) {
                $this->saveAssetData($archiveId, $assetTable, $asset);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Archive data replacement failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 현재 테이블 데이터를 아카이브로 생성
     */
    public function createArchiveFromCurrentData($archiveId) {
        try {
            $this->db->beginTransaction();

            // 현금성 자산 아카이브
            $this->archiveCurrentAssets($archiveId, 'cash_assets');

            // 투자 자산 아카이브
            $this->archiveCurrentAssets($archiveId, 'investment_assets');

            // 연금 자산 아카이브
            $this->archiveCurrentAssets($archiveId, 'pension_assets');

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Archive creation from current data failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 특정 테이블의 현재 데이터를 아카이브로 저장
     */
    private function archiveCurrentAssets($archiveId, $tableName) {
        $sql = "SELECT * FROM {$tableName} WHERE deleted_at IS NULL ORDER BY display_order, id";
        $stmt = $this->db->query($sql);
        $assets = $stmt->fetchAll();

        foreach ($assets as $asset) {
            $this->saveAssetData($archiveId, $tableName, $asset);
        }
    }

    /**
     * 아카이브 집계 캐시 업데이트
     */
    public function updateSummaryCache($archiveId) {
        try {
            // 각 자산 테이블별 집계
            $cashData = $this->getAssetsByArchiveAndTable($archiveId, 'cash_assets');
            $investmentData = $this->getAssetsByArchiveAndTable($archiveId, 'investment_assets');
            $pensionData = $this->getAssetsByArchiveAndTable($archiveId, 'pension_assets');

            $cashTotal = array_sum(array_column($cashData, 'balance'));
            $investmentTotal = array_sum(array_column($investmentData, 'current_value'));
            $pensionTotal = array_sum(array_column($pensionData, 'current_value'));

            // 캐시 테이블 업데이트
            $sql = "INSERT INTO archive_summary_cache
                    (archive_id, cash_total, cash_count, investment_total, investment_count, pension_total, pension_count)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    cash_total = VALUES(cash_total),
                    cash_count = VALUES(cash_count),
                    investment_total = VALUES(investment_total),
                    investment_count = VALUES(investment_count),
                    pension_total = VALUES(pension_total),
                    pension_count = VALUES(pension_count),
                    updated_at = CURRENT_TIMESTAMP";

            return $this->db->query($sql, [
                $archiveId,
                $cashTotal,
                count($cashData),
                $investmentTotal,
                count($investmentData),
                $pensionTotal,
                count($pensionData)
            ]);

        } catch (Exception $e) {
            error_log("Summary cache update failed: " . $e->getMessage());
            return false;
        }
    }
}
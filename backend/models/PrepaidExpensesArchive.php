<?php

require_once __DIR__ . '/../lib/Database.php';

class PrepaidExpensesArchive {
    private $db;
    private $table = 'archive_data';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * 특정 아카이브에서 고정지출(선납) 데이터 조회
     */
    public function getByArchiveId($archiveId) {
        $sql = "SELECT * FROM {$this->table}
                WHERE archive_id = :archive_id AND asset_table = 'prepaid_expenses'
                ORDER BY asset_id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':archive_id', $archiveId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 고정지출(선납) 아카이브 데이터 저장
     */
    public function savePrepaidExpensesArchive($archiveId, $prepaidExpenses) {
        try {
            $this->db->beginTransaction();

            // 기존 선납지출 아카이브 데이터 삭제
            $deleteSql = "DELETE FROM {$this->table}
                         WHERE archive_id = :archive_id AND asset_table = 'prepaid_expenses'";
            $deleteStmt = $this->db->prepare($deleteSql);
            $deleteStmt->bindParam(':archive_id', $archiveId, PDO::PARAM_INT);
            $deleteStmt->execute();

            // 새 데이터 삽입
            foreach ($prepaidExpenses as $expense) {
                $this->insertPrepaidExpenseArchive($archiveId, $expense);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Prepaid expenses archive save failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 개별 선납지출 아카이브 데이터 삽입
     */
    private function insertPrepaidExpenseArchive($archiveId, $expense) {
        $sql = "INSERT INTO {$this->table}
                (archive_id, asset_table, asset_id, data)
                VALUES (:archive_id, 'prepaid_expenses', :asset_id, :data)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':archive_id', $archiveId, PDO::PARAM_INT);
        $stmt->bindParam(':asset_id', $expense['id'], PDO::PARAM_INT);
        $stmt->bindParam(':data', json_encode($expense, JSON_UNESCAPED_UNICODE), PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * 특정 선납지출 아카이브 데이터 업데이트
     */
    public function updatePrepaidExpenseArchive($archiveId, $assetId, $updatedData) {
        $sql = "UPDATE {$this->table}
                SET data = :data, updated_at = CURRENT_TIMESTAMP
                WHERE archive_id = :archive_id AND asset_table = 'prepaid_expenses' AND asset_id = :asset_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':archive_id', $archiveId, PDO::PARAM_INT);
        $stmt->bindParam(':asset_id', $assetId, PDO::PARAM_INT);
        $stmt->bindParam(':data', json_encode($updatedData, JSON_UNESCAPED_UNICODE), PDO::PARAM_STR);

        return $stmt->execute();
    }
}
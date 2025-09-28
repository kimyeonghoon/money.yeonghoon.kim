<?php

require_once __DIR__ . '/BaseModel.php';

class MonthlyArchive extends BaseModel {
    protected $table = 'monthly_archives';
    protected $fillable = [
        'archive_month',
        'modification_notes'
    ];

    /**
     * 특정 월의 아카이브 조회
     */
    public function getByMonth($year, $month) {
        $archiveMonth = sprintf('%04d-%02d-01', $year, $month);
        $sql = "SELECT * FROM {$this->table} WHERE archive_month = ?";
        $stmt = $this->db->query($sql, [$archiveMonth]);
        return $stmt->fetch();
    }

    /**
     * 모든 아카이브 월 목록 조회 (최신 순)
     */
    public function getAllMonths() {
        $sql = "SELECT archive_month, created_at, last_modified
                FROM {$this->table}
                ORDER BY archive_month DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * BaseModel의 findAll 메서드 오버라이드 (deleted_at 없음)
     */
    public function findAll($limit = null, $offset = null) {
        $sql = "SELECT * FROM {$this->table} ORDER BY archive_month DESC";

        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * BaseModel의 findById 메서드 오버라이드 (deleted_at 없음)
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }

    /**
     * 아카이브 생성 또는 업데이트
     */
    public function createOrUpdate($year, $month, $notes = null) {
        $archiveMonth = sprintf('%04d-%02d-01', $year, $month);

        // 기존 아카이브 확인
        $existing = $this->getByMonth($year, $month);

        if ($existing) {
            // 기존 아카이브 업데이트
            $this->update($existing['id'], [
                'modification_notes' => $notes,
                'last_modified' => date('Y-m-d H:i:s')
            ]);
            return $existing['id'];
        } else {
            // 새 아카이브 생성
            return $this->create([
                'archive_month' => $archiveMonth,
                'modification_notes' => $notes
            ]);
        }
    }

    /**
     * 아카이브가 존재하는지 확인
     */
    public function archiveExists($year, $month) {
        $archive = $this->getByMonth($year, $month);
        return !empty($archive);
    }

    /**
     * 아카이브 삭제 (관련된 모든 데이터 함께 삭제)
     */
    public function deleteArchive($year, $month) {
        $archive = $this->getByMonth($year, $month);
        if (!$archive) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            // 관련된 자산 데이터 먼저 삭제 (CASCADE로 자동 삭제되지만 명시적으로)
            $sql = "DELETE FROM assets_archive_data WHERE archive_id = ?";
            $this->db->query($sql, [$archive['id']]);

            // 캐시 데이터 삭제
            $sql = "DELETE FROM archive_summary_cache WHERE archive_id = ?";
            $this->db->query($sql, [$archive['id']]);

            // 메인 아카이브 삭제
            $this->delete($archive['id']);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Archive deletion failed: " . $e->getMessage());
            return false;
        }
    }
}
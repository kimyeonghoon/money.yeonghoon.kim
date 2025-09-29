<?php
require_once '../lib/Database.php';

class ExpenseArchiveController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * 아카이브된 월 목록 조회
     */
    public function getMonths() {
        try {
            $query = "
                SELECT DISTINCT
                    ma.archive_month as month,
                    ma.id as archive_id,
                    ma.created_at,
                    ma.modification_notes
                FROM monthly_archives ma
                WHERE EXISTS (
                    SELECT 1 FROM fixed_expenses_archive fea WHERE fea.archive_id = ma.id
                    UNION
                    SELECT 1 FROM prepaid_expenses_archive pea WHERE pea.archive_id = ma.id
                )
                ORDER BY ma.archive_month DESC
            ";

            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $months = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return json_encode([
                'success' => true,
                'data' => $months
            ], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            error_log("Error getting expense archive months: " . $e->getMessage());
            return json_encode([
                'success' => false,
                'message' => '아카이브된 월 목록을 가져올 수 없습니다.'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * 아카이브된 고정지출 조회
     */
    public function getFixedExpenses() {
        try {
            $year = $_GET['year'] ?? null;
            $month = $_GET['month'] ?? null;

            if (!$year || !$month) {
                return json_encode([
                    'success' => false,
                    'message' => 'year와 month 파라미터가 필요합니다.'
                ], JSON_UNESCAPED_UNICODE);
            }

            $archiveDate = sprintf('%04d-%02d-01', $year, $month);

            $query = "
                SELECT fea.*
                FROM fixed_expenses_archive fea
                JOIN monthly_archives ma ON fea.archive_id = ma.id
                WHERE ma.archive_month = ?
                ORDER BY
                    CASE WHEN fea.payment_date IS NULL THEN 1 ELSE 0 END,
                    fea.payment_date ASC,
                    fea.item_name ASC
            ";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$archiveDate]);
            $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return json_encode([
                'success' => true,
                'data' => $expenses
            ], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            error_log("Error getting archived fixed expenses: " . $e->getMessage());
            return json_encode([
                'success' => false,
                'message' => '아카이브된 고정지출을 가져올 수 없습니다.'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * 아카이브된 선납지출 조회
     */
    public function getPrepaidExpenses() {
        try {
            $year = $_GET['year'] ?? null;
            $month = $_GET['month'] ?? null;

            if (!$year || !$month) {
                return json_encode([
                    'success' => false,
                    'message' => 'year와 month 파라미터가 필요합니다.'
                ], JSON_UNESCAPED_UNICODE);
            }

            $archiveDate = sprintf('%04d-%02d-01', $year, $month);

            $query = "
                SELECT pea.*
                FROM prepaid_expenses_archive pea
                JOIN monthly_archives ma ON pea.archive_id = ma.id
                WHERE ma.archive_month = ?
                ORDER BY
                    CASE WHEN pea.payment_date IS NULL THEN 1 ELSE 0 END,
                    pea.payment_date ASC,
                    pea.item_name ASC
            ";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$archiveDate]);
            $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return json_encode([
                'success' => true,
                'data' => $expenses
            ], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            error_log("Error getting archived prepaid expenses: " . $e->getMessage());
            return json_encode([
                'success' => false,
                'message' => '아카이브된 선납지출을 가져올 수 없습니다.'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * 지출 요약 정보 조회
     */
    public function getExpenseSummary() {
        try {
            $year = $_GET['year'] ?? null;
            $month = $_GET['month'] ?? null;

            if (!$year || !$month) {
                return json_encode([
                    'success' => false,
                    'message' => 'year와 month 파라미터가 필요합니다.'
                ], JSON_UNESCAPED_UNICODE);
            }

            $archiveDate = sprintf('%04d-%02d-01', $year, $month);

            // 고정지출 총액 계산
            $fixedQuery = "
                SELECT
                    COALESCE(SUM(fea.amount), 0) as total_fixed,
                    COUNT(fea.id) as fixed_count
                FROM fixed_expenses_archive fea
                JOIN monthly_archives ma ON fea.archive_id = ma.id
                WHERE ma.archive_month = ?
            ";

            $stmt = $this->db->prepare($fixedQuery);
            $stmt->execute([$archiveDate]);
            $fixedResult = $stmt->fetch(PDO::FETCH_ASSOC);

            // 선납지출 총액 계산
            $prepaidQuery = "
                SELECT
                    COALESCE(SUM(pea.amount), 0) as total_prepaid,
                    COUNT(pea.id) as prepaid_count
                FROM prepaid_expenses_archive pea
                JOIN monthly_archives ma ON pea.archive_id = ma.id
                WHERE ma.archive_month = ?
            ";

            $stmt = $this->db->prepare($prepaidQuery);
            $stmt->execute([$archiveDate]);
            $prepaidResult = $stmt->fetch(PDO::FETCH_ASSOC);

            $summary = [
                'fixed_total' => $fixedResult['total_fixed'],
                'fixed_count' => $fixedResult['fixed_count'],
                'prepaid_total' => $prepaidResult['total_prepaid'],
                'prepaid_count' => $prepaidResult['prepaid_count'],
                'total_expenses' => $fixedResult['total_fixed'] + $prepaidResult['total_prepaid']
            ];

            return json_encode([
                'success' => true,
                'data' => $summary
            ], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            error_log("Error getting expense summary: " . $e->getMessage());
            return json_encode([
                'success' => false,
                'message' => '지출 요약 정보를 가져올 수 없습니다.'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * 지출 스냅샷 생성
     */
    public function createExpenseSnapshot() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $year = $input['year'] ?? null;
            $month = $input['month'] ?? null;

            if (!$year || !$month) {
                return json_encode([
                    'success' => false,
                    'message' => 'year와 month가 필요합니다.'
                ], JSON_UNESCAPED_UNICODE);
            }

            $archiveDate = sprintf('%04d-%02d-01', $year, $month);

            $this->db->beginTransaction();

            // 월별 아카이브 레코드 확인/생성
            $archiveQuery = "SELECT id FROM monthly_archives WHERE archive_month = ?";
            $stmt = $this->db->prepare($archiveQuery);
            $stmt->execute([$archiveDate]);
            $archive = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$archive) {
                // 새 아카이브 생성
                $createArchiveQuery = "
                    INSERT INTO monthly_archives (archive_month, modification_notes)
                    VALUES (?, ?)
                ";
                $stmt = $this->db->prepare($createArchiveQuery);
                $stmt->execute([$archiveDate, "지출 스냅샷 - {$year}년 {$month}월"]);
                $archiveId = $this->db->lastInsertId();
            } else {
                $archiveId = $archive['id'];

                // 기존 아카이브 데이터 삭제
                $this->db->prepare("DELETE FROM fixed_expenses_archive WHERE archive_id = ?")->execute([$archiveId]);
                $this->db->prepare("DELETE FROM prepaid_expenses_archive WHERE archive_id = ?")->execute([$archiveId]);
            }

            // 현재 고정지출 데이터 아카이브
            $fixedQuery = "
                INSERT INTO fixed_expenses_archive (archive_id, category, item_name, amount, payment_date, payment_method)
                SELECT ?, category, item_name, amount, payment_date, payment_method
                FROM fixed_expenses
                WHERE deleted_at IS NULL
            ";
            $stmt = $this->db->prepare($fixedQuery);
            $stmt->execute([$archiveId]);

            // 현재 선납지출 데이터 아카이브
            $prepaidQuery = "
                INSERT INTO prepaid_expenses_archive (archive_id, item_name, amount, payment_date, payment_method, expiry_date)
                SELECT ?, item_name, amount, payment_date, payment_method, expiry_date
                FROM prepaid_expenses
                WHERE deleted_at IS NULL
            ";
            $stmt = $this->db->prepare($prepaidQuery);
            $stmt->execute([$archiveId]);

            $this->db->commit();

            return json_encode([
                'success' => true,
                'message' => "{$year}년 {$month}월 지출 스냅샷이 생성되었습니다.",
                'data' => ['archive_id' => $archiveId]
            ], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error creating expense snapshot: " . $e->getMessage());
            return json_encode([
                'success' => false,
                'message' => '지출 스냅샷 생성에 실패했습니다: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * 아카이브된 지출 항목 수정
     */
    public function updateExpense($table, $id) {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                return json_encode([
                    'success' => false,
                    'message' => '입력 데이터가 필요합니다.'
                ], JSON_UNESCAPED_UNICODE);
            }

            $archiveTable = $table . '_archive';
            $allowedTables = ['fixed_expenses_archive', 'prepaid_expenses_archive'];

            if (!in_array($archiveTable, $allowedTables)) {
                return json_encode([
                    'success' => false,
                    'message' => '잘못된 테이블입니다.'
                ], JSON_UNESCAPED_UNICODE);
            }

            // 공통 필드
            $fields = ['item_name', 'amount', 'payment_date', 'payment_method'];
            $values = [];
            $placeholders = [];

            foreach ($fields as $field) {
                if (isset($input[$field])) {
                    $placeholders[] = "$field = ?";
                    $values[] = $input[$field];
                }
            }

            // 선납지출인 경우 추가 필드
            if ($archiveTable === 'prepaid_expenses_archive') {
                if (isset($input['expiry_date'])) {
                    $placeholders[] = "expiry_date = ?";
                    $values[] = $input['expiry_date'];
                }
            }

            // 고정지출인 경우 추가 필드
            if ($archiveTable === 'fixed_expenses_archive' && isset($input['category'])) {
                $placeholders[] = "category = ?";
                $values[] = $input['category'];
            }

            if (empty($placeholders)) {
                return json_encode([
                    'success' => false,
                    'message' => '수정할 데이터가 없습니다.'
                ], JSON_UNESCAPED_UNICODE);
            }

            $values[] = $id;
            $query = "UPDATE $archiveTable SET " . implode(', ', $placeholders) . " WHERE id = ?";

            $stmt = $this->db->prepare($query);
            $result = $stmt->execute($values);

            if ($result && $stmt->rowCount() > 0) {
                return json_encode([
                    'success' => true,
                    'message' => '아카이브 데이터가 수정되었습니다.'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                return json_encode([
                    'success' => false,
                    'message' => '해당 데이터를 찾을 수 없습니다.'
                ], JSON_UNESCAPED_UNICODE);
            }

        } catch (Exception $e) {
            error_log("Error updating archived expense: " . $e->getMessage());
            return json_encode([
                'success' => false,
                'message' => '데이터 수정에 실패했습니다: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * 사용 가능한 아카이브 월 목록을 assets.php 형식으로 반환
     */
    public function getAvailableMonths() {
        try {
            $query = "
                SELECT DISTINCT
                    DATE_FORMAT(ma.archive_month, '%Y-%m') as value,
                    CONCAT(YEAR(ma.archive_month), '년 ', MONTH(ma.archive_month), '월') as label,
                    ma.archive_month,
                    ma.created_at
                FROM monthly_archives ma
                WHERE EXISTS (
                    SELECT 1 FROM fixed_expenses_archive fea WHERE fea.archive_id = ma.id
                    UNION
                    SELECT 1 FROM prepaid_expenses_archive pea WHERE pea.archive_id = ma.id
                )
                ORDER BY ma.archive_month DESC
            ";

            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $months = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return json_encode([
                'success' => true,
                'data' => $months
            ], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            error_log("Error getting available archive months: " . $e->getMessage());
            return json_encode([
                'success' => false,
                'message' => '사용 가능한 아카이브 월 목록을 가져올 수 없습니다.'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * 특정 월의 아카이브 전체 삭제
     */
    public function deleteArchive($year, $month) {
        try {
            // 해당 연월에 대한 아카이브 ID 찾기
            $archiveQuery = "SELECT id FROM monthly_archives WHERE YEAR(archive_month) = ? AND MONTH(archive_month) = ?";
            $archiveStmt = $this->db->prepare($archiveQuery);
            $archiveStmt->execute([$year, $month]);
            $archive = $archiveStmt->fetch(PDO::FETCH_ASSOC);

            if (!$archive) {
                return json_encode([
                    'success' => false,
                    'message' => '해당 월의 아카이브를 찾을 수 없습니다.'
                ], JSON_UNESCAPED_UNICODE);
            }

            $archiveId = $archive['id'];

            // 트랜잭션 시작
            $this->db->beginTransaction();

            // 고정지출 아카이브 삭제
            $deleteFixedQuery = "DELETE FROM fixed_expenses_archive WHERE archive_id = ?";
            $deleteFixedStmt = $this->db->prepare($deleteFixedQuery);
            $deleteFixedStmt->execute([$archiveId]);

            // 선납지출 아카이브 삭제
            $deletePrepaidQuery = "DELETE FROM prepaid_expenses_archive WHERE archive_id = ?";
            $deletePrepaidStmt = $this->db->prepare($deletePrepaidQuery);
            $deletePrepaidStmt->execute([$archiveId]);

            // 월별 아카이브 메타 정보 삭제
            $deleteArchiveQuery = "DELETE FROM monthly_archives WHERE id = ?";
            $deleteArchiveStmt = $this->db->prepare($deleteArchiveQuery);
            $deleteArchiveStmt->execute([$archiveId]);

            // 트랜잭션 커밋
            $this->db->commit();

            return json_encode([
                'success' => true,
                'message' => $year . '년 ' . $month . '월 아카이브가 삭제되었습니다.'
            ], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            // 트랜잭션 롤백
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            error_log("Error deleting archive: " . $e->getMessage());
            return json_encode([
                'success' => false,
                'message' => '아카이브 삭제에 실패했습니다: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
?>
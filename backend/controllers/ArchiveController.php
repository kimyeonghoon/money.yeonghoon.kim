<?php

require_once __DIR__ . '/../models/MonthlyArchive.php';
require_once __DIR__ . '/../models/ArchiveData.php';
require_once __DIR__ . '/../lib/Database.php';

class ArchiveController {
    private $archiveModel;
    private $archiveDataModel;

    public function __construct() {
        $this->archiveModel = new MonthlyArchive();
        $this->archiveDataModel = new ArchiveData();
    }

    /**
     * 아카이브 가능한 월 목록 조회
     * GET /api/archive/months
     */
    public function getMonths() {
        try {
            $months = $this->archiveModel->getAllMonths();

            // 월 목록을 프론트엔드 친화적 형태로 변환
            $formattedMonths = [];
            foreach ($months as $month) {
                $formattedMonths[] = [
                    'value' => substr($month['archive_month'], 0, 7), // 2025-09
                    'label' => date('Y년 n월', strtotime($month['archive_month'])),
                    'archive_month' => $month['archive_month'],
                    'created_at' => $month['created_at'],
                    'last_modified' => $month['last_modified']
                ];
            }

            return $this->jsonResponse(true, '아카이브 월 목록 조회 성공', $formattedMonths);

        } catch (Exception $e) {
            error_log("Archive months retrieval failed: " . $e->getMessage());
            return $this->jsonResponse(false, '아카이브 월 목록 조회 실패: ' . $e->getMessage());
        }
    }

    /**
     * 특정 월의 현금 자산 아카이브 조회
     * GET /api/archive/cash-assets?month=2025-09
     */
    public function getCashAssets() {
        return $this->getAssetsByType('cash_assets');
    }

    /**
     * 특정 월의 투자 자산 아카이브 조회
     * GET /api/archive/investment-assets?month=2025-09
     */
    public function getInvestmentAssets() {
        return $this->getAssetsByType('investment_assets');
    }

    /**
     * 특정 월의 연금 자산 아카이브 조회
     * GET /api/archive/pension-assets?month=2025-09
     */
    public function getPensionAssets() {
        return $this->getAssetsByType('pension_assets');
    }

    /**
     * 공통 자산 조회 메서드
     */
    private function getAssetsByType($assetTable) {
        try {
            $month = $_GET['month'] ?? null;
            if (!$month) {
                return $this->jsonResponse(false, 'month 파라미터가 필요합니다');
            }

            // 2025-09 형태를 2025, 9로 분리
            $parts = explode('-', $month);
            if (count($parts) !== 2) {
                return $this->jsonResponse(false, '잘못된 월 형식입니다 (YYYY-MM)');
            }

            $year = (int)$parts[0];
            $monthNum = (int)$parts[1];

            // 아카이브 조회
            $archive = $this->archiveModel->getByMonth($year, $monthNum);
            if (!$archive) {
                return $this->jsonResponse(false, '해당 월의 아카이브를 찾을 수 없습니다');
            }

            // 자산 데이터 조회
            $assets = $this->archiveDataModel->getAssetsByArchiveAndTable($archive['id'], $assetTable);

            // 백분율 계산 (현재 데이터와 동일한 형태로)
            $assets = $this->calculatePercentages($assets, $assetTable);

            // 페이지네이션 정보 추가 (기존 API와 호환성 위해)
            $response = [
                'data' => $assets,
                'pagination' => [
                    'total' => count($assets),
                    'page' => 1,
                    'limit' => 20,
                    'pages' => 1,
                    'has_next' => false,
                    'has_previous' => false
                ],
                'archive_info' => [
                    'archive_month' => $archive['archive_month'],
                    'created_at' => $archive['created_at'],
                    'last_modified' => $archive['last_modified']
                ]
            ];

            return $this->jsonResponse(true, ucfirst(str_replace('_', ' ', $assetTable)) . ' 아카이브 조회 성공', $response);

        } catch (Exception $e) {
            error_log("Archive {$assetTable} retrieval failed: " . $e->getMessage());
            return $this->jsonResponse(false, '아카이브 조회 실패: ' . $e->getMessage());
        }
    }

    /**
     * 아카이브 자산 수정
     * PUT /api/archive/cash-assets/123?month=2025-09
     */
    public function updateAsset($assetTable, $assetId) {
        try {
            $month = $_GET['month'] ?? null;
            if (!$month) {
                return $this->jsonResponse(false, 'month 파라미터가 필요합니다');
            }

            $parts = explode('-', $month);
            $year = (int)$parts[0];
            $monthNum = (int)$parts[1];

            // 아카이브 조회
            $archive = $this->archiveModel->getByMonth($year, $monthNum);
            if (!$archive) {
                return $this->jsonResponse(false, '해당 월의 아카이브를 찾을 수 없습니다');
            }

            // 현재 자산 데이터 조회
            $assets = $this->archiveDataModel->getAssetsByArchiveAndTable($archive['id'], $assetTable);

            // 수정할 자산 찾기
            $targetIndex = -1;
            foreach ($assets as $index => $asset) {
                if ($asset['id'] == $assetId) {
                    $targetIndex = $index;
                    break;
                }
            }

            if ($targetIndex === -1) {
                return $this->jsonResponse(false, '수정할 자산을 찾을 수 없습니다');
            }

            // 입력 데이터 받기
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                return $this->jsonResponse(false, '잘못된 입력 데이터입니다');
            }

            // 자산 데이터 업데이트
            foreach ($input as $key => $value) {
                if (isset($assets[$targetIndex][$key])) {
                    $assets[$targetIndex][$key] = $value;
                }
            }

            // 수정 시간 업데이트
            $assets[$targetIndex]['updated_at'] = date('Y-m-d H:i:s');

            // 아카이브 데이터 교체
            $result = $this->archiveDataModel->replaceAssetData($archive['id'], $assetTable, $assets);

            if (!$result) {
                return $this->jsonResponse(false, '아카이브 수정 실패');
            }

            // 집계 캐시 업데이트
            $this->archiveDataModel->updateSummaryCache($archive['id']);

            // 아카이브 수정 정보 업데이트 (직접 SQL로 처리)
            $db = Database::getInstance();
            $sql = "UPDATE monthly_archives SET modification_notes = ?, last_modified = CURRENT_TIMESTAMP WHERE id = ?";
            $db->query($sql, ["자산 수정: {$assetTable} ID {$assetId}", $archive['id']]);

            return $this->jsonResponse(true, '아카이브 수정 완료', $assets[$targetIndex]);

        } catch (Exception $e) {
            error_log("Archive asset update failed: " . $e->getMessage());
            return $this->jsonResponse(false, '아카이브 수정 실패: ' . $e->getMessage());
        }
    }

    /**
     * 현재 데이터로 새 아카이브 생성
     * POST /api/archive/create-snapshot?month=2025-09
     */
    public function createSnapshot() {
        try {
            // 두 가지 형식 지원: ?month=2025-09 또는 ?year=2025&month=9
            $monthParam = $_GET['month'] ?? null;
            $yearParam = $_GET['year'] ?? null;

            if ($monthParam && strpos($monthParam, '-') !== false) {
                // 형식: ?month=2025-09
                $parts = explode('-', $monthParam);
                $year = (int)$parts[0];
                $monthNum = (int)($parts[1] ?? 0);
            } elseif ($yearParam && $monthParam) {
                // 형식: ?year=2025&month=9
                $year = (int)$yearParam;
                $monthNum = (int)$monthParam;
            } else {
                return $this->jsonResponse(false, 'month 파라미터가 필요합니다 (예: ?month=2025-09 또는 ?year=2025&month=9)');
            }

            if ($year < 2000 || $year > 2100 || $monthNum < 1 || $monthNum > 12) {
                return $this->jsonResponse(false, '유효하지 않은 년도 또는 월입니다');
            }

            // 아카이브 생성 또는 업데이트
            $archiveId = $this->archiveModel->createOrUpdate($year, $monthNum, "현재 데이터 기반 스냅샷 생성");

            // 현재 데이터로 아카이브 생성
            $result = $this->archiveDataModel->createArchiveFromCurrentData($archiveId);

            if (!$result) {
                return $this->jsonResponse(false, '스냅샷 생성 실패');
            }

            // 집계 캐시 업데이트
            $this->archiveDataModel->updateSummaryCache($archiveId);

            return $this->jsonResponse(true, '스냅샷 생성 완료', [
                'archive_id' => $archiveId,
                'month' => sprintf('%04d-%02d', $year, $monthNum),
                'created_at' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            error_log("Snapshot creation failed: " . $e->getMessage());
            return $this->jsonResponse(false, '스냅샷 생성 실패: ' . $e->getMessage());
        }
    }

    /**
     * 백분율 계산 (기존 API와 동일한 로직)
     */
    private function calculatePercentages($assets, $assetTable) {
        if (empty($assets)) {
            return $assets;
        }

        // 총액 계산
        $totalField = ($assetTable === 'cash_assets') ? 'balance' : 'current_value';
        $total = array_sum(array_column($assets, $totalField));

        if ($total <= 0) {
            return $assets;
        }

        // 각 자산에 백분율 추가
        foreach ($assets as &$asset) {
            $value = $asset[$totalField] ?? 0;
            $asset['percentage'] = round(($value / $total) * 100, 2);
        }

        return $assets;
    }

    /**
     * 아카이브 삭제
     * DELETE /api/archive/delete?month=2025-09
     */
    public function deleteArchive() {
        try {
            // 월 파라미터 받기
            $monthParam = $_GET['month'] ?? null;
            $yearParam = $_GET['year'] ?? null;

            if ($monthParam && strpos($monthParam, '-') !== false) {
                $parts = explode('-', $monthParam);
                $year = (int)$parts[0];
                $monthNum = (int)($parts[1] ?? 0);
            } elseif ($yearParam && $monthParam) {
                $year = (int)$yearParam;
                $monthNum = (int)$monthParam;
            } else {
                return $this->jsonResponse(false, 'month 파라미터가 필요합니다 (예: ?month=2025-09)');
            }

            $archiveMonth = sprintf('%04d-%02d-01', $year, $monthNum);

            // 아카이브 존재 확인
            $db = Database::getInstance();
            $stmt = $db->query("SELECT id FROM monthly_archives WHERE archive_month = ?", [$archiveMonth]);
            $archive = $stmt->fetch();

            if (!$archive) {
                return $this->jsonResponse(false, '해당 월의 아카이브를 찾을 수 없습니다');
            }

            $archiveId = $archive['id'];

            // 관련 데이터 모두 삭제 (archive_id 기준)
            $db->query("DELETE FROM assets_archive_data WHERE archive_id = ?", [$archiveId]);

            // 오래된 스키마 테이블도 체크 (존재하면 삭제)
            try {
                $db->query("DELETE FROM cash_assets_archive WHERE snapshot_month = ?", [$archiveMonth]);
            } catch (Exception $e) {
                // 테이블이 없으면 무시
            }
            try {
                $db->query("DELETE FROM investment_assets_archive WHERE snapshot_month = ?", [$archiveMonth]);
            } catch (Exception $e) {
                // 테이블이 없으면 무시
            }
            try {
                $db->query("DELETE FROM pension_assets_archive WHERE snapshot_month = ?", [$archiveMonth]);
            } catch (Exception $e) {
                // 테이블이 없으면 무시
            }
            try {
                $db->query("DELETE FROM fixed_expenses_archive WHERE snapshot_month = ?", [$archiveMonth]);
            } catch (Exception $e) {
                // 테이블이 없으면 무시
            }
            try {
                $db->query("DELETE FROM prepaid_expenses_archive WHERE snapshot_month = ?", [$archiveMonth]);
            } catch (Exception $e) {
                // 테이블이 없으면 무시
            }
            try {
                $db->query("DELETE FROM archive_summary_cache WHERE archive_month = ?", [$archiveMonth]);
            } catch (Exception $e) {
                // 테이블이 없으면 무시
            }

            // 메인 아카이브 레코드 삭제
            $db->query("DELETE FROM monthly_archives WHERE id = ?", [$archiveId]);

            return $this->jsonResponse(true, '아카이브가 삭제되었습니다', [
                'month' => sprintf('%04d-%02d', $year, $monthNum),
                'archive_id' => $archiveId
            ]);

        } catch (Exception $e) {
            error_log("Archive deletion failed: " . $e->getMessage());
            return $this->jsonResponse(false, '아카이브 삭제 실패: ' . $e->getMessage());
        }
    }

    /**
     * JSON 응답 생성
     */
    private function jsonResponse($success, $message, $data = null) {
        $response = [
            'success' => $success,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        header('Content-Type: application/json; charset=utf-8');
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }
}
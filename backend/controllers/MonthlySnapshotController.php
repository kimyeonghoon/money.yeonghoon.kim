<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/AssetsMonthlySnapshot.php';
require_once __DIR__ . '/../models/ExpensesMonthlySummary.php';
require_once __DIR__ . '/../lib/Response.php';

class MonthlySnapshotController extends BaseController {

    private $assetsModel;
    private $expensesModel;

    public function __construct() {
        $this->assetsModel = new AssetsMonthlySnapshot();
        $this->expensesModel = new ExpensesMonthlySummary();
    }

    public function handleRequest() {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $segments = explode('/', trim($path, '/'));

        if (isset($segments[2])) {
            $action = $segments[2];
            $id = $segments[3] ?? null;

            switch ($action) {
                case 'create':
                    $this->createSnapshot();
                    break;
                case 'assets':
                    if ($id) {
                        $this->getAssetSnapshot($id);
                    } else {
                        $this->getAssetSnapshots();
                    }
                    break;
                case 'expenses':
                    if ($id) {
                        $this->getExpenseSummary($id);
                    } else {
                        $this->getExpenseSummaries();
                    }
                    break;
                case 'update-assets':
                    if ($id) {
                        $this->updateAssetSnapshot($id);
                    } else {
                        Response::error('Snapshot ID is required', 400);
                    }
                    break;
                case 'update-expenses':
                    if ($id) {
                        $this->updateExpenseSummary($id);
                    } else {
                        Response::error('Summary ID is required', 400);
                    }
                    break;
                case 'by-month':
                    $this->getSnapshotByMonth();
                    break;
                case 'comparison':
                    $this->getYearlyComparison();
                    break;
                default:
                    Response::error('Invalid action', 400);
            }
        } else {
            $this->getOverview();
        }
    }

    private function createSnapshot() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Method not allowed', 405);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            Response::error('Invalid JSON data', 400);
            return;
        }

        $year = $data['year'] ?? date('Y');
        $month = $data['month'] ?? date('n');

        try {
            // 자산 스냅샷 생성
            $assetsResult = $this->assetsModel->createMonthlySnapshot($year, $month);

            // 지출 요약 생성
            $expensesResult = $this->expensesModel->createMonthlySummary($year, $month);

            if ($assetsResult && $expensesResult) {
                Response::success([
                    'assets_snapshot' => $assetsResult,
                    'expenses_summary' => $expensesResult,
                    'snapshot_month' => sprintf('%04d-%02d', $year, $month)
                ], 'Monthly snapshot created successfully');
            } else {
                Response::error('Failed to create snapshot', 500);
            }

        } catch (Exception $e) {
            error_log('Snapshot creation error: ' . $e->getMessage());
            Response::error('Internal server error', 500);
        }
    }

    private function getAssetSnapshots() {
        $params = $this->getQueryParams();
        $year = $params['year'] ?? date('Y');

        if (isset($params['start_year']) && isset($params['end_year'])) {
            $snapshots = $this->assetsModel->getByYearRange($params['start_year'], $params['end_year']);
        } else {
            $snapshots = $this->assetsModel->getByYearRange($year, $year);
        }

        Response::success($snapshots, 'Asset snapshots retrieved');
    }

    private function getAssetSnapshot($id) {
        $snapshot = $this->assetsModel->findById($id);

        if ($snapshot) {
            Response::success($snapshot, 'Asset snapshot retrieved');
        } else {
            Response::error('Snapshot not found', 404);
        }
    }

    private function getExpenseSummaries() {
        $params = $this->getQueryParams();
        $year = $params['year'] ?? date('Y');

        if (isset($params['start_year']) && isset($params['end_year'])) {
            $summaries = $this->expensesModel->getByYearRange($params['start_year'], $params['end_year']);
        } else {
            $summaries = $this->expensesModel->getByYearRange($year, $year);
        }

        Response::success($summaries, 'Expense summaries retrieved');
    }

    private function getExpenseSummary($id) {
        $summary = $this->expensesModel->findById($id);

        if ($summary) {
            Response::success($summary, 'Expense summary retrieved');
        } else {
            Response::error('Summary not found', 404);
        }
    }

    private function updateAssetSnapshot($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            Response::error('Method not allowed', 405);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            Response::error('Invalid JSON data', 400);
            return;
        }

        $result = $this->assetsModel->updateSnapshot($id, $data);

        if ($result) {
            Response::success($result, 'Asset snapshot updated successfully');
        } else {
            Response::error('Failed to update snapshot', 500);
        }
    }

    private function updateExpenseSummary($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            Response::error('Method not allowed', 405);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            Response::error('Invalid JSON data', 400);
            return;
        }

        $result = $this->expensesModel->updateSummary($id, $data);

        if ($result) {
            Response::success($result, 'Expense summary updated successfully');
        } else {
            Response::error('Failed to update summary', 500);
        }
    }

    private function getSnapshotByMonth() {
        $params = $this->getQueryParams();
        $year = $params['year'] ?? date('Y');
        $month = $params['month'] ?? date('n');

        $assetSnapshots = $this->assetsModel->getByMonth($year, $month);
        $expenseSummary = $this->expensesModel->getByMonth($year, $month);

        Response::success([
            'snapshot_month' => sprintf('%04d-%02d', $year, $month),
            'assets' => $assetSnapshots,
            'expenses' => $expenseSummary
        ], 'Monthly snapshot retrieved');
    }

    private function getYearlyComparison() {
        $params = $this->getQueryParams();
        $year = $params['year'] ?? date('Y');

        $expenseComparison = $this->expensesModel->getYearlyComparison($year);
        $assetSnapshots = $this->assetsModel->getByYearRange($year, $year);

        Response::success([
            'year' => $year,
            'expenses_by_month' => $expenseComparison,
            'assets_by_month' => $assetSnapshots
        ], 'Yearly comparison data retrieved');
    }

    private function getOverview() {
        $currentYear = date('Y');
        $currentMonth = date('n');

        $recentAssets = $this->assetsModel->getByMonth($currentYear, $currentMonth);
        $recentExpenses = $this->expensesModel->getByMonth($currentYear, $currentMonth);

        Response::success([
            'current_month' => sprintf('%04d-%02d', $currentYear, $currentMonth),
            'has_current_snapshot' => !empty($recentAssets) || !empty($recentExpenses),
            'assets_snapshot' => $recentAssets,
            'expenses_summary' => $recentExpenses
        ], 'Snapshot overview retrieved');
    }

    protected function validateData($data, $id = null) {
        // 기본 검증만 수행 (BaseController 메서드 구현 요구사항)
        return true;
    }
}
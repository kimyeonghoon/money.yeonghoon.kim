<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/DailyExpense.php';
require_once __DIR__ . '/../lib/Validator.php';

class DailyExpenseController extends BaseController {

    public function __construct() {
        parent::__construct(new DailyExpense());
    }

    protected function validateData($data, $id = null) {
        return Validator::validateDailyExpense($data);
    }

    public function handleRequest() {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $segments = explode('/', trim($path, '/'));

        if (isset($segments[2])) {
            $action = $segments[2];
            $id = $segments[3] ?? null;

            switch ($action) {
                case 'by-date':
                    $this->getByDate();
                    break;
                case 'by-month':
                    $this->getByMonth();
                    break;
                case 'monthly-total':
                    $this->getMonthlyTotal();
                    break;
                case 'yearly-total':
                    $this->getYearlyTotal();
                    break;
                case 'average':
                    $this->getDailyAverage();
                    break;
                case 'recent':
                    $this->getRecentExpenses();
                    break;
                case 'category-breakdown':
                    $this->getCategoryBreakdown();
                    break;
                case 'statistics':
                    $this->getStatistics();
                    break;
                case 'add-today':
                    $this->addToday();
                    break;
                case 'deleted':
                    $this->getDeleted();
                    break;
                case 'restore':
                    if (!$id) {
                        Response::error('ID is required', 400);
                    }
                    $this->restore($id);
                    break;
                default:
                    parent::handleRequest();
            }
        } else {
            parent::handleRequest();
        }
    }

    private function getByDate() {
        $params = $this->getQueryParams();
        $date = $params['date'] ?? null;

        if (!$date) {
            Response::error('Date parameter is required (YYYY-MM-DD)', 400);
        }

        $expense = $this->model->findByDate($date);

        if (!$expense) {
            Response::success(null, 'No expense found for this date');
        } else {
            Response::success($expense, 'Daily expense retrieved');
        }
    }

    private function getByMonth() {
        $params = $this->getQueryParams();
        $year = $params['year'] ?? date('Y');
        $month = $params['month'] ?? date('n');

        $pagination = Pagination::fromRequest($params);
        $expenses = $this->model->getByMonth($year, $month, $pagination->getLimit(), $pagination->getOffset());

        Response::success($expenses, "Expenses for {$year}-{$month} retrieved");
    }

    private function getMonthlyTotal() {
        $params = $this->getQueryParams();
        $year = $params['year'] ?? date('Y');
        $month = $params['month'] ?? date('n');

        $total = $this->model->getMonthlyTotal($year, $month);

        Response::success($total, "Monthly total for {$year}-{$month} retrieved");
    }

    private function getYearlyTotal() {
        $params = $this->getQueryParams();
        $year = $params['year'] ?? date('Y');

        $totals = $this->model->getYearlyTotal($year);

        Response::success($totals, "Yearly totals for {$year} retrieved");
    }

    private function getDailyAverage() {
        $params = $this->getQueryParams();
        $startDate = $params['start_date'] ?? null;
        $endDate = $params['end_date'] ?? null;

        if (!$startDate || !$endDate) {
            Response::error('start_date and end_date parameters are required', 400);
        }

        $average = $this->model->getDailyAverage($startDate, $endDate);

        Response::success($average, 'Daily averages retrieved');
    }

    private function getRecentExpenses() {
        $params = $this->getQueryParams();
        $days = $params['days'] ?? 7;
        $limit = $params['limit'] ?? null;

        $expenses = $this->model->getRecentExpenses($days, $limit);

        Response::success($expenses, "Recent expenses for last {$days} days retrieved");
    }

    private function getCategoryBreakdown() {
        $params = $this->getQueryParams();
        $startDate = $params['start_date'] ?? null;
        $endDate = $params['end_date'] ?? null;

        if (!$startDate || !$endDate) {
            Response::error('start_date and end_date parameters are required', 400);
        }

        $breakdown = $this->model->getCategoryBreakdown($startDate, $endDate);

        Response::success($breakdown, 'Category breakdown retrieved');
    }

    private function getStatistics() {
        $params = $this->getQueryParams();
        $today = $params['today'] ?? date('Y-m-d');
        $weekStart = $params['week_start'] ?? null;
        $monthStart = $params['month_start'] ?? null;

        // 오늘 지출
        $todayExpense = $this->model->findByDate($today);
        $todayTotal = $todayExpense ? $todayExpense['total_amount'] : 0;

        // 이번 주 지출 (week_start부터 today까지)
        $weekTotal = 0;
        if ($weekStart) {
            $weekExpenses = $this->model->getByDateRange($weekStart, $today);
            $weekTotal = array_sum(array_column($weekExpenses, 'total_amount'));
        }

        // 이번 달 지출 (month_start부터 today까지)
        $monthTotal = 0;
        if ($monthStart) {
            $monthExpenses = $this->model->getByDateRange($monthStart, $today);
            $monthTotal = array_sum(array_column($monthExpenses, 'total_amount'));
        }

        $statistics = [
            'today' => (int)$todayTotal,
            'week' => (int)$weekTotal,
            'month' => (int)$monthTotal
        ];

        Response::success($statistics, 'Statistics retrieved');
    }

    private function addToday() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Method not allowed', 405);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            Response::error('Invalid JSON data', 400);
            return;
        }

        $expenseDate = $data['expense_date'] ?? date('Y-m-d');
        $foodCost = (int)($data['food_cost'] ?? 0);
        $necessitiesCost = (int)($data['necessities_cost'] ?? 0);
        $transportationCost = (int)($data['transportation_cost'] ?? 0);
        $otherCost = (int)($data['other_cost'] ?? 0);

        // 기존 데이터 확인
        $existingExpense = $this->model->findByDate($expenseDate);

        if ($existingExpense) {
            // 기존 데이터에 누적
            $newData = [
                'food_cost' => $existingExpense['food_cost'] + $foodCost,
                'necessities_cost' => $existingExpense['necessities_cost'] + $necessitiesCost,
                'transportation_cost' => $existingExpense['transportation_cost'] + $transportationCost,
                'other_cost' => $existingExpense['other_cost'] + $otherCost
            ];

            $newData['total_amount'] = $newData['food_cost'] + $newData['necessities_cost'] +
                                     $newData['transportation_cost'] + $newData['other_cost'];

            $result = $this->model->update($existingExpense['id'], $newData);

            if ($result) {
                Response::success($result, 'Expense added to existing record');
            } else {
                Response::error('Failed to update expense', 500);
            }
        } else {
            // 새 데이터 생성
            $newData = [
                'expense_date' => $expenseDate,
                'food_cost' => $foodCost,
                'necessities_cost' => $necessitiesCost,
                'transportation_cost' => $transportationCost,
                'other_cost' => $otherCost,
                'total_amount' => $foodCost + $necessitiesCost + $transportationCost + $otherCost
            ];

            $result = $this->model->create($newData);

            if ($result) {
                Response::success($result, 'New expense record created');
            } else {
                Response::error('Failed to create expense', 500);
            }
        }
    }
}
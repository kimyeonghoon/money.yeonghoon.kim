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
}
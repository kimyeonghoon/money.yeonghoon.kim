<?php

require_once __DIR__ . '/../models/CashAsset.php';
require_once __DIR__ . '/../models/InvestmentAsset.php';
require_once __DIR__ . '/../models/PensionAsset.php';
require_once __DIR__ . '/../models/DailyExpense.php';
require_once __DIR__ . '/../models/FixedExpense.php';
require_once __DIR__ . '/../models/PrepaidExpense.php';
require_once __DIR__ . '/../lib/Response.php';

class DashboardController {
    private $cashAssetModel;
    private $investmentAssetModel;
    private $pensionAssetModel;
    private $dailyExpenseModel;
    private $fixedExpenseModel;
    private $prepaidExpenseModel;

    public function __construct() {
        $this->cashAssetModel = new CashAsset();
        $this->investmentAssetModel = new InvestmentAsset();
        $this->pensionAssetModel = new PensionAsset();
        $this->dailyExpenseModel = new DailyExpense();
        $this->fixedExpenseModel = new FixedExpense();
        $this->prepaidExpenseModel = new PrepaidExpense();

        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    public function handleRequest() {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $segments = explode('/', trim($path, '/'));

        if (isset($segments[2])) {
            $action = $segments[2];

            switch ($action) {
                case 'summary':
                    $this->getSummary();
                    break;
                case 'assets':
                    $this->getAssetsSummary();
                    break;
                case 'expenses':
                    $this->getExpensesSummary();
                    break;
                case 'monthly-overview':
                    $this->getMonthlyOverview();
                    break;
                default:
                    Response::error('Invalid dashboard endpoint', 404);
            }
        } else {
            $this->getSummary();
        }
    }

    private function getSummary() {
        try {
            $cashTotal = $this->cashAssetModel->getTotalBalance();
            $investmentTotal = $this->investmentAssetModel->getTotalValue();
            $pensionTotal = $this->pensionAssetModel->getTotalValue();
            $totalAssets = $cashTotal + $investmentTotal + $pensionTotal;

            $currentMonth = date('Y-m');
            $thisYear = date('Y');
            $thisMonth = date('n');

            $monthlyExpenses = $this->dailyExpenseModel->getMonthlyTotal($thisYear, $thisMonth);
            $fixedExpensesTotal = $this->fixedExpenseModel->getTotalMonthlyAmount();
            $prepaidExpensesTotal = $this->prepaidExpenseModel->getTotalActiveAmount();

            $recentExpenses = $this->dailyExpenseModel->getRecentExpenses(7, 5);
            $upcomingPayments = $this->fixedExpenseModel->getUpcomingPayments(7);
            $expiringSoon = $this->prepaidExpenseModel->getExpiringSoon(30);

            $summary = [
                'total_assets' => $totalAssets,
                'assets_breakdown' => [
                    'cash' => $cashTotal,
                    'investment' => $investmentTotal,
                    'pension' => $pensionTotal
                ],
                'monthly_expenses' => [
                    'daily_total' => $monthlyExpenses['total'] ?? 0,
                    'fixed_total' => $fixedExpensesTotal,
                    'prepaid_total' => $prepaidExpensesTotal
                ],
                'recent_activities' => [
                    'recent_expenses' => $recentExpenses,
                    'upcoming_payments' => $upcomingPayments,
                    'expiring_soon' => $expiringSoon
                ],
                'investment_return_rate' => $this->investmentAssetModel->getReturnRate(),
                'pension_return_rate' => $this->pensionAssetModel->getReturnRate(),
                'generated_at' => date('Y-m-d H:i:s')
            ];

            Response::success($summary, 'Dashboard summary retrieved');

        } catch (Exception $e) {
            Response::serverError('Failed to generate dashboard summary: ' . $e->getMessage());
        }
    }

    private function getAssetsSummary() {
        try {
            $cashAssets = $this->cashAssetModel->getTotalByType();
            $investmentAssets = $this->investmentAssetModel->getTotalByCategory();
            $pensionAssets = $this->pensionAssetModel->getTotalByType();

            $investmentReturnRates = $this->investmentAssetModel->getReturnRateByCategory();
            $pensionReturnRates = $this->pensionAssetModel->getReturnRateByType();

            $summary = [
                'cash_assets' => $cashAssets,
                'investment_assets' => [
                    'breakdown' => $investmentAssets,
                    'return_rates' => $investmentReturnRates
                ],
                'pension_assets' => [
                    'breakdown' => $pensionAssets,
                    'return_rates' => $pensionReturnRates
                ],
                'total_values' => [
                    'cash' => $this->cashAssetModel->getTotalBalance(),
                    'investment' => $this->investmentAssetModel->getTotalValue(),
                    'pension' => $this->pensionAssetModel->getTotalValue()
                ]
            ];

            Response::success($summary, 'Assets summary retrieved');

        } catch (Exception $e) {
            Response::serverError('Failed to generate assets summary: ' . $e->getMessage());
        }
    }

    private function getExpensesSummary() {
        try {
            $params = $_GET;
            $year = $params['year'] ?? date('Y');
            $month = $params['month'] ?? date('n');

            $monthlyTotal = $this->dailyExpenseModel->getMonthlyTotal($year, $month);
            $categoryBreakdown = $this->dailyExpenseModel->getCategoryBreakdown(
                sprintf('%04d-%02d-01', $year, $month),
                date('Y-m-t', strtotime(sprintf('%04d-%02d-01', $year, $month)))
            );

            $fixedExpensesByCategory = $this->fixedExpenseModel->getByCategory();
            $fixedExpensesByPaymentMethod = $this->fixedExpenseModel->getPaymentMethodTotals();

            $prepaidExpensesByPaymentMethod = $this->prepaidExpenseModel->getPaymentMethodTotals();
            $expiryStatus = $this->prepaidExpenseModel->getExpiryStatus();

            $summary = [
                'period' => sprintf('%04d-%02d', $year, $month),
                'daily_expenses' => [
                    'monthly_total' => $monthlyTotal,
                    'category_breakdown' => $categoryBreakdown
                ],
                'fixed_expenses' => [
                    'by_category' => $fixedExpensesByCategory,
                    'by_payment_method' => $fixedExpensesByPaymentMethod,
                    'total_monthly' => $this->fixedExpenseModel->getTotalMonthlyAmount()
                ],
                'prepaid_expenses' => [
                    'by_payment_method' => $prepaidExpensesByPaymentMethod,
                    'expiry_status' => $expiryStatus,
                    'total_active' => $this->prepaidExpenseModel->getTotalActiveAmount()
                ]
            ];

            Response::success($summary, 'Expenses summary retrieved');

        } catch (Exception $e) {
            Response::serverError('Failed to generate expenses summary: ' . $e->getMessage());
        }
    }

    private function getMonthlyOverview() {
        try {
            $params = $_GET;
            $year = $params['year'] ?? date('Y');

            $yearlyExpenses = $this->dailyExpenseModel->getYearlyTotal($year);
            $totalAssets = $this->cashAssetModel->getTotalBalance() +
                          $this->investmentAssetModel->getTotalValue() +
                          $this->pensionAssetModel->getTotalValue();

            $monthlyData = [];
            for ($month = 1; $month <= 12; $month++) {
                $monthlyExpense = array_filter($yearlyExpenses, function($item) use ($month) {
                    return $item['month'] == $month;
                });

                $monthlyData[] = [
                    'month' => $month,
                    'expenses' => !empty($monthlyExpense) ? reset($monthlyExpense) : null,
                    'fixed_expenses_total' => $this->fixedExpenseModel->getTotalMonthlyAmount()
                ];
            }

            $overview = [
                'year' => $year,
                'total_assets' => $totalAssets,
                'monthly_data' => $monthlyData,
                'investment_performance' => [
                    'overall_return_rate' => $this->investmentAssetModel->getReturnRate(),
                    'by_category' => $this->investmentAssetModel->getReturnRateByCategory()
                ],
                'pension_performance' => [
                    'overall_return_rate' => $this->pensionAssetModel->getReturnRate(),
                    'by_type' => $this->pensionAssetModel->getReturnRateByType()
                ]
            ];

            Response::success($overview, 'Monthly overview retrieved');

        } catch (Exception $e) {
            Response::serverError('Failed to generate monthly overview: ' . $e->getMessage());
        }
    }
}
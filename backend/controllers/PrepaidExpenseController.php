<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/PrepaidExpense.php';
require_once __DIR__ . '/../lib/Validator.php';

class PrepaidExpenseController extends BaseController {

    public function __construct() {
        parent::__construct(new PrepaidExpense());
    }

    protected function validateData($data, $id = null) {
        return Validator::validatePrepaidExpense($data);
    }

    public function handleRequest() {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $segments = explode('/', trim($path, '/'));

        if (isset($segments[2])) {
            $action = $segments[2];
            $id = $segments[3] ?? null;

            switch ($action) {
                case 'active':
                    $this->getActive();
                    break;
                case 'summary':
                    $this->getSummary();
                    break;
                case 'expiring-soon':
                    $this->getExpiringSoon();
                    break;
                case 'expired':
                    $this->getExpired();
                    break;
                case 'by-payment-date':
                    $this->getByPaymentDate();
                    break;
                case 'by-payment-method':
                    $this->getByPaymentMethod();
                    break;
                case 'upcoming':
                    $this->getUpcomingPayments();
                    break;
                case 'toggle-active':
                    if (!$id) {
                        Response::error('ID is required', 400);
                    }
                    $this->toggleActive($id);
                    break;
                case 'renew':
                    if (!$id) {
                        Response::error('ID is required', 400);
                    }
                    $this->renewExpiry($id);
                    break;
                case 'expiry-status':
                    $this->getExpiryStatus();
                    break;
                case 'search':
                    $this->search();
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

    private function getActive() {
        $params = $this->getQueryParams();
        $pagination = Pagination::fromRequest($params);

        $expenses = $this->model->getActive($pagination->getLimit(), $pagination->getOffset());

        Response::success($expenses, 'Active prepaid expenses retrieved');
    }

    private function getSummary() {
        $totalAmount = $this->model->getTotalActiveAmount();
        $paymentMethodTotals = $this->model->getPaymentMethodTotals();
        $expiryStatus = $this->model->getExpiryStatus();

        $summary = [
            'total_active_amount' => $totalAmount,
            'by_payment_method' => $paymentMethodTotals,
            'expiry_status' => $expiryStatus,
            'active_count' => count($this->model->getActive()),
            'total_count' => $this->model->count()
        ];

        Response::success($summary, 'Prepaid expenses summary retrieved');
    }

    private function getExpiringSoon() {
        $params = $this->getQueryParams();
        $days = $params['days'] ?? 30;

        $expenses = $this->model->getExpiringSoon($days);

        Response::success($expenses, "Expenses expiring within {$days} days retrieved");
    }

    private function getExpired() {
        $expenses = $this->model->getExpired();

        Response::success($expenses, 'Expired prepaid expenses retrieved');
    }

    private function getByPaymentDate() {
        $params = $this->getQueryParams();
        $date = $params['date'] ?? null;

        if (!$date) {
            Response::error('Date parameter is required (1-31)', 400);
        }

        if (!is_numeric($date) || $date < 1 || $date > 31) {
            Response::error('Date must be a number between 1 and 31', 400);
        }

        $expenses = $this->model->getByPaymentDate($date);

        Response::success($expenses, "Prepaid expenses for payment date {$date} retrieved");
    }

    private function getByPaymentMethod() {
        $params = $this->getQueryParams();
        $method = $params['method'] ?? null;

        if (!$method) {
            Response::error('Method parameter is required', 400);
        }

        if (!in_array($method, ['신용', '체크', '현금'])) {
            Response::error('Invalid payment method. Must be 신용, 체크, or 현금', 400);
        }

        $expenses = $this->model->getByPaymentMethod($method);

        Response::success($expenses, "Prepaid expenses with payment method {$method} retrieved");
    }

    private function getUpcomingPayments() {
        $params = $this->getQueryParams();
        $days = $params['days'] ?? 7;

        $expenses = $this->model->getUpcomingPayments($days);

        Response::success($expenses, "Upcoming payments for next {$days} days retrieved");
    }

    private function toggleActive($id) {
        if (!$this->model->exists($id)) {
            Response::notFound('Prepaid expense not found');
        }

        $toggled = $this->model->toggleActive($id);

        if (!$toggled) {
            Response::error('Failed to toggle active status', 500);
        }

        $item = $this->model->findById($id);
        Response::success($item, 'Active status toggled successfully');
    }

    private function renewExpiry($id) {
        if (!$this->model->exists($id)) {
            Response::notFound('Prepaid expense not found');
        }

        $data = $this->getRequestData();
        $newExpiryDate = $data['expiry_date'] ?? null;

        if (!$newExpiryDate) {
            Response::error('New expiry_date is required', 400);
        }

        $validator = new Validator();
        $validator->date($newExpiryDate, 'expiry_date');

        if ($validator->hasErrors()) {
            Response::validationError($validator->getErrors());
        }

        $renewed = $this->model->renewExpiry($id, $newExpiryDate);

        if (!$renewed) {
            Response::error('Failed to renew expiry date', 500);
        }

        $item = $this->model->findById($id);
        Response::success($item, 'Expiry date renewed successfully');
    }

    private function getExpiryStatus() {
        $status = $this->model->getExpiryStatus();

        Response::success($status, 'Expiry status summary retrieved');
    }

    private function search() {
        $params = $this->getQueryParams();
        $keyword = $params['q'] ?? '';

        if (empty($keyword)) {
            Response::error('Search keyword is required', 400);
        }

        $pagination = Pagination::fromRequest($params);
        $expenses = $this->model->searchByName($keyword, $pagination->getLimit(), $pagination->getOffset());

        Response::success($expenses, 'Search results retrieved');
    }
}
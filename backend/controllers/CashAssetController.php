<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/CashAsset.php';
require_once __DIR__ . '/../lib/Validator.php';

class CashAssetController extends BaseController {

    public function __construct() {
        parent::__construct(new CashAsset());
    }

    protected function validateData($data, $id = null) {
        return Validator::validateCashAsset($data);
    }

    public function handleRequest() {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $segments = explode('/', trim($path, '/'));

        if (isset($segments[2])) {
            $action = $segments[2];
            $id = $segments[3] ?? null;

            switch ($action) {
                case 'summary':
                    $this->getSummary();
                    break;
                case 'by-type':
                    $type = $this->getQueryParams()['type'] ?? null;
                    $this->getByType($type);
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

    private function getSummary() {
        $totalBalance = $this->model->getTotalBalance();
        $totalsByType = $this->model->getTotalByType();

        $summary = [
            'total_balance' => $totalBalance,
            'by_type' => $totalsByType,
            'count' => $this->model->count()
        ];

        Response::success($summary, 'Cash assets summary retrieved');
    }

    private function getByType($type) {
        if (!$type) {
            Response::error('Type parameter is required', 400);
        }

        if (!in_array($type, ['현금', '통장'])) {
            Response::error('Invalid type. Must be 현금 or 통장', 400);
        }

        $assets = $this->model->getByType($type);
        Response::success($assets, "Assets of type {$type} retrieved");
    }

    private function search() {
        $params = $this->getQueryParams();
        $keyword = $params['q'] ?? '';

        if (empty($keyword)) {
            Response::error('Search keyword is required', 400);
        }

        $pagination = Pagination::fromRequest($params);
        $assets = $this->model->searchByName($keyword, $pagination->getLimit(), $pagination->getOffset());

        Response::success($assets, 'Search results retrieved');
    }
}
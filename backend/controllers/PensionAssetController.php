<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/PensionAsset.php';
require_once __DIR__ . '/../lib/Validator.php';

class PensionAssetController extends BaseController {

    public function __construct() {
        parent::__construct(new PensionAsset());
    }

    protected function validateData($data, $id = null) {
        return Validator::validatePensionAsset($data);
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
                case 'return-rate':
                    $this->getReturnRate();
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
        $totalValue = $this->model->getTotalValue();
        $totalDeposit = $this->model->getTotalDeposit();
        $totalsByType = $this->model->getTotalByType();
        $returnRate = $this->model->getReturnRate();

        $summary = [
            'total_value' => $totalValue,
            'total_deposit' => $totalDeposit,
            'profit_loss' => $totalValue - $totalDeposit,
            'return_rate' => $returnRate,
            'by_type' => $totalsByType,
            'count' => $this->model->count()
        ];

        Response::success($summary, 'Pension assets summary retrieved');
    }

    private function getByType($type) {
        if (!$type) {
            Response::error('Type parameter is required', 400);
        }

        if (!in_array($type, ['연금저축', '퇴직연금'])) {
            Response::error('Invalid type. Must be 연금저축 or 퇴직연금', 400);
        }

        $assets = $this->model->getByType($type);
        Response::success($assets, "Assets of type {$type} retrieved");
    }

    private function getReturnRate() {
        $overallRate = $this->model->getReturnRate();
        $ratesByType = $this->model->getReturnRateByType();

        $data = [
            'overall_return_rate' => $overallRate,
            'by_type' => $ratesByType
        ];

        Response::success($data, 'Return rates retrieved');
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
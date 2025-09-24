<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/InvestmentAsset.php';
require_once __DIR__ . '/../lib/Validator.php';

class InvestmentAssetController extends BaseController {

    public function __construct() {
        parent::__construct(new InvestmentAsset());
    }

    protected function validateData($data, $id = null) {
        return Validator::validateInvestmentAsset($data);
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
                case 'by-category':
                    $category = $this->getQueryParams()['category'] ?? null;
                    $this->getByCategory($category);
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
        $totalsByCategory = $this->model->getTotalByCategory();
        $returnRate = $this->model->getReturnRate();

        $summary = [
            'total_value' => $totalValue,
            'total_deposit' => $totalDeposit,
            'profit_loss' => $totalValue - $totalDeposit,
            'return_rate' => $returnRate,
            'by_category' => $totalsByCategory,
            'count' => $this->model->count()
        ];

        Response::success($summary, 'Investment assets summary retrieved');
    }

    private function getByCategory($category) {
        if (!$category) {
            Response::error('Category parameter is required', 400);
        }

        if (!in_array($category, ['저축', '혼합', '주식'])) {
            Response::error('Invalid category. Must be 저축, 혼합, or 주식', 400);
        }

        $assets = $this->model->getByCategory($category);
        Response::success($assets, "Assets of category {$category} retrieved");
    }

    private function getReturnRate() {
        $overallRate = $this->model->getReturnRate();
        $ratesByCategory = $this->model->getReturnRateByCategory();

        $data = [
            'overall_return_rate' => $overallRate,
            'by_category' => $ratesByCategory
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
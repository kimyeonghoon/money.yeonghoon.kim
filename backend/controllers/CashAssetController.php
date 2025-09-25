<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/CashAsset.php';
require_once __DIR__ . '/../lib/Validator.php';

class CashAssetController extends BaseController {

    public function __construct() {
        parent::__construct(new CashAsset());
    }

    protected function validateData($data, $id = null) {
        return Validator::validateCashAsset($data, false);
    }

    protected function validateDataForPartialUpdate($data, $id = null) {
        return Validator::validateCashAsset($data, true);
    }

    public function handleRequest() {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $segments = explode('/', trim($path, '/'));


        if (isset($segments[2])) {
            $action = $segments[2];
            $id = $segments[3] ?? null;

            // reorder 액션은 ID가 필요없음
            if ($action === 'reorder') {
                $id = null;
            }

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
                case 'reorder':
                    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                        $this->updateOrder();
                    } else {
                        Response::error('Method not allowed for reorder', 405);
                    }
                    break;
                case 'balance':
                    if (!$id) {
                        Response::error('ID is required', 400);
                    }
                    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                        $this->updateBalance($id);
                    } else {
                        Response::error('Method not allowed for balance update', 405);
                    }
                    break;
                default:
                    // 기본 REST API 처리
                    $this->handleDefaultRequest($action, $id);
            }
        } else {
            // 기본 REST API 처리 (action 없음)
            $this->handleDefaultRequest(null, null);
        }
    }

    private function handleDefaultRequest($action, $id) {
        $method = $this->getRequestMethod();

        // action이 있으면서 숫자가 아닌 경우 (예: reorder)는 이미 위에서 처리됨
        // 여기서는 표준 REST API만 처리
        if ($action && !is_numeric($action)) {
            Response::error('Unknown action: ' . $action, 404);
            return;
        }

        // action이 숫자인 경우 이것이 실제 ID
        if (is_numeric($action)) {
            $id = $action;
        }

        switch ($method) {
            case 'GET':
                if (is_numeric($id)) {
                    $this->show($id);
                } else {
                    $this->index();
                }
                break;

            case 'POST':
                $this->store();
                break;

            case 'PUT':
                if (!is_numeric($id)) {
                    Response::error('ID is required for update', 400);
                }
                $this->update($id);
                break;

            case 'PATCH':
                if (!is_numeric($id)) {
                    Response::error('ID is required for partial update', 400);
                }
                $this->partialUpdate($id);
                break;

            case 'DELETE':
                if (!is_numeric($id)) {
                    Response::error('ID is required for delete', 400);
                }
                $this->destroy($id);
                break;

            default:
                Response::error('Method not allowed', 405);
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

        // 비중 계산 추가
        $totalBalance = $this->model->getTotalBalance();
        foreach ($assets as &$asset) {
            if ($totalBalance > 0) {
                $asset['percentage'] = round(($asset['balance'] / $totalBalance) * 100, 2);
            } else {
                $asset['percentage'] = 0;
            }
        }

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

    // 잔액만 업데이트하는 전용 메서드
    private function updateBalance($id) {
        if (!$this->model->exists($id)) {
            Response::notFound('Cash asset not found');
        }

        $data = $this->getRequestData();

        // 잔액 업데이트 전용 검증
        $validator = Validator::validateCashAssetForBalanceUpdate($data);
        if ($validator->hasErrors()) {
            Response::validationError($validator->getErrors());
        }

        $updated = $this->model->partialUpdate($id, ['balance' => $data['balance']]);

        if (!$updated) {
            Response::error('Failed to update balance', 500);
        }

        $asset = $this->model->getByIdWithPercentage($id);
        Response::success($asset, 'Balance updated successfully');
    }

    // 기본 조회시 비중 포함
    protected function index() {
        $params = $this->getQueryParams();
        $pagination = Pagination::fromRequest($params);

        $total = $this->model->count();
        $data = $this->model->getAllWithPercentage();

        $pagination = new Pagination($pagination->getPage(), $pagination->getLimit(), $total);

        Response::success([
            'data' => $data,
            'pagination' => $pagination->toArray()
        ], 'Cash assets retrieved successfully');
    }

    // 개별 조회시 비중 포함
    protected function show($id) {
        $asset = $this->model->getByIdWithPercentage($id);

        if (!$asset) {
            Response::notFound('Cash asset not found');
        }

        Response::success($asset, 'Cash asset retrieved successfully');
    }

    // PUT 메서드를 부분 업데이트로 처리
    protected function update($id) {
        if (!$this->model->exists($id)) {
            Response::notFound('Cash asset not found');
        }

        $data = $this->getRequestData();

        // 입력된 필드만 있으면 부분 업데이트로 처리
        $hasOnlyBalance = (count($data) === 1 && isset($data['balance']));

        if ($hasOnlyBalance) {
            // 잔액만 업데이트하는 경우
            $validator = Validator::validateCashAssetForBalanceUpdate($data);
            if ($validator->hasErrors()) {
                Response::validationError($validator->getErrors());
            }

            $updated = $this->model->partialUpdate($id, ['balance' => $data['balance']]);

            if (!$updated) {
                Response::error('Failed to update balance', 500);
            }

            $asset = $this->model->getByIdWithPercentage($id);
            Response::success($asset, 'Balance updated successfully');
        } else {
            // 전체 업데이트는 부모 메서드 호출
            parent::update($id);
        }
    }

    private function updateOrder() {
        $data = $this->getRequestData();

        if (!isset($data['orders']) || !is_array($data['orders'])) {
            Response::error('Orders array is required', 400);
        }

        $orderData = [];
        foreach ($data['orders'] as $index => $item) {
            if (!isset($item['id'])) {
                Response::error('Asset ID is required for each item', 400);
            }

            $orderData[] = [
                'id' => $item['id'],
                'order' => $index + 1  // 1부터 시작하는 순서
            ];
        }

        $success = $this->model->updateDisplayOrders($orderData);

        if ($success) {
            Response::success(null, 'Order updated successfully');
        } else {
            Response::error('Failed to update order', 500);
        }
    }
}
<?php

require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Pagination.php';
require_once __DIR__ . '/../lib/Auth.php';

abstract class BaseController {
    protected $model;

    public function __construct($model) {
        $this->model = $model;
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: http://localhost:3001');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Credentials: true');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        // API 인증 확인
        Auth::requireApiAuth();
    }

    protected function getRequestMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }

    protected function getRequestData() {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?: [];
    }

    protected function getQueryParams() {
        return $_GET;
    }

    protected function getId() {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $segments = explode('/', trim($path, '/'));
        return end($segments);
    }

    public function handleRequest() {
        try {
            $method = $this->getRequestMethod();
            $id = $this->getId();

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
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }

    protected function index() {
        $params = $this->getQueryParams();
        $pagination = Pagination::fromRequest($params);

        $total = $this->model->count();
        $data = $this->model->findAll($pagination->getLimit(), $pagination->getOffset());

        $pagination = new Pagination($pagination->getPage(), $pagination->getLimit(), $total);

        Response::success($data, 'Success', $pagination->toArray());
    }

    protected function show($id) {
        $item = $this->model->findById($id);

        if (!$item) {
            Response::notFound('Item not found');
        }

        Response::success($item);
    }

    protected function store() {
        $data = $this->getRequestData();

        $validator = $this->validateData($data);
        if ($validator->hasErrors()) {
            Response::validationError($validator->getErrors());
        }

        $id = $this->model->create($data);
        $item = $this->model->findById($id);

        Response::created($item, 'Item created successfully');
    }

    protected function update($id) {
        if (!$this->model->exists($id)) {
            Response::notFound('Item not found');
        }

        $data = $this->getRequestData();

        $validator = $this->validateData($data, $id);
        if ($validator->hasErrors()) {
            Response::validationError($validator->getErrors());
        }

        $updated = $this->model->update($id, $data);

        if (!$updated) {
            Response::error('Failed to update item', 500);
        }

        $item = $this->model->findById($id);
        Response::success($item, 'Item updated successfully');
    }

    protected function destroy($id) {
        if (!$this->model->exists($id)) {
            Response::notFound('Item not found');
        }

        $deleted = $this->model->softDelete($id);

        if (!$deleted) {
            Response::error('Failed to delete item', 500);
        }

        Response::success(null, 'Item deleted successfully');
    }

    protected function restore($id) {
        $restored = $this->model->restore($id);

        if (!$restored) {
            Response::error('Failed to restore item', 500);
        }

        $item = $this->model->findById($id);
        Response::success($item, 'Item restored successfully');
    }

    protected function forceDelete($id) {
        $deleted = $this->model->forceDelete($id);

        if (!$deleted) {
            Response::error('Failed to permanently delete item', 500);
        }

        Response::success(null, 'Item permanently deleted');
    }

    protected function partialUpdate($id) {
        if (!$this->model->exists($id)) {
            Response::notFound('Item not found');
        }

        $data = $this->getRequestData();

        // 부분 업데이트용 검증 사용
        $validator = $this->validateDataForPartialUpdate($data, $id);
        if ($validator->hasErrors()) {
            Response::validationError($validator->getErrors());
        }

        $updated = $this->model->partialUpdate($id, $data);

        if (!$updated) {
            Response::error('No fields to update or update failed', 400);
        }

        $item = $this->model->findById($id);
        Response::success($item, 'Item partially updated successfully');
    }

    // 부분 업데이트용 검증 메서드 (하위 클래스에서 재정의 가능)
    protected function validateDataForPartialUpdate($data, $id = null) {
        return $this->validateData($data, $id);
    }

    protected function getDeleted() {
        $params = $this->getQueryParams();
        $pagination = Pagination::fromRequest($params);

        $total = $this->model->count(true) - $this->model->count(false);
        $data = $this->model->getDeleted($pagination->getLimit(), $pagination->getOffset());

        $pagination = new Pagination($pagination->getPage(), $pagination->getLimit(), $total);

        Response::success($data, 'Deleted items retrieved', $pagination->toArray());
    }

    abstract protected function validateData($data, $id = null);
}
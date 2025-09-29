<?php
// Expense Archive API 엔드포인트
// 지출 아카이브 전용 엔드포인트

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Preflight 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../controllers/ExpenseArchiveController.php';

try {
    $controller = new ExpenseArchiveController();
    $method = $_SERVER['REQUEST_METHOD'];
    $path = $_SERVER['REQUEST_URI'];

    // URL에서 쿼리스트링 제거하고 경로만 추출
    $parsedUrl = parse_url($path);
    $route = $parsedUrl['path'];

    // /api/expense-archive/ 부분 제거
    $route = str_replace('/api/expense-archive', '', $route);
    $route = trim($route, '/');

    $response = '';

    switch ($method) {
        case 'GET':
            switch ($route) {
                case 'months':
                    $response = $controller->getMonths();
                    break;

                case 'available-months':
                    $response = $controller->getAvailableMonths();
                    break;

                case 'fixed-expenses':
                    $response = $controller->getFixedExpenses();
                    break;

                case 'prepaid-expenses':
                    $response = $controller->getPrepaidExpenses();
                    break;

                case 'summary':
                    $response = $controller->getExpenseSummary();
                    break;

                default:
                    http_response_code(404);
                    $response = json_encode([
                        'success' => false,
                        'message' => '존재하지 않는 엔드포인트입니다: ' . $route
                    ], JSON_UNESCAPED_UNICODE);
                    break;
            }
            break;

        case 'POST':
            switch ($route) {
                case 'create-snapshot':
                    $response = $controller->createExpenseSnapshot();
                    break;

                default:
                    http_response_code(404);
                    $response = json_encode([
                        'success' => false,
                        'message' => '지원하지 않는 POST 엔드포인트입니다: ' . $route
                    ], JSON_UNESCAPED_UNICODE);
                    break;
            }
            break;

        case 'PUT':
            // PUT /api/expense-archive/fixed-expenses/123 형태 처리
            $parts = explode('/', $route);

            if (count($parts) >= 2) {
                $expenseType = $parts[0]; // fixed-expenses, prepaid-expenses
                $expenseId = $parts[1];   // 지출 ID

                // expense-type을 테이블명으로 변환
                $expenseTable = str_replace('-', '_', $expenseType);

                // 유효한 지출 테이블인지 확인
                $validTables = ['fixed_expenses', 'prepaid_expenses'];
                if (in_array($expenseTable, $validTables)) {
                    $response = $controller->updateExpense($expenseTable, $expenseId);
                } else {
                    http_response_code(400);
                    $response = json_encode([
                        'success' => false,
                        'message' => '잘못된 지출 유형입니다: ' . $expenseType
                    ], JSON_UNESCAPED_UNICODE);
                }
            } else {
                http_response_code(400);
                $response = json_encode([
                    'success' => false,
                    'message' => '잘못된 PUT 요청 형식입니다'
                ], JSON_UNESCAPED_UNICODE);
            }
            break;

        case 'DELETE':
            // DELETE /api/expense-archive?year=2025&month=9 형태 처리
            if (isset($_GET['year']) && isset($_GET['month'])) {
                $year = intval($_GET['year']);
                $month = intval($_GET['month']);
                $response = $controller->deleteArchive($year, $month);
            } else {
                http_response_code(400);
                $response = json_encode([
                    'success' => false,
                    'message' => 'year와 month 파라미터가 필요합니다'
                ], JSON_UNESCAPED_UNICODE);
            }
            break;

        default:
            http_response_code(405);
            $response = json_encode([
                'success' => false,
                'message' => '지원하지 않는 HTTP 메서드입니다: ' . $method
            ], JSON_UNESCAPED_UNICODE);
            break;
    }

    echo $response;

} catch (Exception $e) {
    http_response_code(500);
    error_log("Expense Archive API Error: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => '서버 오류가 발생했습니다: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
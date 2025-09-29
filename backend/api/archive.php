<?php
// Archive API 엔드포인트
// 기존 API를 방해하지 않는 새로운 아카이브 전용 엔드포인트

header('Access-Control-Allow-Origin: http://localhost:3001');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json; charset=utf-8');

// Preflight 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../controllers/ArchiveController.php';
require_once '../lib/Auth.php';

// API 인증 확인
Auth::requireApiAuth();

try {
    $controller = new ArchiveController();
    $method = $_SERVER['REQUEST_METHOD'];
    $path = $_SERVER['REQUEST_URI'];

    // URL에서 쿼리스트링 제거하고 경로만 추출
    $parsedUrl = parse_url($path);
    $route = $parsedUrl['path'];

    // /api/archive/ 부분 제거
    $route = str_replace('/api/archive', '', $route);
    $route = trim($route, '/');

    $response = '';

    switch ($method) {
        case 'GET':
            switch ($route) {
                case 'months':
                    $response = $controller->getMonths();
                    break;

                case 'cash-assets':
                    $response = $controller->getCashAssets();
                    break;

                case 'investment-assets':
                    $response = $controller->getInvestmentAssets();
                    break;

                case 'pension-assets':
                    $response = $controller->getPensionAssets();
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
                    $response = $controller->createSnapshot();
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
            // PUT /api/archive/cash-assets/123 형태 처리
            $parts = explode('/', $route);

            if (count($parts) >= 2) {
                $assetType = $parts[0]; // cash-assets, investment-assets, pension-assets
                $assetId = $parts[1];   // 자산 ID

                // asset-type을 테이블명으로 변환
                $assetTable = str_replace('-', '_', $assetType);

                // 유효한 자산 테이블인지 확인
                $validTables = ['cash_assets', 'investment_assets', 'pension_assets'];
                if (in_array($assetTable, $validTables)) {
                    $response = $controller->updateAsset($assetTable, $assetId);
                } else {
                    http_response_code(400);
                    $response = json_encode([
                        'success' => false,
                        'message' => '잘못된 자산 유형입니다: ' . $assetType
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
    error_log("Archive API Error: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => '서버 오류가 발생했습니다: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
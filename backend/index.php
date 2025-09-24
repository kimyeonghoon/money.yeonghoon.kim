<?php
/**
 * Money Management API Entry Point
 * 자산 관리 웹 애플리케이션 API 진입점
 */

// 에러 리포팅 설정
if (getenv('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// CORS 헤더 설정
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// OPTIONS 요청 처리 (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 기본 설정
date_default_timezone_set(getenv('PHP_TIMEZONE') ?: 'Asia/Seoul');

// 오토로더 (추후 Composer 사용 시)
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/lib/Router.php';
require_once __DIR__ . '/lib/Response.php';

try {
    // 라우터 초기화
    $router = new Router();

    // API 라우트 정의
    $router->get('/', function() {
        return Response::success([
            'message' => 'Money Management API',
            'version' => '1.0.0',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    });

    // Health Check
    $router->get('/health', function() {
        return Response::success([
            'status' => 'healthy',
            'database' => Database::testConnection(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    });

    // 라우트 실행
    $router->run();

} catch (Exception $e) {
    error_log('API Error: ' . $e->getMessage());
    echo Response::error('Internal Server Error', 500);
}
?>
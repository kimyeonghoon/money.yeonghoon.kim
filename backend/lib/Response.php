<?php
/**
 * API Response Helper
 * API 응답 헬퍼 클래스
 */

class Response {
    /**
     * 성공 응답
     */
    public static function success($data = null, $message = 'Success') {
        $response = [
            'success' => true,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        http_response_code(200);
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 에러 응답
     */
    public static function error($message = 'Error', $code = 400) {
        $response = [
            'success' => false,
            'message' => $message,
            'error_code' => $code
        ];

        http_response_code($code);
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 데이터 응답 (페이징 포함)
     */
    public static function data($data, $total = null, $page = null, $limit = null) {
        $response = [
            'success' => true,
            'data' => $data
        ];

        if ($total !== null) {
            $response['pagination'] = [
                'total' => (int)$total,
                'page' => (int)$page,
                'limit' => (int)$limit,
                'pages' => $limit > 0 ? ceil($total / $limit) : 1
            ];
        }

        http_response_code(200);
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 생성 성공 응답
     */
    public static function created($data = null, $message = 'Created') {
        $response = [
            'success' => true,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        http_response_code(201);
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }
}
?>
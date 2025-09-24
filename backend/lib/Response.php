<?php

class Response {

    public static function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function success($data = null, $message = 'Success', $pagination = null) {
        $response = [
            'success' => true,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($pagination !== null) {
            $response['pagination'] = $pagination;
        }

        self::json($response);
    }

    public static function error($message = 'Error', $errorCode = 400, $details = null) {
        $response = [
            'success' => false,
            'message' => $message,
            'error_code' => $errorCode
        ];

        if ($details !== null) {
            $response['details'] = $details;
        }

        self::json($response, $errorCode);
    }

    public static function notFound($message = 'Resource not found') {
        self::error($message, 404);
    }

    public static function validationError($errors) {
        self::error('Validation failed', 400, $errors);
    }

    public static function serverError($message = 'Internal server error') {
        self::error($message, 500);
    }

    public static function created($data = null, $message = 'Created') {
        $response = [
            'success' => true,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        self::json($response, 201);
    }
}
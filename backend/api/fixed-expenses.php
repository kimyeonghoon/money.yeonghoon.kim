<?php

require_once __DIR__ . '/../controllers/FixedExpenseController.php';

try {
    $controller = new FixedExpenseController();
    $controller->handleRequest();
} catch (Exception $e) {
    Response::serverError('Internal server error: ' . $e->getMessage());
}
<?php

require_once __DIR__ . '/../controllers/DailyExpenseController.php';

try {
    $controller = new DailyExpenseController();
    $controller->handleRequest();
} catch (Exception $e) {
    Response::serverError('Internal server error: ' . $e->getMessage());
}
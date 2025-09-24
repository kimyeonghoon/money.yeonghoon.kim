<?php

require_once __DIR__ . '/../controllers/PrepaidExpenseController.php';

try {
    $controller = new PrepaidExpenseController();
    $controller->handleRequest();
} catch (Exception $e) {
    Response::serverError('Internal server error: ' . $e->getMessage());
}
<?php

require_once __DIR__ . '/../controllers/DashboardController.php';

try {
    $controller = new DashboardController();
    $controller->handleRequest();
} catch (Exception $e) {
    Response::serverError('Internal server error: ' . $e->getMessage());
}
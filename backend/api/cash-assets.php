<?php

require_once __DIR__ . '/../controllers/CashAssetController.php';

try {
    $controller = new CashAssetController();
    $controller->handleRequest();
} catch (Exception $e) {
    Response::serverError('Internal server error: ' . $e->getMessage());
}
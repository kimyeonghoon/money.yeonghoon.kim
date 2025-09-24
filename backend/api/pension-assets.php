<?php

require_once __DIR__ . '/../controllers/PensionAssetController.php';

try {
    $controller = new PensionAssetController();
    $controller->handleRequest();
} catch (Exception $e) {
    Response::serverError('Internal server error: ' . $e->getMessage());
}
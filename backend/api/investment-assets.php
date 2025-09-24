<?php

require_once __DIR__ . '/../controllers/InvestmentAssetController.php';

try {
    $controller = new InvestmentAssetController();
    $controller->handleRequest();
} catch (Exception $e) {
    Response::serverError('Internal server error: ' . $e->getMessage());
}
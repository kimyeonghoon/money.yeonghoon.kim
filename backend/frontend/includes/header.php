<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : '개인 자산관리'; ?> - 머니매니저</title>

    <!-- Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Custom CSS for overrides -->
    <link rel="stylesheet" href="/frontend/css/material-custom.css">

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

    <!-- 파비콘 -->
    <link rel="icon" type="image/gif" href="/frontend/img/money.gif">
</head>
<body>
    <!-- Header Section -->
    <div class="header-content">
        <div class="container">
            <div class="row">
                <div class="col s12 center-align">
                    <h1 class="brand-logo">개인 자산관리</h1>
                    <p class="brand-subtitle">스마트한 재무 관리 도구</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav>
        <div class="nav-wrapper">
            <a href="#" data-target="mobile-demo" class="sidenav-trigger"><i class="material-icons">menu</i></a>
            <ul class="hide-on-med-and-down">
                <li><a href="/frontend/dashboard.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="material-icons left">dashboard</i>대시보드
                </a></li>
                <li><a href="/frontend/cash-assets.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'cash-assets.php') ? 'active' : ''; ?>">
                    <i class="material-icons left">account_balance_wallet</i>현금자산
                </a></li>
                <li><a href="/frontend/investment-assets.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'investment-assets.php') ? 'active' : ''; ?>">
                    <i class="material-icons left">trending_up</i>투자자산
                </a></li>
                <li><a href="/frontend/pension-assets.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'pension-assets.php') ? 'active' : ''; ?>">
                    <i class="material-icons left">security</i>연금자산
                </a></li>
                <li><a href="/frontend/daily-expenses.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'daily-expenses.php') ? 'active' : ''; ?>">
                    <i class="material-icons left">receipt</i>일별지출
                </a></li>
                <li><a href="/frontend/fixed-expenses.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'fixed-expenses.php') ? 'active' : ''; ?>">
                    <i class="material-icons left">repeat</i>고정지출
                </a></li>
                <li><a href="/frontend/prepaid-expenses.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'prepaid-expenses.php') ? 'active' : ''; ?>">
                    <i class="material-icons left">payment</i>선납지출
                </a></li>
            </ul>
        </div>
    </nav>

    <!-- Mobile Navigation -->
    <ul class="sidenav" id="mobile-demo">
        <li><a href="/frontend/dashboard.php"><i class="material-icons">dashboard</i>대시보드</a></li>
        <li><a href="/frontend/cash-assets.php"><i class="material-icons">account_balance_wallet</i>현금자산</a></li>
        <li><a href="/frontend/investment-assets.php"><i class="material-icons">trending_up</i>투자자산</a></li>
        <li><a href="/frontend/pension-assets.php"><i class="material-icons">security</i>연금자산</a></li>
        <li><a href="/frontend/daily-expenses.php"><i class="material-icons">receipt</i>일별지출</a></li>
        <li><a href="/frontend/fixed-expenses.php"><i class="material-icons">repeat</i>고정지출</a></li>
        <li><a href="/frontend/prepaid-expenses.php"><i class="material-icons">payment</i>선납지출</a></li>
    </ul>

    <!-- Main Content Container -->
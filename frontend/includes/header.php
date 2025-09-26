<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="머니매니저">
    <meta name="theme-color" content="#2196F3">
    <title><?php echo isset($pageTitle) ? $pageTitle : '개인 자산관리'; ?> - 머니매니저</title>

    <!-- Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Custom CSS for overrides -->
    <link rel="stylesheet" href="/css/material-custom.css">

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- jQuery UI (for sortable) -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css">
    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

    <!-- 파비콘 -->
    <link rel="icon" type="image/gif" href="/img/money.gif">
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
                <li><a href="/dashboard.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="material-icons left">dashboard</i>대시보드
                </a></li>
                <li><a href="/pension-assets.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'pension-assets.php') ? 'active' : ''; ?>">
                    <i class="material-icons left">security</i>연금자산
                </a></li>
                <li><a href="/daily-expenses.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'daily-expenses.php') ? 'active' : ''; ?>">
                    <i class="material-icons left">receipt</i>일별지출
                </a></li>
                <li><a href="/fixed-expenses.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'fixed-expenses.php') ? 'active' : ''; ?>">
                    <i class="material-icons left">repeat</i>고정지출
                </a></li>
                <li><a href="/prepaid-expenses.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'prepaid-expenses.php') ? 'active' : ''; ?>">
                    <i class="material-icons left">payment</i>선납지출
                </a></li>
            </ul>
        </div>
    </nav>

    <!-- Mobile Navigation -->
    <ul class="sidenav" id="mobile-demo">
        <li><a href="/dashboard.php"><i class="material-icons">dashboard</i>대시보드</a></li>
        <li><a href="/pension-assets.php"><i class="material-icons">security</i>연금자산</a></li>
        <li><a href="/daily-expenses.php"><i class="material-icons">receipt</i>일별지출</a></li>
        <li><a href="/fixed-expenses.php"><i class="material-icons">repeat</i>고정지출</a></li>
        <li><a href="/prepaid-expenses.php"><i class="material-icons">payment</i>선납지출</a></li>
    </ul>

    <!-- Bottom Navigation (Mobile) -->
    <nav class="bottom-nav">
        <a href="/dashboard.php" class="bottom-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
            <i class="material-icons">dashboard</i>
            <span>홈</span>
        </a>
        <a href="/pension-assets.php" class="bottom-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'pension-assets.php') ? 'active' : ''; ?>">
            <i class="material-icons">security</i>
            <span>연금</span>
        </a>
        <a href="/daily-expenses.php" class="bottom-nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'daily-expenses.php') ? 'active' : ''; ?>">
            <i class="material-icons">receipt</i>
            <span>지출</span>
        </a>
    </nav>

    <!-- Main Content Container -->
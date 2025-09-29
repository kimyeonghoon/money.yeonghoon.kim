<?php
require_once __DIR__ . '/lib/Auth.php';

// 이미 로그인된 경우 메인 페이지로 리다이렉트
if (Auth::isAuthenticated()) {
    header('Location: /assets.php');
    exit;
}

$error = '';

// 로그인 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (Auth::login($email, $password)) {
        header('Location: /assets.php');
        exit;
    } else {
        $error = '이메일 또는 비밀번호가 올바르지 않습니다.';
    }
}

$pageTitle = '로그인';
?>
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
    <title><?php echo $pageTitle; ?> - 머니매니저</title>

    <!-- Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Custom CSS for overrides -->
    <link rel="stylesheet" href="/css/material-custom.css">
    <!-- Login Page CSS -->
    <link rel="stylesheet" href="/css/login.css">

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

    <!-- 파비콘 -->
    <link rel="icon" type="image/gif" href="/img/money.gif">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="material-icons login-icon">account_balance_wallet</i>
            <h4>머니매니저</h4>
            <p style="color: #666; margin: 0;">개인 자산관리 시스템</p>
        </div>

        <?php if ($error): ?>
        <div class="error-message">
            <i class="material-icons left" style="font-size: 18px;">error</i>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <form method="POST" id="login-form">
            <div class="input-field">
                <input id="email" name="email" type="email" class="validate" required
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                <label for="email">이메일</label>
                <span class="helper-text" data-error="올바른 이메일 형식이 아닙니다" data-success="올바른 형식입니다"></span>
            </div>

            <div class="input-field">
                <input id="password" name="password" type="password" class="validate" required>
                <label for="password">비밀번호</label>
            </div>

            <button class="btn waves-effect waves-light btn-login" type="submit" id="login-btn">
                <span class="btn-text">로그인</span>
                <div class="preloader-wrapper small loading" style="display: none;">
                    <div class="spinner-layer spinner-white-only">
                        <div class="circle-clipper left">
                            <div class="circle"></div>
                        </div>
                        <div class="gap-patch">
                            <div class="circle"></div>
                        </div>
                        <div class="circle-clipper right">
                            <div class="circle"></div>
                        </div>
                    </div>
                </div>
            </button>
        </form>

        <div class="app-info">
            <p style="margin: 5px 0;">안전한 개인 자산 관리</p>
            <small style="color: #999;">© 2025 YeongHoon Kim</small>
        </div>
    </div>

    <!-- Login Page JavaScript -->
    <script src="/js/login.js"></script>
</body>
</html>
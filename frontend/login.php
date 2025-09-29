<?php
/**
 * 로그인 페이지 - 머니매니저 시스템
 *
 * 사용자 인증을 처리하는 로그인 폼을 제공합니다.
 * 세션 기반 인증을 사용하며, 로그인 성공 시 자산현황 페이지로 리다이렉트됩니다.
 *
 * @package MoneyManager
 * @version 1.0
 * @author YeongHoon Kim
 */

// 인증 라이브러리 로드
require_once __DIR__ . '/lib/Auth.php';

// 이미 로그인된 사용자가 접근한 경우 메인 페이지로 자동 리다이렉트
// 중복 로그인 방지 및 사용자 경험 개선
if (Auth::isAuthenticated()) {
    header('Location: /assets.php');
    exit;
}

// 에러 메시지 초기화
$error = '';

// POST 요청 처리 - 로그인 폼 제출 시
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 사용자 입력 데이터 안전하게 수집
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // 로그인 시도 - Auth 클래스의 login 메소드 사용
    if (Auth::login($email, $password)) {
        // 로그인 성공: 자산현황 페이지로 리다이렉트
        header('Location: /assets.php');
        exit;
    } else {
        // 로그인 실패: 에러 메시지 설정
        $error = '이메일 또는 비밀번호가 올바르지 않습니다.';
    }
}

// 페이지 타이틀 설정
$pageTitle = '로그인';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <!-- 기본 문서 설정 -->
    <meta charset="UTF-8">
    <!-- 모바일 반응형 설정: 뷰포트 크기 조정, 확대/축소 제한 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">

    <!-- PWA(Progressive Web App) 설정 -->
    <meta name="mobile-web-app-capable" content="yes">           <!-- 모바일 웹앱으로 설치 가능 -->
    <meta name="apple-mobile-web-app-capable" content="yes">      <!-- iOS Safari에서 웹앱 모드 지원 -->
    <meta name="apple-mobile-web-app-status-bar-style" content="default"> <!-- iOS 상태바 스타일 -->
    <meta name="apple-mobile-web-app-title" content="머니매니저">  <!-- iOS 홈스크린 앱 이름 -->
    <meta name="theme-color" content="#2196F3">                   <!-- 브라우저 주소창 색상 -->

    <!-- 동적 페이지 타이틀 -->
    <title><?php echo $pageTitle; ?> - 머니매니저</title>

    <!-- 외부 CSS 라이브러리 -->
    <!-- Materialize CSS: Material Design 기반 UI 프레임워크 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <!-- Google Material Icons: 아이콘 폰트 -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- 커스텀 CSS -->
    <!-- Materialize 스타일 커스터마이징 -->
    <link rel="stylesheet" href="/css/material-custom.css">
    <!-- 로그인 페이지 전용 스타일 -->
    <link rel="stylesheet" href="/css/login.css">

    <!-- JavaScript 라이브러리 -->
    <!-- jQuery: DOM 조작 및 AJAX 지원 -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Materialize JS: 인터랙티브 컴포넌트 지원 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

    <!-- 브라우저 아이콘 -->
    <link rel="icon" type="image/gif" href="/img/money.gif">
</head>
<body>
    <!-- 로그인 페이지 메인 컨테이너 -->
    <div class="login-container">
        <!-- 헤더 영역: 로고, 앱 이름, 설명 -->
        <div class="login-header">
            <!-- Material Icons 지갑 아이콘 -->
            <i class="material-icons login-icon">account_balance_wallet</i>
            <!-- 앱 이름 -->
            <h4>머니매니저</h4>
            <!-- 앱 설명 -->
            <p style="color: #666; margin: 0;">개인 자산관리 시스템</p>
        </div>

        <!-- 에러 메시지 표시 영역 (로그인 실패 시에만 표시) -->
        <?php if ($error): ?>
        <div class="error-message">
            <!-- 에러 아이콘 -->
            <i class="material-icons left" style="font-size: 18px;">error</i>
            <!-- 에러 메시지 (XSS 방지를 위한 htmlspecialchars 적용) -->
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <!-- 로그인 폼 -->
        <form method="POST" id="login-form">
            <!-- 이메일 입력 필드 -->
            <div class="input-field">
                <!-- 이메일 입력: HTML5 email 타입, 필수 입력, 자동 검증 -->
                <input id="email" name="email" type="email" class="validate" required
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                <!-- 플로팅 라벨 -->
                <label for="email">이메일</label>
                <!-- 실시간 검증 메시지 -->
                <span class="helper-text" data-error="올바른 이메일 형식이 아닙니다" data-success="올바른 형식입니다"></span>
            </div>

            <!-- 비밀번호 입력 필드 -->
            <div class="input-field">
                <!-- 비밀번호 입력: password 타입으로 마스킹, 필수 입력 -->
                <input id="password" name="password" type="password" class="validate" required>
                <!-- 플로팅 라벨 -->
                <label for="password">비밀번호</label>
            </div>

            <!-- 로그인 버튼 -->
            <button class="btn waves-effect waves-light btn-login" type="submit" id="login-btn">
                <!-- 버튼 텍스트 -->
                <span class="btn-text">로그인</span>
                <!-- 로딩 스피너 (JavaScript로 제어) -->
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

        <!-- 앱 정보 및 저작권 -->
        <div class="app-info">
            <!-- 앱 설명 -->
            <p style="margin: 5px 0;">안전한 개인 자산 관리</p>
            <!-- 저작권 정보 -->
            <small style="color: #999;">© 2025 YeongHoon Kim</small>
        </div>
    </div>

    <!-- 로그인 페이지 전용 JavaScript -->
    <script src="/js/login.js"></script>
</body>
</html>
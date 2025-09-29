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

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

    <!-- 파비콘 -->
    <link rel="icon" type="image/gif" href="/img/money.gif">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: 'Roboto', sans-serif;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 24px 48px rgba(0, 0, 0, 0.15);
            max-width: 400px;
            width: 90%;
            margin: 20px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h4 {
            color: #333;
            margin: 10px 0;
            font-weight: 300;
        }

        .login-icon {
            font-size: 48px !important;
            color: #2196F3;
            margin-bottom: 10px;
        }

        .input-field input:focus + label {
            color: #2196F3 !important;
        }

        .input-field input:focus {
            border-bottom: 2px solid #2196F3 !important;
            box-shadow: 0 1px 0 0 #2196F3 !important;
        }

        .btn-login {
            background: linear-gradient(45deg, #2196F3, #21CBF3);
            width: 100%;
            border-radius: 25px;
            padding: 12px 0;
            margin-top: 20px;
            font-weight: 500;
            text-transform: none;
            font-size: 16px;
        }

        .btn-login:hover {
            background: linear-gradient(45deg, #1976D2, #0097A7);
        }

        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #f44336;
            font-size: 14px;
        }

        @media only screen and (max-width: 600px) {
            .login-container {
                padding: 30px 20px;
                margin: 10px;
            }

            .login-header h4 {
                font-size: 22px;
            }

            .login-icon {
                font-size: 40px !important;
            }
        }

        .app-info {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }

        .loading {
            display: none;
        }

        .loading .btn-login {
            background-color: #ccc !important;
            cursor: not-allowed;
        }
    </style>
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

    <script>
        $(document).ready(function() {
            // Materialize 초기화
            M.updateTextFields();

            // 로그인 폼 제출 처리
            $('#login-form').on('submit', function(e) {
                const email = $('#email').val();
                const password = $('#password').val();

                if (!email || !password) {
                    M.toast({html: '이메일과 비밀번호를 입력해주세요.', classes: 'red'});
                    e.preventDefault();
                    return;
                }

                // 로딩 상태 표시
                $('#login-btn').addClass('loading');
                $('.btn-text').text('로그인 중...');
                $('.loading').show();
            });

            // 엔터키로 로그인
            $('#password').on('keypress', function(e) {
                if (e.which === 13) {
                    $('#login-form').submit();
                }
            });

            // 입력 필드 포커스 효과
            $('#email, #password').on('focus', function() {
                $(this).parent().addClass('focused');
            }).on('blur', function() {
                if (!$(this).val()) {
                    $(this).parent().removeClass('focused');
                }
            });
        });
    </script>
</body>
</html>
<?php

require_once __DIR__ . '/SessionManager.php';

class Auth {
    private static $sessionManager = null;
    private static $currentSessionId = null;

    /**
     * SessionManager 인스턴스 가져오기
     */
    private static function getSessionManager() {
        if (self::$sessionManager === null) {
            self::$sessionManager = new SessionManager();
        }
        return self::$sessionManager;
    }

    /**
     * 현재 세션 ID 가져오기 (쿠키에서)
     */
    private static function getCurrentSessionId() {
        if (self::$currentSessionId === null) {
            self::$currentSessionId = $_COOKIE['app_session_id'] ?? null;
        }
        return self::$currentSessionId;
    }

    /**
     * 세션 쿠키 설정
     */
    private static function setSessionCookie($sessionId) {
        $expire = time() + 3600; // 1시간
        $path = '/';
        $domain = '';
        $secure = false; // HTTPS에서만 true로 설정
        $httpOnly = true;
        $sameSite = 'Lax';

        setcookie('app_session_id', $sessionId, [
            'expires' => $expire,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httpOnly,
            'samesite' => $sameSite
        ]);

        self::$currentSessionId = $sessionId;
    }

    /**
     * 세션 쿠키 삭제
     */
    private static function clearSessionCookie() {
        setcookie('app_session_id', '', time() - 3600, '/');
        self::$currentSessionId = null;
    }

    /**
     * 사용자 인증 확인
     */
    public static function isAuthenticated() {
        $sessionId = self::getCurrentSessionId();
        if (!$sessionId) {
            return false;
        }

        $sessionManager = self::getSessionManager();
        $sessionData = $sessionManager->validateSession($sessionId);

        return $sessionData['valid'];
    }

    /**
     * 환경변수 로드 (.env 파일에서)
     */
    private static function loadEnv() {
        static $loaded = false;
        if ($loaded) {
            return;
        }

        // 프로젝트 루트의 .env 파일 경로
        $envFile = __DIR__ . '/../../.env';

        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                // 주석 제거
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }

                // KEY=VALUE 파싱
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);

                    // 인라인 주석 제거 (따옴표로 감싸지 않은 경우만)
                    if (strpos($value, '#') !== false && $value[0] !== '"' && $value[0] !== "'") {
                        $value = trim(explode('#', $value)[0]);
                    }

                    // 따옴표 제거
                    $value = trim($value, "'\"");

                    // 환경변수에 설정 (이미 없는 경우만)
                    if (!getenv($key)) {
                        putenv("$key=$value");
                    }
                }
            }
        }

        $loaded = true;
    }

    /**
     * 로그인 처리
     */
    public static function login($email, $password) {
        // .env 파일 로드
        self::loadEnv();

        $envEmail = getenv('LOGIN_USERNAME');
        $envPasswordHash = getenv('LOGIN_PASSWORD_HASH');

        if (!$envEmail || !$envPasswordHash) {
            return false;
        }

        // 이메일 확인
        if ($email !== $envEmail) {
            return false;
        }

        // 비밀번호 확인 (bcrypt 우선, SHA-512 fallback)
        // bcrypt 해시 형식: $2y$로 시작
        if (strpos($envPasswordHash, '$2y$') === 0) {
            // bcrypt 검증
            if (!password_verify($password, $envPasswordHash)) {
                return false;
            }
        } else {
            // SHA-512 검증 (하위 호환성)
            $inputPasswordHash = hash('sha512', $password);
            if ($inputPasswordHash !== $envPasswordHash) {
                return false;
            }
        }

        // 로그인 성공 - 새 세션 생성
        $sessionManager = self::getSessionManager();
        $sessionData = [
            'login_time' => time(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ip_address' => $sessionManager->getUserIP()
        ];

        $sessionId = $sessionManager->createSession($email, $sessionData);
        self::setSessionCookie($sessionId);

        // 텔레그램 알림 전송
        self::sendTelegramNotification($email);

        return true;
    }

    /**
     * 로그아웃 처리
     */
    public static function logout() {
        $sessionId = self::getCurrentSessionId();
        if ($sessionId) {
            $sessionManager = self::getSessionManager();
            $sessionManager->invalidateSession($sessionId);
        }

        self::clearSessionCookie();
    }

    /**
     * 모든 세션 로그아웃
     */
    public static function logoutAllSessions() {
        $user = self::getUser();
        if ($user) {
            $sessionManager = self::getSessionManager();
            $sessionManager->invalidateUserSessions($user['email']);
        }

        self::clearSessionCookie();
    }

    /**
     * 인증 필요 페이지 보호
     */
    public static function requireAuth() {
        if (!self::isAuthenticated()) {
            // AJAX 요청인 경우
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Authentication required']);
                exit;
            }

            // 일반 페이지 요청인 경우
            header('Location: /login.php');
            exit;
        }
    }

    /**
     * API 인증 확인
     */
    public static function requireApiAuth() {
        if (!self::isAuthenticated()) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Authentication required',
                'message' => 'Please login to access this API'
            ]);
            exit;
        }
    }

    /**
     * 현재 로그인된 사용자 정보 반환
     */
    public static function getUser() {
        $sessionId = self::getCurrentSessionId();
        if (!$sessionId) {
            return null;
        }

        $sessionManager = self::getSessionManager();
        $sessionData = $sessionManager->validateSession($sessionId);

        if ($sessionData['valid']) {
            return [
                'email' => $sessionData['user_email'],
                'session_data' => $sessionData['session_data']
            ];
        }

        return null;
    }

    /**
     * 세션 정리 (크론잡이나 정기적 호출용)
     */
    public static function cleanupSessions() {
        $sessionManager = self::getSessionManager();
        $sessionManager->cleanupExpiredSessions();
    }

    /**
     * 활성 세션 수 조회
     */
    public static function getActiveSessionsCount($userEmail = null) {
        $sessionManager = self::getSessionManager();
        return $sessionManager->getActiveSessionsCount($userEmail);
    }

    /**
     * 세션 수명 설정 (초 단위)
     */
    public static function setSessionLifetime($seconds) {
        $sessionManager = self::getSessionManager();
        $sessionManager->setSessionLifetime($seconds);
    }

    /**
     * 텔레그램 로그인 알림 전송
     */
    private static function sendTelegramNotification($email) {
        // .env 파일 로드
        self::loadEnv();

        $botToken = getenv('TELEGRAM_BOT_TOKEN');
        $chatId = getenv('TELEGRAM_CHAT_ID');

        if (!$botToken || !$chatId || $botToken === 'your_telegram_bot_token') {
            return; // 텔레그램 설정이 없으면 무시
        }

        $sessionManager = self::getSessionManager();
        $activeSessions = $sessionManager->getActiveSessionsCount($email);

        $message = "🔐 머니매니저 로그인 알림\n\n";
        $message .= "📧 사용자: " . $email . "\n";
        $message .= "🕒 시간: " . date('Y-m-d H:i:s') . "\n";
        $message .= "🌐 IP: " . $sessionManager->getUserIP() . "\n";
        $message .= "📱 활성 세션: " . $activeSessions . "개\n";
        $message .= "🖥️ User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];

        // 비동기로 전송 (실패해도 로그인에 영향 없음)
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query($data),
                'timeout' => 5
            ]
        ]);

        @file_get_contents($url, false, $context);
    }
}
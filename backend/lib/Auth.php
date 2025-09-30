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
        $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'; // HTTPS 자동 감지
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
     * 로그인 처리
     */
    public static function login($email, $password) {
        $envEmail = getenv('LOGIN_USERNAME');
        $envPasswordHash = getenv('LOGIN_PASSWORD_HASH');

        // 보안 로그용 정보 수집
        $clientIP = self::getSessionManager()->getUserIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        if (!$envEmail || !$envPasswordHash) {
            self::logSecurityEvent('LOGIN_CONFIG_ERROR', $email, $clientIP, 'Missing environment configuration');
            return false;
        }

        // 이메일 확인
        if ($email !== $envEmail) {
            self::logSecurityEvent('LOGIN_FAILED', $email, $clientIP, 'Invalid email: ' . $email);
            return false;
        }

        // 비밀번호 해시 확인 (password_verify 사용)
        // 기존 SHA-512 방식과 새로운 password_hash 방식 모두 지원
        if (strlen($envPasswordHash) === 128) {
            // 기존 SHA-512 해시인 경우 (하위 호환성)
            $inputPasswordHash = hash('sha512', $password);
            if (!hash_equals($inputPasswordHash, $envPasswordHash)) {
                self::logSecurityEvent('LOGIN_FAILED', $email, $clientIP, 'Invalid password (SHA-512)');
                return false;
            }
        } else {
            // 새로운 password_hash 방식
            if (!password_verify($password, $envPasswordHash)) {
                self::logSecurityEvent('LOGIN_FAILED', $email, $clientIP, 'Invalid password (password_hash)');
                return false;
            }
        }

        // 로그인 성공 - 새 세션 생성
        $sessionManager = self::getSessionManager();
        $sessionData = [
            'login_time' => time(),
            'user_agent' => $userAgent,
            'ip_address' => $clientIP
        ];

        $sessionId = $sessionManager->createSession($email, $sessionData);
        self::setSessionCookie($sessionId);

        // 성공 로그 기록
        self::logSecurityEvent('LOGIN_SUCCESS', $email, $clientIP, 'Login successful');

        // 텔레그램 알림 전송
        self::sendTelegramNotification($email);

        return true;
    }

    /**
     * 로그아웃 처리
     */
    public static function logout() {
        $sessionId = self::getCurrentSessionId();
        $user = self::getUser();

        if ($sessionId) {
            $sessionManager = self::getSessionManager();
            $sessionManager->invalidateSession($sessionId);

            // 로그아웃 이벤트 로깅
            if ($user) {
                self::logSecurityEvent('LOGOUT', $user['email'], $sessionManager->getUserIP(), 'User logout');
            }
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
            // API 인증 실패 로깅
            $sessionManager = self::getSessionManager();
            self::logSecurityEvent('API_AUTH_FAILED', 'unknown', $sessionManager->getUserIP(), 'Unauthorized API access attempt: ' . $_SERVER['REQUEST_URI']);

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

    /**
     * 보안 이벤트 로깅
     */
    private static function logSecurityEvent($eventType, $email, $ipAddress, $details) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = sprintf(
            "[%s] SECURITY_EVENT: %s | Email: %s | IP: %s | Details: %s | UserAgent: %s",
            $timestamp,
            $eventType,
            $email,
            $ipAddress,
            $details,
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        );

        // PHP 에러 로그에 기록
        error_log($logMessage);

        // 선택적: 별도 보안 로그 파일에도 기록
        if (defined('SECURITY_LOG_PATH')) {
            $logFile = SECURITY_LOG_PATH . '/security.log';
            @file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }
}
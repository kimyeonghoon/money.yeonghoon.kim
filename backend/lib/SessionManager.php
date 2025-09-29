<?php

require_once __DIR__ . '/Database.php';

class SessionManager {
    private $db;
    private $sessionLifetime = 3600; // 1시간 (초 단위)

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * 새 세션 생성
     */
    public function createSession($userEmail, $sessionData = []) {
        // 기존 세션들 무효화
        $this->invalidateUserSessions($userEmail);

        // 새 세션 ID 생성
        $sessionId = $this->generateSessionId();

        // 만료 시간 설정
        $expiresAt = date('Y-m-d H:i:s', time() + $this->sessionLifetime);

        // 사용자 정보 수집
        $ipAddress = $this->getUserIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $stmt = $this->db->prepare("
            INSERT INTO user_sessions
            (session_id, user_email, session_data, expires_at, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $sessionDataJson = json_encode($sessionData);
        $stmt->execute([
            $sessionId,
            $userEmail,
            $sessionDataJson,
            $expiresAt,
            $ipAddress,
            $userAgent
        ]);

        return $sessionId;
    }

    /**
     * 세션 유효성 검증
     */
    public function validateSession($sessionId) {
        // 만료된 세션 정리
        $this->cleanupExpiredSessions();

        $stmt = $this->db->prepare("
            SELECT user_email, session_data, expires_at
            FROM user_sessions
            WHERE session_id = ? AND is_active = TRUE AND expires_at > NOW()
        ");

        $stmt->execute([$sessionId]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($session) {
            // 세션 활동 시간 업데이트
            $this->updateSessionActivity($sessionId);
            return [
                'valid' => true,
                'user_email' => $session['user_email'],
                'session_data' => json_decode($session['session_data'], true) ?? []
            ];
        }

        return ['valid' => false];
    }

    /**
     * 세션 무효화
     */
    public function invalidateSession($sessionId) {
        $stmt = $this->db->prepare("
            UPDATE user_sessions
            SET is_active = FALSE
            WHERE session_id = ?
        ");

        $stmt->execute([$sessionId]);
    }

    /**
     * 사용자의 모든 세션 무효화
     */
    public function invalidateUserSessions($userEmail) {
        $stmt = $this->db->prepare("
            UPDATE user_sessions
            SET is_active = FALSE
            WHERE user_email = ? AND is_active = TRUE
        ");

        $stmt->execute([$userEmail]);
    }

    /**
     * 세션 활동 시간 업데이트 및 만료 시간 연장
     */
    public function updateSessionActivity($sessionId) {
        $newExpiresAt = date('Y-m-d H:i:s', time() + $this->sessionLifetime);

        $stmt = $this->db->prepare("
            UPDATE user_sessions
            SET last_activity = NOW(), expires_at = ?
            WHERE session_id = ? AND is_active = TRUE
        ");

        $stmt->execute([$newExpiresAt, $sessionId]);
    }

    /**
     * 만료된 세션 정리
     */
    public function cleanupExpiredSessions() {
        $stmt = $this->db->prepare("
            DELETE FROM user_sessions
            WHERE expires_at < NOW() OR is_active = FALSE
        ");

        $stmt->execute();
    }

    /**
     * 세션 데이터 업데이트
     */
    public function updateSessionData($sessionId, $sessionData) {
        $stmt = $this->db->prepare("
            UPDATE user_sessions
            SET session_data = ?, last_activity = NOW()
            WHERE session_id = ? AND is_active = TRUE
        ");

        $sessionDataJson = json_encode($sessionData);
        $stmt->execute([$sessionDataJson, $sessionId]);
    }

    /**
     * 고유한 세션 ID 생성
     */
    private function generateSessionId() {
        return bin2hex(random_bytes(32)) . '_' . time();
    }

    /**
     * 사용자 IP 주소 가져오기
     */
    public function getUserIP() {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];

        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }

    /**
     * 활성 세션 통계 조회
     */
    public function getActiveSessionsCount($userEmail = null) {
        if ($userEmail) {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM user_sessions
                WHERE user_email = ? AND is_active = TRUE AND expires_at > NOW()
            ");
            $stmt->execute([$userEmail]);
        } else {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM user_sessions
                WHERE is_active = TRUE AND expires_at > NOW()
            ");
            $stmt->execute();
        }

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    /**
     * 세션 수명 설정
     */
    public function setSessionLifetime($seconds) {
        $this->sessionLifetime = $seconds;
    }

    /**
     * 세션 수명 조회
     */
    public function getSessionLifetime() {
        return $this->sessionLifetime;
    }
}
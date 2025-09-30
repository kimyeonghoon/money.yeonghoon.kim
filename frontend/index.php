<?php
/**
 * 루트 인덱스 페이지 - 머니매니저 시스템
 *
 * 사용자 인증 상태에 따른 조건부 리다이렉션을 처리합니다.
 * - 미인증 사용자: login.php로 리다이렉션
 * - 인증된 사용자: assets.php로 리다이렉션
 *
 * @package MoneyManager
 * @version 1.0
 * @author YeongHoon Kim
 */

// 인증 라이브러리 로드
require_once __DIR__ . '/lib/Auth.php';

// 인증 상태 체크
if (Auth::isAuthenticated()) {
    // 인증된 사용자 → 자산현황 페이지로 리다이렉션
    header('Location: /assets.php');
    exit;
} else {
    // 미인증 사용자 → 로그인 페이지로 리다이렉션
    header('Location: /login.php');
    exit;
}
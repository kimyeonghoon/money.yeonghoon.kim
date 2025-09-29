<?php
require_once __DIR__ . '/lib/Auth.php';

// 로그아웃 처리
Auth::logout();

// 로그인 페이지로 리다이렉트
header('Location: /login.php?message=logout');
exit;
?>
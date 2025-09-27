<?php
// daily-expenses.php에서 expense-records.php로 영구 리다이렉트
header('HTTP/1.1 301 Moved Permanently');
header('Location: /expense-records.php');
exit();
?>
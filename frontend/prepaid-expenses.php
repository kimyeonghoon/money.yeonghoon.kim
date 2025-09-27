<?php
// prepaid-expenses.php에서 expense-status.php로 영구 리다이렉트
header('HTTP/1.1 301 Moved Permanently');
header('Location: /expense-status.php');
exit();
?>
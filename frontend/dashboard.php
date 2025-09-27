<?php
// dashboard.php에서 assets.php로 영구 리다이렉트
header('HTTP/1.1 301 Moved Permanently');
header('Location: /assets.php');
exit();
?>
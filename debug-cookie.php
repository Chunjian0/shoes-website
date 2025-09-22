<?php
// 清除之前的调试日志
$debugLogFile = 'cookie-debug.log';
file_put_contents($debugLogFile, '');

// 记录所有cookie
file_put_contents($debugLogFile, "All cookies:\n" . print_r($_COOKIE, true) . "\n", FILE_APPEND);

// 看是否有购物车session ID
if (isset($_COOKIE['cart_session_id'])) {
    file_put_contents($debugLogFile, "Found cart_session_id cookie: " . $_COOKIE['cart_session_id'] . "\n", FILE_APPEND);
} else {
    file_put_contents($debugLogFile, "No cart_session_id cookie found\n", FILE_APPEND);
    
    // 设置cookie测试
    $sessionId = uniqid('test_', true);
    setcookie('cart_session_id', $sessionId, time() + 3600, '/');
    file_put_contents($debugLogFile, "Set new cart_session_id cookie: " . $sessionId . "\n", FILE_APPEND);
}

// 输出结果
echo "Cookie检查完成，结果已记录到cookie-debug.log。请刷新页面再次检查cookie是否已保存。"; 
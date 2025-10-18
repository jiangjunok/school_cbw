<?php
// 简单的PHP测试文件
header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'status' => 'PHP工作正常',
    'php_version' => phpversion(),
    'current_dir' => getcwd(),
    'content_file_exists' => file_exists('content.txt'),
    'content_file_writable' => is_writable('content.txt') || is_writable('.'),
    'server_method' => $_SERVER['REQUEST_METHOD']
]);
?>
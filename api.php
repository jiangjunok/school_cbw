<?php
// 开启错误报告（调试用）
error_reporting(E_ALL);
ini_set('display_errors', 0); // 不直接显示错误，避免破坏JSON格式

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$contentFile = 'content.txt';

// 确保文件存在
if (!file_exists($contentFile)) {
    file_put_contents($contentFile, '[]');
}

// 通过GET参数来确定操作类型，兼容不支持POST的服务器
$action = isset($_GET['action']) ? $_GET['action'] : 'get';

switch ($action) {
    case 'get':
        // 读取所有提交内容
        $content = file_get_contents($contentFile);
        if ($content === false) {
            echo json_encode(['error' => '读取文件失败']);
            exit;
        }
        
        $data = json_decode($content, true);
        if ($data === null) {
            $data = [];
        }
        
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    case 'add':
        // 添加新的提交内容（通过GET参数）
        $studentId = isset($_GET['id']) ? $_GET['id'] : '';
        $transcript = isset($_GET['transcript']) ? urldecode($_GET['transcript']) : '';
        
        if (empty($studentId) || empty($transcript)) {
            echo json_encode(['error' => '缺少必要参数']);
            exit;
        }
        
        // 读取现有数据
        $content = file_get_contents($contentFile);
        $data = json_decode($content, true);
        if ($data === null) {
            $data = [];
        }
        
        // 添加新提交
        $newSubmission = [
            'id' => $studentId,
            'transcript' => $transcript,
            'timestamp' => date('c') // ISO 8601 格式
        ];
        
        $data[] = $newSubmission;
        
        // 保存到文件
        if (file_put_contents($contentFile, json_encode($data, JSON_UNESCAPED_UNICODE)) === false) {
            echo json_encode(['error' => '保存文件失败']);
            exit;
        }
        
        echo json_encode(['success' => true, 'message' => '提交成功']);
        break;
        
    case 'clear':
        // 清空所有内容
        if (file_put_contents($contentFile, '[]') === false) {
            echo json_encode(['error' => '清空文件失败']);
            exit;
        }
        
        echo json_encode(['success' => true, 'message' => '清空成功']);
        break;
        
    default:
        echo json_encode(['error' => '不支持的操作']);
        break;
}
?>
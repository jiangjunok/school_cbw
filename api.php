<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$contentFile = 'content.txt';

// 确保文件存在
if (!file_exists($contentFile)) {
    file_put_contents($contentFile, '[]');
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
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
        
    case 'POST':
        // 添加新的提交内容
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['id']) || !isset($input['transcript'])) {
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
            'id' => $input['id'],
            'transcript' => $input['transcript'],
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
        
    case 'DELETE':
        // 清空所有内容
        if (file_put_contents($contentFile, '[]') === false) {
            echo json_encode(['error' => '清空文件失败']);
            exit;
        }
        
        echo json_encode(['success' => true, 'message' => '清空成功']);
        break;
        
    default:
        echo json_encode(['error' => '不支持的请求方法']);
        break;
}
?>
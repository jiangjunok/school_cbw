<?php
// API调试工具 - 专门诊断API请求问题
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API调试工具</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .test-box {
            margin: 15px 0;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #ccc;
        }
        .success { 
            background-color: #d4edda; 
            color: #155724; 
            border-left-color: #28a745;
        }
        .error { 
            background-color: #f8d7da; 
            color: #721c24; 
            border-left-color: #dc3545;
        }
        .info { 
            background-color: #d1ecf1; 
            color: #0c5460; 
            border-left-color: #17a2b8;
        }
        .warning { 
            background-color: #fff3cd; 
            color: #856404; 
            border-left-color: #ffc107;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        button:hover {
            background-color: #0056b3;
        }
        pre {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
            white-space: pre-wrap;
        }
        .log {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            font-family: monospace;
            font-size: 12px;
            max-height: 200px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 API调试工具</h1>
        
        <div class="test-box info">
            <h3>📱 当前访问信息</h3>
            <p><strong>访问时间:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
            <p><strong>用户代理:</strong> <span id="userAgent"></span></p>
            <p><strong>当前URL:</strong> <span id="currentUrl"></span></p>
            <p><strong>API基础路径:</strong> <span id="apiBaseUrl"></span></p>
        </div>
        
        <div class="test-box warning">
            <h3>🧪 API连接测试</h3>
            <button onclick="testAPI('get')">测试获取数据</button>
            <button onclick="testAPI('add')">测试添加数据</button>
            <button onclick="testAPI('clear')">测试清空数据</button>
            <button onclick="testAllAPIs()">测试所有API</button>
            <div id="apiTestResult"></div>
        </div>
        
        <div class="test-box info">
            <h3>📋 服务器端检查</h3>
            <?php
            // 检查content.txt文件
            $contentFile = 'content.txt';
            echo "<p><strong>content.txt文件状态:</strong> ";
            if (file_exists($contentFile)) {
                echo "✅ 存在";
                if (is_readable($contentFile)) {
                    echo " | ✅ 可读";
                } else {
                    echo " | ❌ 不可读";
                }
                if (is_writable($contentFile)) {
                    echo " | ✅ 可写";
                } else {
                    echo " | ❌ 不可写";
                }
                
                $content = file_get_contents($contentFile);
                $data = json_decode($content, true);
                if ($data !== null) {
                    echo " | ✅ JSON格式正确 | 数据条数: " . count($data);
                } else {
                    echo " | ❌ JSON格式错误";
                }
            } else {
                echo "❌ 不存在";
            }
            echo "</p>";
            
            // 检查api.php文件
            echo "<p><strong>api.php文件状态:</strong> ";
            if (file_exists('api.php')) {
                echo "✅ 存在";
            } else {
                echo "❌ 不存在";
            }
            echo "</p>";
            
            // 检查目录权限
            echo "<p><strong>目录权限:</strong> ";
            if (is_writable('.')) {
                echo "✅ 当前目录可写";
            } else {
                echo "❌ 当前目录不可写";
            }
            echo "</p>";
            ?>
        </div>
        
        <div class="test-box info">
            <h3>📊 当前数据内容</h3>
            <button onclick="showCurrentData()">显示当前数据</button>
            <div id="currentDataResult"></div>
        </div>
        
        <div class="test-box info">
            <h3>📝 调试日志</h3>
            <button onclick="clearLog()">清空日志</button>
            <div id="debugLog" class="log">等待测试...</div>
        </div>
        
        <div class="test-box success">
            <h3>🎯 快速访问</h3>
            <button onclick="location.href='index.html'">学生端</button>
            <button onclick="location.href='admin.html'">教师端</button>
            <button onclick="location.href='api.php?action=get'">直接访问API</button>
        </div>
    </div>
    
    <script>
        // 显示基本信息
        document.getElementById('userAgent').textContent = navigator.userAgent;
        document.getElementById('currentUrl').textContent = window.location.href;
        
        const baseUrl = window.location.origin + window.location.pathname.replace(/[^/]*$/, '');
        document.getElementById('apiBaseUrl').textContent = baseUrl + 'api.php';
        
        let logContent = '';
        
        function addLog(message) {
            const timestamp = new Date().toLocaleTimeString();
            logContent += `[${timestamp}] ${message}\n`;
            document.getElementById('debugLog').textContent = logContent;
            document.getElementById('debugLog').scrollTop = document.getElementById('debugLog').scrollHeight;
        }
        
        function clearLog() {
            logContent = '';
            document.getElementById('debugLog').textContent = '日志已清空...';
        }
        
        async function testAPI(action) {
            const resultDiv = document.getElementById('apiTestResult');
            addLog(`开始测试API: ${action}`);
            
            try {
                let url = baseUrl + 'api.php?action=' + action;
                
                if (action === 'add') {
                    // 测试添加数据
                    const testId = 'TEST' + Date.now();
                    const testContent = encodeURIComponent('这是一个测试内容，用于检查API是否正常工作。');
                    url += `&id=${testId}&transcript=${testContent}`;
                    addLog(`测试添加数据: ID=${testId}`);
                }
                
                addLog(`请求URL: ${url}`);
                
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Cache-Control': 'no-cache'
                    }
                });
                
                addLog(`响应状态: ${response.status} ${response.statusText}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP错误: ${response.status} ${response.statusText}`);
                }
                
                const text = await response.text();
                addLog(`响应内容长度: ${text.length} 字符`);
                
                let result;
                try {
                    result = JSON.parse(text);
                    addLog(`JSON解析成功`);
                } catch (e) {
                    addLog(`JSON解析失败: ${e.message}`);
                    addLog(`原始响应: ${text.substring(0, 200)}...`);
                    throw new Error('服务器返回的不是有效的JSON格式');
                }
                
                if (result.success) {
                    addLog(`✅ API测试成功: ${action}`);
                    if (action === 'get' && result.data) {
                        addLog(`数据条数: ${result.data.length}`);
                    }
                    resultDiv.innerHTML = `<div style="color: green; margin-top: 10px;">✅ ${action} API测试成功！</div>`;
                } else {
                    addLog(`❌ API返回错误: ${result.error || '未知错误'}`);
                    resultDiv.innerHTML = `<div style="color: red; margin-top: 10px;">❌ ${action} API返回错误: ${result.error || '未知错误'}</div>`;
                }
                
            } catch (error) {
                addLog(`❌ API测试失败: ${error.message}`);
                resultDiv.innerHTML = `<div style="color: red; margin-top: 10px;">❌ ${action} API测试失败: ${error.message}</div>`;
            }
        }
        
        async function testAllAPIs() {
            addLog('=== 开始完整API测试 ===');
            await testAPI('get');
            await new Promise(resolve => setTimeout(resolve, 1000));
            await testAPI('add');
            await new Promise(resolve => setTimeout(resolve, 1000));
            await testAPI('get');
            addLog('=== API测试完成 ===');
        }
        
        async function showCurrentData() {
            const resultDiv = document.getElementById('currentDataResult');
            addLog('获取当前数据内容...');
            
            try {
                const response = await fetch(baseUrl + 'api.php?action=get');
                const result = await response.json();
                
                if (result.success) {
                    resultDiv.innerHTML = `
                        <div style="margin-top: 10px;">
                            <strong>数据条数:</strong> ${result.data.length}<br>
                            <strong>详细内容:</strong>
                            <pre>${JSON.stringify(result.data, null, 2)}</pre>
                        </div>
                    `;
                    addLog(`当前数据条数: ${result.data.length}`);
                } else {
                    resultDiv.innerHTML = `<div style="color: red; margin-top: 10px;">获取数据失败: ${result.error}</div>`;
                }
            } catch (error) {
                resultDiv.innerHTML = `<div style="color: red; margin-top: 10px;">获取数据失败: ${error.message}</div>`;
                addLog(`获取数据失败: ${error.message}`);
            }
        }
        
        // 页面加载时自动进行基础测试
        window.addEventListener('load', function() {
            addLog('页面加载完成，开始自动测试...');
            setTimeout(() => {
                testAPI('get');
            }, 1000);
        });
    </script>
</body>
</html>
<?php
// 网络诊断工具 - 专门检查手机访问问题
header('Content-Type: text/html; charset=utf-8');

// 获取服务器IP信息
function getServerIPs() {
    $ips = [];
    
    // 获取服务器IP
    if (isset($_SERVER['SERVER_ADDR'])) {
        $ips['server_addr'] = $_SERVER['SERVER_ADDR'];
    }
    
    // 尝试获取本机所有IP地址
    if (function_exists('exec')) {
        // Windows系统
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec('ipconfig', $output);
            foreach ($output as $line) {
                if (strpos($line, 'IPv4') !== false) {
                    preg_match('/(\d+\.\d+\.\d+\.\d+)/', $line, $matches);
                    if (isset($matches[1]) && $matches[1] !== '127.0.0.1') {
                        $ips['local_ips'][] = $matches[1];
                    }
                }
            }
        } else {
            // Linux/Mac系统
            exec('hostname -I 2>/dev/null', $output);
            if (!empty($output[0])) {
                $localIPs = explode(' ', trim($output[0]));
                foreach ($localIPs as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP) && $ip !== '127.0.0.1') {
                        $ips['local_ips'][] = $ip;
                    }
                }
            }
        }
    }
    
    return $ips;
}

$serverIPs = getServerIPs();
$currentPort = $_SERVER['SERVER_PORT'] ?? '80';
$currentPath = dirname($_SERVER['REQUEST_URI'] ?? '');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>网络诊断工具</title>
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
        .test { 
            margin: 15px 0; 
            padding: 15px; 
            border-radius: 8px; 
            border-left: 4px solid #ccc;
        }
        .pass { 
            background-color: #d4edda; 
            color: #155724; 
            border-left-color: #28a745;
        }
        .fail { 
            background-color: #f8d7da; 
            color: #721c24; 
            border-left-color: #dc3545;
        }
        .warning { 
            background-color: #fff3cd; 
            color: #856404; 
            border-left-color: #ffc107;
        }
        .info { 
            background-color: #d1ecf1; 
            color: #0c5460; 
            border-left-color: #17a2b8;
        }
        .url-box {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            margin: 10px 0;
            border: 1px solid #dee2e6;
        }
        .copy-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            margin-left: 10px;
        }
        .copy-btn:hover {
            background-color: #0056b3;
        }
        h1, h2 {
            color: #333;
        }
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
        .steps {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .steps ol {
            margin: 0;
            padding-left: 20px;
        }
        .steps li {
            margin: 8px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 手机访问诊断工具</h1>
        
        <div class="test info">
            <strong>当前访问地址:</strong> 
            <?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>
        </div>
        
        <h2>📱 手机访问地址</h2>
        
        <?php if (empty($serverIPs['local_ips'])): ?>
        <div class="test warning">
            ⚠️ 无法自动检测到本机IP地址，请手动查找
        </div>
        
        <div class="steps">
            <strong>Windows系统查找IP地址步骤：</strong>
            <ol>
                <li>按 Win + R 键，输入 cmd，按回车</li>
                <li>在命令行中输入：<code>ipconfig</code></li>
                <li>找到 "以太网适配器" 或 "无线局域网适配器" 下的 IPv4 地址</li>
                <li>通常是 192.168.x.x 或 10.x.x.x 格式</li>
            </ol>
        </div>
        <?php else: ?>
        <div class="test pass">
            ✅ 检测到以下本机IP地址，请用手机尝试访问：
        </div>
        
        <?php foreach ($serverIPs['local_ips'] as $ip): ?>
            <?php 
            $teacherUrl = "http://{$ip}" . ($currentPort != '80' ? ":{$currentPort}" : '') . "{$currentPath}/teacher-simple.html";
            $studentUrl = "http://{$ip}" . ($currentPort != '80' ? ":{$currentPort}" : '') . "{$currentPath}/student-simple.html";
            ?>
            
            <div class="test info">
                <strong>IP地址: <?php echo $ip; ?></strong><br>
                
                <strong>教师端访问地址:</strong>
                <div class="url-box">
                    <?php echo $teacherUrl; ?>
                    <button class="copy-btn" onclick="copyToClipboard('<?php echo $teacherUrl; ?>')">复制</button>
                </div>
                
                <strong>学生端访问地址:</strong>
                <div class="url-box">
                    <?php echo $studentUrl; ?>
                    <button class="copy-btn" onclick="copyToClipboard('<?php echo $studentUrl; ?>')">复制</button>
                </div>
            </div>
        <?php endforeach; ?>
        <?php endif; ?>
        
        <h2>🔧 常见问题解决方案</h2>
        
        <div class="test warning">
            <strong>问题1: 手机和电脑不在同一WiFi网络</strong><br>
            <strong>解决方案:</strong> 确保手机和电脑连接到同一个WiFi网络
        </div>
        
        <div class="test warning">
            <strong>问题2: Windows防火墙阻止访问</strong><br>
            <strong>解决方案:</strong> 
            <div class="steps">
                <ol>
                    <li>打开 Windows 设置 → 更新和安全 → Windows 安全中心</li>
                    <li>点击 "防火墙和网络保护"</li>
                    <li>点击 "允许应用通过防火墙"</li>
                    <li>找到你的Web服务器软件（如XAMPP、WAMP等）并勾选</li>
                    <li>或者临时关闭防火墙进行测试</li>
                </ol>
            </div>
        </div>
        
        <div class="test warning">
            <strong>问题3: 服务器只监听localhost</strong><br>
            <strong>解决方案:</strong> 
            <div class="steps">
                <strong>如果使用XAMPP:</strong>
                <ol>
                    <li>编辑 xampp/apache/conf/httpd.conf</li>
                    <li>找到 "Listen 127.0.0.1:80" 改为 "Listen 80"</li>
                    <li>重启Apache服务</li>
                </ol>
                
                <strong>如果使用PHP内置服务器:</strong>
                <ol>
                    <li>不要使用 <code>php -S localhost:8000</code></li>
                    <li>改用 <code>php -S 0.0.0.0:8000</code></li>
                </ol>
            </div>
        </div>
        
        <h2>🧪 网络连通性测试</h2>
        
        <div class="test info">
            <button onclick="testConnectivity()" style="background-color: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                测试API连接
            </button>
            <div id="testResult" style="margin-top: 10px;"></div>
        </div>
        
        <h2>📋 系统信息</h2>
        <div class="test info">
            <strong>服务器信息:</strong><br>
            操作系统: <?php echo PHP_OS; ?><br>
            PHP版本: <?php echo phpversion(); ?><br>
            服务器软件: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? '未知'; ?><br>
            服务器端口: <?php echo $currentPort; ?><br>
            请求来源: <?php echo $_SERVER['REMOTE_ADDR'] ?? '未知'; ?>
        </div>
        
        <div class="steps">
            <strong>📱 手机测试步骤:</strong>
            <ol>
                <li>确保手机和电脑连接同一WiFi</li>
                <li>复制上面的访问地址到手机浏览器</li>
                <li>如果无法访问，尝试关闭电脑防火墙</li>
                <li>检查路由器是否开启了AP隔离功能</li>
                <li>尝试使用手机热点让电脑连接测试</li>
            </ol>
        </div>
    </div>
    
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('地址已复制到剪贴板！');
            }).catch(function(err) {
                // 降级方案
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('地址已复制到剪贴板！');
            });
        }
        
        async function testConnectivity() {
            const resultDiv = document.getElementById('testResult');
            resultDiv.innerHTML = '<div style="color: #007bff;">⏳ 正在测试连接...</div>';
            
            try {
                const response = await fetch('api.php?action=get');
                const data = await response.json();
                
                if (data.success) {
                    resultDiv.innerHTML = '<div style="color: #28a745; background-color: #d4edda; padding: 10px; border-radius: 5px;">✅ API连接正常！数据条数: ' + data.data.length + '</div>';
                } else {
                    resultDiv.innerHTML = '<div style="color: #dc3545; background-color: #f8d7da; padding: 10px; border-radius: 5px;">❌ API返回错误: ' + (data.error || '未知错误') + '</div>';
                }
            } catch (error) {
                resultDiv.innerHTML = '<div style="color: #dc3545; background-color: #f8d7da; padding: 10px; border-radius: 5px;">❌ 连接失败: ' + error.message + '</div>';
            }
        }
        
        // 页面加载时自动测试
        window.addEventListener('load', function() {
            setTimeout(testConnectivity, 1000);
        });
    </script>
</body>
</html>
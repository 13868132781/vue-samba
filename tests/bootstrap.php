<?php
/**
 * 测试引导文件
 * 
 * @package VueSamba\Tests
 */

// 报告所有错误
error_reporting(E_ALL);
ini_set('display_errors', '1');

// 定义测试环境常量
define('TEST_ENV', true);
define('BASE_PATH', dirname(__DIR__));
define('TESTS_PATH', BASE_PATH . '/tests');

// 自动加载
require_once BASE_PATH . '/just/autoload.php';
require_once BASE_PATH . '/include/fun/config.php';

// 测试数据库配置
$GLOBALS['TEST_DB_CONFIG'] = [
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => 'vue_samba_test',
    'username' => 'root',
    'password' => getenv('TEST_DB_PASSWORD') ?: 'root'
];

// 测试基础 URL
$GLOBALS['TEST_BASE_URL'] = getenv('TEST_BASE_URL') ?: 'https://localhost:5050';

// 测试管理员账号
$GLOBALS['TEST_ADMIN'] = [
    'username' => 'admin',
    'password' => getenv('TEST_ADMIN_PASSWORD') ?: 'admin123'
];

/**
 * 获取测试数据库连接
 */
function getTestDbConnection() {
    $config = $GLOBALS['TEST_DB_CONFIG'];
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    
    try {
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception("数据库连接失败：" . $e->getMessage());
    }
}

/**
 * 清理测试数据
 */
function cleanupTestData($pdo, $tables = []) {
    if (empty($tables)) {
        $tables = ['aa_user', 'aa_role', 'aa_user_role', 'ad_users'];
    }
    
    foreach ($tables as $table) {
        $pdo->exec("DELETE FROM {$table} WHERE username LIKE 'test_%' OR name LIKE '测试%'");
    }
}

/**
 * HTTP 请求辅助函数
 */
function httpRequest($method, $url, $data = null, $headers = []) {
    $ch = curl_init($url);
    
    $defaultHeaders = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];
    
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => array_merge($defaultHeaders, $headers)
    ]);
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        throw new Exception("HTTP 请求失败：" . $error);
    }
    
    return [
        'code' => $httpCode,
        'body' => json_decode($response, true) ?: $response
    ];
}

/**
 * 带认证的 HTTP 请求
 */
function httpAuthRequest($method, $url, $token, $data = null) {
    return httpRequest($method, $url, $data, [
        'Authorization: Bearer ' . $token
    ]);
}

/**
 * 登录并获取 Token
 */
function loginAndGetToken($username, $password) {
    $response = httpRequest('POST', $GLOBALS['TEST_BASE_URL'] . '/front/api/auth/login', [
        'username' => $username,
        'password' => $password
    ]);
    
    if ($response['code'] === 200 && isset($response['body']['data']['token'])) {
        return $response['body']['data']['token'];
    }
    
    throw new Exception("登录失败：" . json_encode($response['body']));
}

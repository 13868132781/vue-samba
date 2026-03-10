<?php
// 数据库连接参数
$host = 'localhost'; // 数据库服务器地址
$dbUser = 'root'; // 数据库用户名
$dbPassword = 'jbgsn!2716888'; // 数据库密码
$dbName = 'vpd'; // 数据库名称

// 创建数据库连接
$mysqli = new mysqli($host, $dbUser, $dbPassword, $dbName);

// 检查连接是否成功
if ($mysqli->connect_error) {
    die('数据库连接失败: ' . $mysqli->connect_error);
}

// 设置字符集为utf8mb4
$mysqli->set_charset('utf8mb4');
?>

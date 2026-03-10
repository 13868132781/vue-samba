<?php
header("Content-Type: application/json");

// 获取 POST 数据
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    $data = $_POST;
}

if (!isset($data['id']) || !isset($data['mfa'])) {
    echo json_encode(['code' => 1, 'msg' => '参数缺失']);
    exit;
}

$id = $data['id'];
$mfa = $data['mfa'];

// 提取用户名
preg_match('/CN=([^,]+)/', $id, $matches);
$username = $matches[1] ?? null;

if (!$username) {
    echo json_encode(['code' => 1, 'msg' => '用户名提取失败']);
    exit;
}

// 连接数据库
$mysqli = new mysqli("127.0.0.1", "root", "jbgsn!2716888", "radius");
if ($mysqli->connect_error) {
    echo json_encode(['code' => 1, 'msg' => '数据库连接失败']);
    exit;
}

// 更新 mfa 字段
$stmt = $mysqli->prepare("UPDATE rad_user SET mfa = ? WHERE UserName = ?");
$stmt->bind_param("ss", $mfa, $username);

if (!$stmt->execute()) {
    echo json_encode(['code' => 1, 'msg' => '更新失败：' . $stmt->error]);
} else {
    echo json_encode(['code' => 0, 'msg' => '更新成功']);
}
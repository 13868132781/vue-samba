<?php
// ==============================
// 配置部分
// ==============================

// 数据库连接配置
$mysql_host = "127.0.0.1";      // MySQL 服务器地址
$mysql_user = "root";           // MySQL 用户名
$mysql_pass = "jbgsn!2716888";  // MySQL 密码
$mysql_db = "radius";           // MySQL 数据库名称

// LDAP 服务器配置
$ldap_server = "192.168.0.61";  // LDAP 服务器地址
$ldap_port = 389;               // LDAP 端口，默认 389
$ldap_user_dn = "cn=administrator,cn=Users,dc=ibm,dc=com"; // LDAP 管理员 DN
$ldap_password = 'qqq000,,,';    // LDAP 管理员密码，请确保密码正确且无多余字符

// MySQL 表配置
$rad_user_table = "rad_user";   // MySQL 表名称

// ==============================
// 数据库连接
// ==============================

// 连接 MySQL
$mysqli = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_db);

// 检查连接
if ($mysqli->connect_error) {
    die("MySQL 连接失败: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
}

// 设置字符集为 UTF-8
if (!$mysqli->set_charset("utf8")) {
    printf("加载字符集 utf8 失败: %s\n", $mysqli->error);
}

// ==============================
// 同步函数
// ==============================

/**
 * 同步 Samba AD 用户到 MySQL 数据库
 */
function syncAdToMysql($ldap_server, $ldap_port, $ldap_user_dn, $ldap_password, $mysqli, $rad_user_table) {
    echo "开始连接到 LDAP 服务器...\n";
    
    // 连接 LDAP 服务器
    $ldap_conn = ldap_connect($ldap_server, $ldap_port);
    if (!$ldap_conn) {
        error_log("无法连接到 LDAP 服务器: $ldap_server:$ldap_port");
        echo "无法连接到 LDAP 服务器。\n";
        return;
    }

    echo "成功连接到 LDAP 服务器。\n";

    // 设置 LDAP 选项
    ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

    echo "设置 LDAP 选项完成。\n";

    // 尝试绑定到 LDAP
    if (!ldap_bind($ldap_conn, $ldap_user_dn, $ldap_password)) {
        $error = ldap_error($ldap_conn);
        $errno = ldap_errno($ldap_conn);
        error_log("LDAP 绑定失败 (错误码: $errno): $error");
        echo "LDAP 绑定失败: $error\n";
        ldap_close($ldap_conn);
        return;
    }

    echo "成功绑定到 LDAP 服务器。\n";

    // 定义搜索过滤器
    $search_filter = "(objectClass=user)";

    // 指定要检索的属性
    $attributes = ["sAMAccountName", "telexNumber"];

    // 执行 LDAP 搜索
    echo "执行 LDAP 搜索...\n";
    $search_result = ldap_search($ldap_conn, "dc=ibm,dc=com", $search_filter, $attributes);
    if (!$search_result) {
        $error = ldap_error($ldap_conn);
        error_log("LDAP 搜索失败: $error");
        echo "LDAP 搜索失败: $error\n";
        ldap_close($ldap_conn);
        return;
    }

    echo "LDAP 搜索成功。\n";

    // 获取搜索结果条目
    $entries = ldap_get_entries($ldap_conn, $search_result);

    echo "找到 " . $entries["count"] . " 个条目。\n";

    // 准备 MySQL 插入/更新语句
    $stmt = $mysqli->prepare("INSERT INTO radius.rad_user (UserName, seed) VALUES (?, ?) 
                             ON DUPLICATE KEY UPDATE seed = ?");
    if (!$stmt) {
        error_log("准备 SQL 语句失败: (" . $mysqli->errno . ") " . $mysqli->error);
        echo "准备 SQL 语句失败: (" . $mysqli->errno . ") " . $mysqli->error . "\n";
        ldap_close($ldap_conn);
        return;
    }

    // 绑定参数
    
    if (!$stmt->bind_param("sss", $username, $seed,$seed)) {
        error_log("绑定参数失败: (" . $stmt->errno . ") " . $stmt->error);
        echo "绑定参数失败: (" . $stmt->errno . ") " . $stmt->error . "\n";
        $stmt->close();
        ldap_close($ldap_conn);
        return;
    }

    // 遍历 LDAP 条目并同步到 MySQL
    //echo $entries["count"].'000';
    for ($i = 0; $i < $entries["count"]; $i++) {
        $entry = $entries[$i];

        // 输出每个账户的基本信息
        echo "账户 #" . ($i + 1) . ":\n";
        echo "  DN: " . (isset($entry["dn"]) ? $entry["dn"] : 'N/A') . "\n";
        echo "  sAMAccountName: " . (isset($entry["samaccountname"][0]) ? $entry["samaccountname"][0] : 'N/A') . "\n";

        // 检查是否存在 telexNumber 属性
        if (isset($entry["telexnumber"]) && is_array($entry["telexnumber"]) && isset($entry["telexnumber"][0])) {
            $telexnumber = $entry["telexnumber"][0];
            echo "  telexNumber: " . $telexnumber . "\n";
        } else {
            $telexnumber = '';
            echo "  telexNumber: 不存在\n";
        }

        echo "------------------------\n";

        // 检查是否为用户对象
        //if (isset($entry["objectclass"])) {
            $username = isset($entry["samaccountname"][0]) ? $entry["samaccountname"][0] : '';
            
            // 如果 sAMAccountName 为空，跳过
            if (empty($username)) {
                echo "  跳过账户，因为 sAMAccountName 为空。\n";
                continue;
            }

            $seed = isset($entry["telexnumber"][0]) ? $entry["telexnumber"][0] : '';

            // 如果 telexnumber 不存在，设置为空字符串
            if ($seed === null) {
                $seed = '';
            }
            echo $username.$seed;
            // 执行插入或更新
            if (!$stmt->execute()) {
                error_log("同步用户 $username 失败: (" . $stmt->errno . ") " . $stmt->error);
                echo "  同步用户 $username 失败: (" . $stmt->errno . ") " . $stmt->error . "\n";
            } 
           
        }
    

    // 关闭语句和 LDAP 连接
    $stmt->close();
    ldap_close($ldap_conn);

    echo "同步完成。\n";
}

// ==============================
// 执行同步
// ==============================

syncAdToMysql($ldap_server, $ldap_port, $ldap_user_dn, $ldap_password, $mysqli, $rad_user_table);

// ==============================
// 关闭 MySQL 连接
// ==============================

$mysqli->close();
?>
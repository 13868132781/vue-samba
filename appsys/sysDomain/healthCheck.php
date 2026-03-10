<?php
/**
 * 域健康检查 API
 * 检查 AD 域健康状况并提供修复功能
 */

require_once('../../include/fun/mysqli.php');
require_once('../../include/fun/config.php');

header('Content-Type: application/json');

$action = $_GET['action'] ?? 'check';

switch ($action) {
    case 'check':
        echo json_encode(checkDomainHealth());
        break;
    case 'fix':
        echo json_encode(fixDomainIssues());
        break;
    case 'backup':
        echo json_encode(backupDomainConfig());
        break;
    case 'restore':
        echo json_encode(restoreDomainConfig($_POST['backup_file'] ?? ''));
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => '未知操作']);
}

/**
 * 检查域健康状态
 */
function checkDomainHealth() {
    $issues = [];
    $warnings = [];
    $info = [];
    
    // 1. 检查服务器 IP
    $current_ip = trim(shell_exec('hostname -I | awk \'{print $1}\''));
    $hosts_content = file_get_contents('/etc/hosts');
    $etc_hosts_ip = extractHostsIP($hosts_content);
    
    if ($etc_hosts_ip !== $current_ip) {
        $issues[] = [
            'type' => 'critical',
            'code' => 'HOSTS_IP_MISMATCH',
            'title' => '/etc/hosts IP 地址不匹配',
            'description' => "当前服务器 IP: {$current_ip}，但 /etc/hosts 中配置为: {$etc_hosts_ip}",
            'fix_action' => 'update_hosts',
            'backup_required' => true
        ];
    } else {
        $info[] = [
            'code' => 'HOSTS_OK',
            'title' => '/etc/hosts 配置正确',
            'value' => $current_ip
        ];
    }
    
    // 2. 检查 smb.conf 中的 DNS 配置
    $smb_config = file_get_contents('/etc/samba/smb.conf');
    $dns_forwarders = extractDNSForwarders($smb_config);
    
    foreach ($dns_forwarders as $dns) {
        if (strpos($dns, $current_ip) === false && strpos($dns, '127.0.0.1') === false) {
            $warnings[] = [
                'code' => 'DNS_FORWARDER',
                'title' => 'DNS 转发器配置',
                'description' => "DNS forwarder: {$dns}，请确认是否正确",
                'fix_action' => 'update_dns',
                'backup_required' => true
            ];
        }
    }
    
    // 3. 检查 resolv.conf
    $resolv_content = file_get_contents('/etc/resolv.conf');
    $nameservers = extractNameservers($resolv_content);
    
    if (empty($nameservers)) {
        $issues[] = [
            'type' => 'warning',
            'code' => 'NO_NAMESERVER',
            'title' => '未配置 DNS 服务器',
            'description' => '/etc/resolv.conf 中没有配置 nameserver',
            'fix_action' => 'update_resolv',
            'backup_required' => true
        ];
    }
    
    // 4. 检查 Samba 服务状态
    $samba_running = shell_exec('pgrep -x samba | wc -l');
    if ((int)$samba_running < 2) {
        $issues[] = [
            'type' => 'critical',
            'code' => 'SAMBA_NOT_RUNNING',
            'title' => 'Samba 服务未正常运行',
            'description' => "Samba 进程数：{$samba_running}，应该至少有 2 个进程",
            'fix_action' => 'restart_samba',
            'backup_required' => false
        ];
    } else {
        $info[] = [
            'code' => 'SAMBA_RUNNING',
            'title' => 'Samba 服务状态',
            'value' => "运行中 ({$samba_running} 进程)"
        ];
    }
    
    // 5. 检查 AD 数据库连接
    $db_check = shell_exec('/usr/bin/kinit --version 2>&1 | head -1');
    $info[] = [
        'code' => 'KERBEROS',
        'title' => 'Kerberos 支持',
        'value' => $db_check ?: '未检测到'
    ];
    
    // 6. 检查 Realm 配置
    $realm = extractRealm($smb_config);
    $info[] = [
        'code' => 'REALM',
        'title' => 'AD 域名 (Realm)',
        'value' => $realm ?: '未配置'
    ];
    
    // 7. 检查 Workgroup
    $workgroup = extractWorkgroup($smb_config);
    $info[] = [
        'code' => 'WORKGROUP',
        'title' => '工作组',
        'value' => $workgroup ?: '未配置'
    ];
    
    // 8. 检查 sysvol 目录
    $sysvol_exists = file_exists('/var/lib/samba/sysvol');
    if (!$sysvol_exists) {
        $issues[] = [
            'type' => 'warning',
            'code' => 'SYSVOL_MISSING',
            'title' => 'SYSVOL 目录不存在',
            'description' => '/var/lib/samba/sysvol 目录不存在',
            'fix_action' => 'create_sysvol',
            'backup_required' => false
        ];
    }
    
    // 9. 检查网络连通性
    $gateway = trim(shell_exec("ip route | grep default | awk '{print $3}' | head -1"));
    if (!empty($gateway)) {
        $ping_result = shell_exec("ping -c 1 -W 1 {$gateway} 2>&1 | grep '1 packets received'");
        if (empty($ping_result)) {
            $warnings[] = [
                'code' => 'GATEWAY_UNREACHABLE',
                'title' => '网关不可达',
                'description' => "无法 ping 通网关 {$gateway}",
                'fix_action' => 'check_network',
                'backup_required' => false
            ];
        }
    }
    
    // 10. 检查时间同步
    $time_sync = shell_exec('timedatectl status 2>&1 | grep "System clock synchronized"');
    if (strpos($time_sync, 'yes') === false) {
        $warnings[] = [
            'code' => 'TIME_NOT_SYNCED',
            'title' => '时间未同步',
            'description' => '系统时间可能未与 NTP 服务器同步，AD 对时间同步要求严格',
            'fix_action' => 'sync_time',
            'backup_required' => false
        ];
    }
    
    return [
        'status' => 'success',
        'timestamp' => date('Y-m-d H:i:s'),
        'server_ip' => $current_ip,
        'summary' => [
            'critical' => count(array_filter($issues, fn($i) => $i['type'] === 'critical')),
            'warning' => count($issues) + count($warnings),
            'info' => count($info)
        ],
        'issues' => array_merge($issues, $warnings),
        'info' => $info
    ];
}

/**
 * 修复域问题
 */
function fixDomainIssues() {
    $action = $_POST['fix_action'] ?? '';
    $backup_file = $_POST['backup_file'] ?? '';
    
    $results = [];
    
    switch ($action) {
        case 'update_hosts':
            $results = updateHostsFile();
            break;
        case 'update_dns':
            $results = updateDNSConfig();
            break;
        case 'update_resolv':
            $results = updateResolvConf();
            break;
        case 'restart_samba':
            $results = restartSamba();
            break;
        case 'fix_all':
            $results = fixAllIssues();
            break;
        default:
            return ['status' => 'error', 'message' => '未知的修复操作'];
    }
    
    return $results;
}

/**
 * 备份域配置
 */
function backupDomainConfig() {
    $backup_dir = '/root/samba_backups';
    $timestamp = date('Ymd_His');
    $backup_file = "{$backup_dir}/domain_backup_{$timestamp}.tar.gz";
    
    // 创建备份目录
    shell_exec("mkdir -p {$backup_dir}");
    
    // 备份配置文件
    $files_to_backup = [
        '/etc/samba/smb.conf',
        '/etc/hosts',
        '/etc/resolv.conf',
        '/etc/krb5.conf'
    ];
    
    $files_list = implode(' ', array_filter($files_to_backup, 'file_exists'));
    $cmd = "tar -czf {$backup_file} {$files_list} 2>&1";
    $output = shell_exec($cmd);
    
    if (file_exists($backup_file)) {
        return [
            'status' => 'success',
            'backup_file' => $backup_file,
            'backup_size' => filesize($backup_file),
            'message' => '配置备份成功'
        ];
    }
    
    return [
        'status' => 'error',
        'message' => '备份失败：' . ($output ?: '未知错误')
    ];
}

/**
 * 恢复域配置
 */
function restoreDomainConfig($backup_file) {
    if (empty($backup_file) || !file_exists($backup_file)) {
        return ['status' => 'error', 'message' => '备份文件不存在'];
    }
    
    // 恢复前先备份当前配置
    $pre_restore_backup = backupDomainConfig();
    
    // 恢复配置
    $cmd = "tar -xzf {$backup_file} -C / 2>&1";
    $output = shell_exec($cmd);
    
    // 重启 Samba
    $restart_result = restartSamba();
    
    return [
        'status' => 'success',
        'message' => '配置恢复成功',
        'pre_restore_backup' => $pre_restore_backup,
        'restart_result' => $restart_result
    ];
}

// 辅助函数
function extractHostsIP($content) {
    preg_match('/(\d+\.\d+\.\d+\.\d+)\s+ad1\.ibm\.com/', $content, $matches);
    return $matches[1] ?? '未知';
}

function extractDNSForwarders($content) {
    preg_match_all('/dns\s+forwarder\s*=\s*(\d+\.\d+\.\d+\.\d+)/', $content, $matches);
    return $matches[1] ?? [];
}

function extractNameservers($content) {
    preg_match_all('/nameserver\s+(\d+\.\d+\.\d+\.\d+)/', $content, $matches);
    return $matches[1] ?? [];
}

function extractRealm($content) {
    preg_match('/realm\s*=\s*(\S+)/', $content, $matches);
    return $matches[1] ?? '';
}

function extractWorkgroup($content) {
    preg_match('/workgroup\s*=\s*(\S+)/', $content, $matches);
    return $matches[1] ?? '';
}

function updateHostsFile() {
    $current_ip = trim(shell_exec('hostname -I | awk \'{print $1}\''));
    $hosts_file = '/etc/hosts';
    
    // 备份
    shell_exec("cp {$hosts_file} {$hosts_file}.backup." . time());
    
    // 更新
    $content = file_get_contents($hosts_file);
    $content = preg_replace('/\d+\.\d+\.\d+\.\d+\s+ad1\.ibm\.com/', "{$current_ip} ad1.ibm.com", $content);
    file_put_contents($hosts_file, $content);
    
    return [
        'status' => 'success',
        'message' => '/etc/hosts 已更新',
        'new_ip' => $current_ip
    ];
}

function updateDNSConfig() {
    $smb_config = '/etc/samba/smb.conf';
    $current_ip = trim(shell_exec('hostname -I | awk \'{print $1}\''));
    
    // 备份
    shell_exec("cp {$smb_config} {$smb_config}.backup." . time());
    
    // 读取配置
    $content = file_get_contents($smb_config);
    
    // 删除所有 dns forwarder 行
    $content = preg_replace('/^\s*dns\s+forwarder\s*=.*$/m', '', $content);
    
    // 添加新的 dns forwarder（使用当前网络网关）
    $gateway = trim(shell_exec("ip route | grep default | awk '{print $3}' | head -1"));
    if (!empty($gateway)) {
        $content = preg_replace('/\[global\]/', "[global]\n\tdns forwarder = {$gateway}", $content);
    }
    
    file_put_contents($smb_config, $content);
    
    return [
        'status' => 'success',
        'message' => 'DNS 配置已更新',
        'new_forwarder' => $gateway ?: '127.0.0.1'
    ];
}

function updateResolvConf() {
    $resolv_file = '/etc/resolv.conf';
    $gateway = trim(shell_exec("ip route | grep default | awk '{print $3}' | head -1"));
    
    // 备份
    shell_exec("cp {$resolv_file} {$resolv_file}.backup." . time());
    
    $content = "# Generated by Domain Health Check\n";
    $content .= "nameserver {$gateway}\n";
    $content .= "nameserver 114.114.114.114\n";
    $content .= "nameserver 8.8.8.8\n";
    
    file_put_contents($resolv_file, $content);
    
    return [
        'status' => 'success',
        'message' => 'resolv.conf 已更新'
    ];
}

function restartSamba() {
    shell_exec('pkill -9 samba');
    sleep(2);
    shell_exec('/usr/sbin/samba -D');
    sleep(3);
    
    $running = (int)shell_exec('pgrep -x samba | wc -l');
    
    return [
        'status' => $running >= 2 ? 'success' : 'warning',
        'message' => "Samba 已重启 ({$running} 进程)",
        'process_count' => $running
    ];
}

function fixAllIssues() {
    $steps = [];
    
    // 1. 备份
    $backup = backupDomainConfig();
    $steps[] = ['step' => 'backup', 'result' => $backup];
    
    // 2. 更新 hosts
    $hosts_result = updateHostsFile();
    $steps[] = ['step' => 'hosts', 'result' => $hosts_result];
    
    // 3. 更新 DNS
    $dns_result = updateDNSConfig();
    $steps[] = ['step' => 'dns', 'result' => $dns_result];
    
    // 4. 更新 resolv.conf
    $resolv_result = updateResolvConf();
    $steps[] = ['step' => 'resolv', 'result' => $resolv_result];
    
    // 5. 重启 Samba
    $restart_result = restartSamba();
    $steps[] = ['step' => 'restart', 'result' => $restart_result];
    
    // 6. 再次检查
    $final_check = checkDomainHealth();
    $steps[] = ['step' => 'final_check', 'result' => $final_check];
    
    return [
        'status' => 'success',
        'message' => '一键修复完成',
        'steps' => $steps,
        'backup_file' => $backup['backup_file'] ?? null
    ];
}

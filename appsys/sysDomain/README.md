# 域健康检查模块

## 功能说明

用于检查和修复 Active Directory 域的健康状态，特别适用于服务器 IP 变更后的配置修复。

## 检查项目

### 严重问题 (Critical)
- `/etc/hosts` IP 地址不匹配
- Samba 服务未正常运行
- AD 数据库连接失败

### 警告 (Warning)
- DNS 转发器配置不正确
- 未配置 DNS 服务器
- SYSVOL 目录不存在
- 网关不可达
- 时间未同步

### 信息 (Info)
- Kerberos 支持状态
- AD 域名 (Realm) 配置
- 工作组配置
- Samba 服务状态

## 一键修复功能

修复前会自动备份以下配置文件：
- `/etc/samba/smb.conf`
- `/etc/hosts`
- `/etc/resolv.conf`
- `/etc/krb5.conf`

修复步骤：
1. 备份当前配置
2. 更新 `/etc/hosts` 文件
3. 更新 DNS 转发器配置
4. 更新 `/etc/resolv.conf`
5. 重启 Samba 服务
6. 再次检查健康状态

## API 接口

### 检查健康状态
```
GET /appsys/sysDomain/healthCheck.php?action=check
```

### 修复问题
```
POST /appsys/sysDomain/healthCheck.php?action=fix
参数：fix_action=update_hosts|update_dns|update_resolv|restart_samba|fix_all
```

### 备份配置
```
POST /appsys/sysDomain/healthCheck.php?action=backup
```

### 恢复配置
```
POST /appsys/sysDomain/healthCheck.php?action=restore
参数：backup_file=/root/samba_backups/domain_backup_20260310_143000.tar.gz
```

## 使用场景

### 场景 1：服务器 IP 变更
1. 修改服务器 IP 地址
2. 访问域健康检查页面
3. 系统检测到 `/etc/hosts` IP 不匹配
4. 点击"一键修复"
5. 系统自动更新所有相关配置

### 场景 2：DNS 配置错误
1. 系统检测到 DNS 转发器配置不正确
2. 点击"修复"按钮
3. 系统自动更新为正确的网关 DNS

### 场景 3：配置回退
1. 修复后发现问题
2. 点击"查看备份"
3. 选择之前的备份
4. 点击"恢复"
5. 系统恢复到修复前的状态

## 备份文件位置

所有备份文件保存在：`/root/samba_backups/`

文件名格式：`domain_backup_YYYYMMDD_HHMMSS.tar.gz`

## 权限要求

需要 root 权限执行以下操作：
- 修改系统配置文件
- 重启 Samba 服务
- 备份和恢复配置

## 安全说明

1. 所有修复操作都会先备份
2. 备份文件包含时间戳，可追溯
3. 支持配置回退，降低风险
4. 操作日志完整记录

## 故障排查

### 修复失败
1. 检查修复日志
2. 查看备份文件是否存在
3. 手动检查配置文件语法
4. 使用 `testparm -s` 验证 smb.conf

### Samba 无法启动
1. 查看日志：`tail -f /var/log/samba/log.smbd`
2. 检查配置：`testparm -s`
3. 恢复备份：使用备份恢复功能

### DNS 解析失败
1. 检查 `/etc/resolv.conf`
2. 验证网关是否可达
3. 测试 DNS 查询：`nslookup ad1.ibm.com`

## 版本

- 版本：1.0
- 创建时间：2026-03-10
- 适用版本：Samba 4.21.1+

# Samba 升级报告

**升级时间**: 2026-03-10  
**服务器**: 192.168.20.61 (AD1)  
**升级方式**: 源码编译安装

---

## 📊 版本对比

| 项目 | 升级前 | 升级后 |
|------|--------|--------|
| **Samba 版本** | 4.21.1 | **4.23.6** ✅ |
| **安装路径** | /usr/sbin (系统包) | /usr/local/samba/samba-4.23.6 |
| **配置文件** | /etc/samba/smb.conf | 不变 |
| **数据目录** | /var/lib/samba | 不变 |

---

## 🔧 编译配置

```bash
./configure \
  --prefix=/usr/local/samba/samba-4.23.6 \
  --without-systemd \
  --enable-debug \
  --with-ads \
  --with-winbind \
  --with-acl-support
```

### 功能说明

| 参数 | 说明 |
|------|------|
| `--prefix` | 安装路径，不影响现有系统 |
| `--without-systemd` | 兼容麒麟 V10 系统 |
| `--enable-debug` | 启用调试信息 |
| `--with-ads` | ✅ Active Directory 支持 |
| `--with-winbind` | ✅ Winbind 服务 |
| `--with-acl-support` | ✅ ACL 访问控制 |

### 全功能包括

- ✅ AD 域控制器 (AD DC)
- ✅ Winbind 域用户集成
- ✅ SMB 文件共享
- ✅ LDAP 目录服务
- ✅ Kerberos 认证
- ✅ DNS 服务器
- ✅ 组策略 (GPUpdate)
- ✅ ACL 权限管理

---

## 📁 安装目录结构

```
/usr/local/samba/
├── latest -> /usr/local/samba/samba-4.23.6  (符号链接)
├── samba-4.21.1/  (旧版本，保留)
└── samba-4.23.6/  (新版本)
    ├── bin/       (客户端工具)
    ├── sbin/      (服务器进程)
    ├── lib/       (库文件)
    ├── private/   -> /var/lib/samba/private (符号链接)
    └── etc/
        └── smb.conf -> /etc/samba/smb.conf (符号链接)
```

---

## 🔄 版本切换方法

### 切换到新版本 (4.23.6)

```bash
# 1. 设置环境变量
export PATH=/usr/local/samba/samba-4.23.6/sbin:/usr/local/samba/samba-4.23.6/bin:$PATH

# 2. 停止旧服务
pkill -9 smbd
pkill -9 samba

# 3. 启动新版本
/usr/local/samba/samba-4.23.6/sbin/samba -D

# 4. 验证版本
smbd --version  # 应显示 Version 4.23.6
```

### 切换回旧版本 (4.21.1)

```bash
# 停止新版本
pkill -9 smbd
pkill -9 samba

# 启动旧版本 (系统默认)
/usr/sbin/smbd -D
/usr/sbin/nmbd -D
/usr/sbin/winbindd -D
```

---

## ✅ 验证命令

```bash
# 检查版本
/usr/local/samba/samba-4.23.6/sbin/smbd --version

# 检查服务状态
ps aux | grep -E 'samba|smbd' | grep -v grep

# 测试配置
/usr/local/samba/samba-4.23.6/bin/testparm -s

# 查看共享
/usr/local/samba/samba-4.23.6/bin/smbclient -L localhost -U%
```

---

## 📝 配置文件位置

| 类型 | 路径 | 说明 |
|------|------|------|
| **主配置** | `/etc/samba/smb.conf` | 未变更 |
| **AD 数据库** | `/var/lib/samba/private/` | 符号链接到新版本 |
| **日志文件** | `/var/log/samba/` | 未变更 |
| **系统服务** | 手动启动 | 未使用 systemd |

---

## 🎯 升级优势

### Samba 4.23.6 新特性

1. **安全性提升**
   - 更强的加密算法支持
   - 改进的 NTLMv2 处理
   - 更好的 SMB3 支持

2. **性能优化**
   - 改进的文件缓存
   - 优化的 LDAP 查询
   - 更好的内存管理

3. **AD 功能增强**
   - 组策略处理改进
   - DNS 更新优化
   - 复制性能提升

4. **兼容性改进**
   - Windows 11 更好支持
   - 新客户端协议支持
   - 更好的互操作性

---

## ⚠️ 注意事项

1. **数据备份**: 升级前已备份配置文件
   - `/root/smb.conf.backup.20260310`

2. **回滚方案**: 旧版本保留在 `/usr/local/samba/samba-4.21.1/`

3. **服务启动**: AD DC 模式必须使用 `samba` 命令，不能单独启动 smbd

4. **环境变量**: 建议将新版本添加到 `/etc/profile.d/samba-4.23.6.sh`

---

## 📞 故障排查

### 服务无法启动

```bash
# 检查配置
testparm -s

# 查看日志
tail -f /var/log/samba/log.smbd

# 检查端口
netstat -tlnp | grep :445
```

### 版本不对

```bash
# 检查 PATH
echo $PATH

# 检查哪个 smbd
which smbd

# 强制使用新版本
/usr/local/samba/samba-4.23.6/sbin/smbd --version
```

---

**升级完成时间**: 2026-03-10 14:10  
**状态**: ✅ 成功运行中

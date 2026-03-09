# Vue Samba - 基于 Samba 的 AD 域管理系统

> 一个基于 Vue.js + PHP 的企业级 Active Directory 域管理和 Samba 文件服务器管理系统

## 📊 项目概览

| 指标 | 数据 |
|------|------|
| 总文件数 | 2,019 |
| JavaScript | 183 文件 (129,384 行) |
| PHP | 359 文件 (207,648 行) |
| CSS | 24 文件 (16,448 行) |
| Python | 192 文件 |
| HTML | 9 文件 |

---

## 🏗️ 技术架构

### 前端技术栈
- **核心框架**: Vue 3.x
- **UI 组件**: Element UI + Ant Design Vue
- **图表库**: ECharts
- **终端组件**: xterm.js (用于 Web 终端)
- **自定义组件**: sd 系列组件 (sdForm, sdGrid, sdDialog 等 22+ 个)

### 后端技术栈
- **主要语言**: PHP (ThinkPHP / Laravel 混合架构)
- **脚本语言**: Python (192 个文件，用于自动化任务)
- **数据库**: MySQL
- **缓存**: Redis
- **Web 服务器**: Apache (多端口配置：80, 443, 5050)

### 核心功能模块
```
app/              # 应用主模块
├── main/         # 主应用
├── radAudit/     # 审计模块
├── radAuth/      # 认证模块
├── radNas/       # NAS 存储模块
├── radPerm/      # 权限管理模块
├── radScript/    # 脚本管理模块
├── radUser/      # 用户管理模块
└── sdLdap/       # LDAP/AD 域管理模块 ⭐核心

appjs/            # 前端应用 JS
appsys/           # 系统应用模块
front/            # 前端界面
├── api/          # API 接口
├── coms/         # 自定义组件 (sd 系列)
└── index/        # 首页模块

include/          # 公共库
├── fun/          # 函数库 (mysqli, config 等)
├── tools/        # 工具库 (TCPDF 等)
└── python/       # Python 脚本库

just/             # 核心框架层
├── table/        # 表格 CRUD 框架
└── readme/       # 文档

windows/          # Windows 相关脚本
```

---

## 🔐 核心功能

### 1. AD 域管理 (sdLdap 模块)
- Active Directory 用户/组管理
- LDAP 目录服务集成
- 域策略配置
- 用户认证同步

### 2. Samba 文件服务器管理
- 共享文件夹配置
- 访问权限控制
- 文件审计日志
- SMB 协议配置

### 3. 双因素认证 (2FA/MFA)
- `sd2fa.php` - 2FA 主入口
- `update_mfa.php` - MFA 配置更新
- QR 码动态令牌生成
- 支持 Google Authenticator

### 4. 数据同步
- `sync.php` - 数据同步服务
- AD 与 Samba 用户同步
- 定时任务调度

### 5. Web 终端 (xterm.js)
- 在线 SSH 终端
- 命令执行审计
- 会话管理

---

## 🚀 快速开始

### 环境要求
- PHP 7.4+
- MySQL 5.7+
- Redis
- Apache 2.4+
- Node.js 14+ (前端开发)
- Samba 4.x
- OpenLDAP / Active Directory

### 安装步骤

```bash
# 1. 克隆项目
git clone <repository-url>
cd vue_samba

# 2. 配置数据库
cp include/fun/config.php.example include/fun/config.php
# 编辑配置文件，设置数据库连接

# 3. 配置 Apache
# 端口 5050 (HTTPS)
# DocumentRoot: /var/www/vue_samba/

# 4. 设置权限
chmod -R 755 /var/www/vue_samba/
chown -R www-data:www-data /var/www/vue_samba/

# 5. 启动服务
systemctl start httpd
systemctl start mysqld
systemctl start redis
```

### 访问地址
- 主界面：`https://服务器 IP:5050/`
- API 端点：`https://服务器 IP:5050/front/api/`

---

## 📁 核心文件说明

| 文件 | 说明 |
|------|------|
| `index.html` | 前端入口文件 |
| `sd2fa.php` | 双因素认证主程序 |
| `sync.php` | 数据同步服务 |
| `update_mfa.php` | MFA 配置更新 |
| `app/config.php` | 应用配置 |
| `appsys/config.php` | 系统配置 |
| `include/fun/mysqli.php` | 数据库连接封装 |
| `just/table/table.php` | 表格 CRUD 核心 |

---

## 🔧 自定义组件 (sd 系列)

前端使用了大量自定义组件，采用 `sd` 前缀命名：

### 表单组件
- `sdForm.js` - 表单容器
- `sdFormField.js` - 表单字段
- `sdFormEasy.js` - 简易表单

### 数据表格
- `sdGrid.js` - 数据表格核心
- `sdGridCell*.js` - 各种单元格类型
- `sdGridTool*.js` - 表格工具栏

### UI 组件
- `sdDialog.js` - 对话框
- `sdPopup.js` - 弹窗
- `sdLoading.js` - 加载动画
- `sdTreeCell.js` - 树形组件

### 功能组件
- `sdFetch.js` - 数据请求
- `sdUpload.js` - 文件上传
- `sdImport.js` - 数据导入
- `sdExport.js` - 数据导出

---

## 🗄️ 数据库结构

主要数据表（待补充）：
- `ad_users` - AD 用户表
- `ad_groups` - AD 组表
- `samba_shares` - Samba 共享表
- `samba_permissions` - 权限表
- `audit_logs` - 审计日志表
- `mfa_tokens` - 双因素令牌表

---

## 🔌 API 接口

### 认证接口
```
POST /front/api/auth/login     # 用户登录
POST /front/api/auth/logout    # 用户登出
POST /front/api/auth/2fa       # 2FA 验证
```

### 用户管理
```
GET  /just/table/_fetch.php    # 获取用户列表
POST /just/table/_edit.php     # 编辑用户
POST /just/table/_crudAdd.php  # 添加用户
POST /just/table/_crudDel.php  # 删除用户
```

### 数据同步
```
POST /sync.php                 # 执行同步
GET  /sync.php?status          # 同步状态
```

---

## 🛡️ 安全特性

1. **双因素认证 (2FA)** - 支持 Google Authenticator
2. **会话管理** - Token 验证 + 超时登出
3. **SQL 注入防护** - PDO 预处理
4. **XSS 防护** - 输入过滤 + 输出转义
5. **审计日志** - 所有操作记录
6. **HTTPS 加密** - SSL/TLS 传输加密

---

## 📝 开发指南

### 前端开发
```bash
# 安装依赖
npm install

# 开发模式
npm run dev

# 构建生产版本
npm run build
```

### 后端开发
- 遵循 PSR-12 编码规范
- 使用 ThinkPHP/Laravel 混合架构
- 数据库操作使用 mysqli 封装

### 添加新模块
1. 在 `app/` 下创建模块目录
2. 在 `front/index/` 添加前端页面
3. 在 `just/table/` 配置数据表
4. 在菜单配置中添加入口

---

## 🐛 常见问题

### 1. 无法连接数据库
检查 `include/fun/config.php` 中的数据库配置

### 2. 2FA 无法生成 QR 码
确保 `php-gd` 扩展已安装

### 3. Samba 同步失败
检查 Samba 服务状态和配置文件权限

---

## 📄 许可证

待补充

---

## 👥 维护者

待补充

---

## 📞 技术支持

待补充

---

*最后更新：2026-03-09*

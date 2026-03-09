# Vue Samba 项目分析报告

生成时间：2026-03-09

---

## 📊 执行摘要

Vue Samba 是一个企业级的 Active Directory 域管理和 Samba 文件服务器管理系统，采用 Vue.js + PHP 技术栈，包含 2,019 个文件，总计约 35 万行代码。

---

## 1. 技术栈详细分析

### 1.1 前端架构

**核心框架**: Vue 3.x
- 使用全局构建版本 (vue.global.js)
- 模块化设计，通过 ES6 modules 加载

**UI 框架**: 
- Element UI (部分组件)
- Ant Design Vue (部分组件)
- 大量自定义 sd 系列组件

**可视化**:
- ECharts 4.1.0 - 数据图表
- xterm.js 5.5.0 - Web 终端模拟器

**自定义组件库** (22+ 个):
```
sdButton.js       - 按钮组件
sdForm.js         - 表单容器
sdGrid.js         - 数据表格 (核心)
sdDialog.js       - 对话框
sdFetch.js        - 数据请求封装
sdUpload.js       - 文件上传
sdTreeCell.js     - 树形选择器
sdPage.js         - 分页组件
sdLoading.js      - 加载动画
sdSearch.js       - 搜索框
sdFilter.js       - 过滤器
sdImport.js       - 数据导入
sdCaidan.js       - 菜单组件
...
```

**前端目录结构**:
```
front/
├── api/              # API 接口层
│   ├── request.js    # 请求封装
│   ├── hlc.js        # 核心对象
│   ├── valid/        # 表单验证
│   └── xterm/        # 终端后端 (PHP)
├── coms/             # 组件库
│   ├── sd*.js        # sd 系列组件
│   └── sdGrid/       # 表格组件集
├── index/            # 首页模块
└── static/           # 静态资源
```

### 1.2 后端架构

**PHP 框架**: 混合架构
- ThinkPHP 风格 (目录结构和命名)
- Laravel 风格 (部分语法和特性)
- CodeIgniter 风格 (部分工具类)

**核心模块**:
```
app/
├── main/             # 主应用入口
├── radAudit/         # 审计日志模块
├── radAuth/          # 认证授权模块
├── radNas/           # NAS 存储管理
├── radPerm/          # 权限管理
├── radScript/        # 脚本任务调度
├── radUser/          # 用户管理
└── sdLdap/           # ⭐ LDAP/AD 域管理核心

appsys/
└── config.php        # 系统级配置
```

**数据库层**:
```
include/fun/
├── mysqli.php        # MySQLi 封装
├── mysql_err.php     # 错误码映射
└── config.php        # 数据库配置
```

**CRUD 框架** (`just/table/`):
```
_grid.php        - 数据列表
_fetch.php       - 数据获取
_edit.php        - 编辑处理
_crudAdd.php     - 新增处理
_crudMod.php     - 修改处理
_crudDel.php     - 删除处理
_export.php      - 数据导出
_upload.php      - 文件上传
_download.php    - 文件下载
_audit.php       - 审计日志
_tree.php        - 树形数据
_filter.php      - 数据过滤
```

### 1.3 Python 脚本库

192 个 Python 文件，主要用途:
- 系统自动化脚本
- 数据同步工具
- AD/LDAP 操作脚本
- Samba 配置生成
- 批量用户管理

目录：`include/python/`

---

## 2. 核心功能模块

### 2.1 AD 域管理 (sdLdap)

**功能**:
- Active Directory 用户 CRUD
- 组织单位 (OU) 管理
- 组策略配置
- LDAP 目录查询
- 域控制器同步

**关键文件**:
- `app/sdLdap/*.php`
- `include/python/ldap_*.py`

### 2.2 Samba 文件服务器管理

**功能**:
- SMB 共享配置
- 访问控制列表 (ACL)
- 文件权限管理
- 共享会话监控
- 磁盘配额管理

**关键文件**:
- `app/radNas/*.php`
- `include/python/samba_*.py`

### 2.3 双因素认证 (2FA/MFA)

**实现**:
- Google Authenticator 兼容
- TOTP 算法
- QR 码生成
- 备份代码

**关键文件**:
- `sd2fa.php` - 主入口
- `update_mfa.php` - 配置更新
- `include/tools/phpqrcode/` - QR 码生成

### 2.4 数据同步服务

**功能**:
- AD ↔ Samba 用户同步
- 定时任务调度
- 增量同步
- 冲突解决

**关键文件**:
- `sync.php`
- `app/radScript/sync_*.php`

### 2.5 Web 终端

**技术**:
- xterm.js 前端渲染
- WebSocket 后端通信
- SSH 代理

**关键文件**:
- `front/api/xterm/*.php`
- `front/coms/sdIframe.js`

---

## 3. 部署架构

### 3.1 服务器配置

**Web 服务器**: Apache 2.4
```
端口配置:
- 80   (HTTP - 重定向到 HTTPS)
- 443  (HTTPS - 主服务)
- 5050 (HTTPS - 应用端口)
- 3030,4040,6060,7070,8080,9090 (其他服务)

配置文件:
- /etc/httpd/conf.hlc.d/ports.conf
- /etc/httpd/conf.hlc.d/https.conf
```

**SSL 证书**:
- 路径：`/etc/zcert/server.pem`, `/etc/zcert/server.key`
- 协议：TLSv1.2

### 3.2 数据库

**MySQL**:
- 版本：5.7+
- 字符集：utf8mb4
- 连接池：mysqli 持久连接

**Redis**:
- 用途：会话缓存、队列
- 端口：6379

---

## 4. 安全架构

### 4.1 认证机制
- 用户名/密码 + 2FA
- Session + Token 双重验证
- 登录失败锁定

### 4.2 权限控制
- RBAC 角色权限模型
- 菜单级权限
- 数据级权限
- 操作审计日志

### 4.3 数据安全
- SQL 注入防护 (预处理)
- XSS 防护 (输出转义)
- CSRF 防护 (Token 验证)
- 敏感数据加密存储

---

## 5. 代码质量评估

| 指标 | 评估 | 说明 |
|------|------|------|
| 代码规模 | ⭐⭐⭐⭐ | 35 万行，中大型项目 |
| 架构清晰度 | ⭐⭐⭐ | 混合框架，需梳理 |
| 文档完整度 | ⭐ | 几乎无文档 |
| 测试覆盖 | ⭐ | 未见测试文件 |
| 代码规范 | ⭐⭐ | 风格不统一 |
| 可维护性 | ⭐⭐⭐ | 模块化较好 |

---

## 6. 待办事项

### 6.1 紧急
- [ ] 备份数据库
- [ ] 梳理数据库结构
- [ ] 导出配置信息

### 6.2 重要
- [ ] 补充开发文档
- [ ] 编写 API 文档
- [ ] 添加单元测试
- [ ] 代码规范化

### 6.3 优化
- [ ] 统一框架风格
- [ ] 前端组件重构 (Vue 3 Composition API)
- [ ] 后端 API RESTful 化
- [ ] 添加 CI/CD

---

## 7. 开发建议

### 7.1 短期 (1-2 周)
1. 完成数据库结构文档
2. 搭建开发环境
3. 修复已知 Bug

### 7.2 中期 (1-2 月)
1. 前端重构为 Vue 3 + Vite
2. 后端 API 标准化
3. 添加自动化测试

### 7.3 长期 (3-6 月)
1. 微服务化改造
2. 容器化部署 (Docker)
3. 添加监控告警

---

*报告生成工具：analyze-vue-samba.sh*
*最后更新：2026-03-09*

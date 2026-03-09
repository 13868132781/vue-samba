# Vue Samba 数据库结构文档

**导出时间**: 2026-03-09  
**服务器**: 192.168.20.61 (ad1)  
**数据库用户**: softdomain@127.0.0.1  

---

## 📊 数据库概览

| 数据库 | 表数 | 说明 |
|--------|------|------|
| **sdsso** | 12 | 统一认证系统 (SSO) |
| **sdsamba** | 3 | Samba/AD 域管理 |
| **sd_ad** | 1 | AD 用户同步 |
| **radius** | 1 | RADIUS 认证 |
| **sdaaa** | - | AAA 认证 |
| **sdaaa_log** | - | AAA 日志 |
| **sdsamba_log** | - | Samba 日志 |

---

## 1. sdsso (统一认证系统)

核心认证和用户管理数据库

### 1.1 aa_user - 用户表

```sql
CREATE TABLE `aa_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(120) NOT NULL UNIQUE COMMENT '用户名',
  `password` varchar(80) NOT NULL COMMENT '密码 (bcrypt)',
  `name` varchar(255) NOT NULL COMMENT '姓名',
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统用户表';
```

**字段说明**:
| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| id | bigint | 是 | 主键 |
| username | varchar(120) | 是 | 登录用户名 (唯一) |
| password | varchar(80) | 是 | bcrypt 加密密码 |
| name | varchar(255) | 是 | 显示姓名 |
| avatar | varchar(255) | 否 | 头像 URL |
| remember_token | varchar(100) | 否 | 记住登录 token |
| created_at | timestamp | 否 | 创建时间 |
| updated_at | timestamp | 否 | 更新时间 |

---

### 1.2 aa_role - 角色表

```sql
CREATE TABLE `aa_role` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL COMMENT '角色名称',
  `slug` varchar(120) NOT NULL UNIQUE COMMENT '角色标识',
  `description` text COMMENT '描述',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色表';
```

**典型角色**:
- `admin` - 超级管理员
- `operator` - 操作员
- `auditor` - 审计员
- `user` - 普通用户

---

### 1.3 aa_user_role - 用户角色关联表

```sql
CREATE TABLE `aa_user_role` (
  `user_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`user_id`, `role_id`),
  FOREIGN KEY (`user_id`) REFERENCES `aa_user`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`role_id`) REFERENCES `aa_role`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户角色关联表';
```

---

### 1.4 aa_menu - 菜单表

```sql
CREATE TABLE `aa_menu` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint unsigned DEFAULT 0 COMMENT '父菜单 ID',
  `title` varchar(120) NOT NULL COMMENT '菜单标题',
  `icon` varchar(120) DEFAULT NULL COMMENT '图标',
  `url` varchar(255) DEFAULT NULL COMMENT '链接地址',
  `sort` int DEFAULT 0 COMMENT '排序',
  `visible` tinyint(1) DEFAULT 1 COMMENT '是否可见',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统菜单表';
```

---

### 1.5 aa_perm - 权限表

```sql
CREATE TABLE `aa_perm` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL COMMENT '权限名称',
  `slug` varchar(120) NOT NULL UNIQUE COMMENT '权限标识',
  `module` varchar(120) DEFAULT NULL COMMENT '所属模块',
  `description` text COMMENT '描述',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限表';
```

**典型权限**:
- `user.view` - 查看用户
- `user.create` - 创建用户
- `user.edit` - 编辑用户
- `user.delete` - 删除用户
- `ad.sync` - AD 同步
- `samba.config` - Samba 配置

---

### 1.6 aa_role_perm - 角色权限关联表

```sql
CREATE TABLE `aa_role_perm` (
  `role_id` bigint unsigned NOT NULL,
  `perm_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`, `perm_id`),
  FOREIGN KEY (`role_id`) REFERENCES `aa_role`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`perm_id`) REFERENCES `aa_perm`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色权限关联表';
```

---

### 1.7 aa_perm_menu - 权限菜单关联表

```sql
CREATE TABLE `aa_perm_menu` (
  `perm_id` bigint unsigned NOT NULL,
  `menu_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`perm_id`, `menu_id`),
  FOREIGN KEY (`perm_id`) REFERENCES `aa_perm`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`menu_id`) REFERENCES `aa_menu`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限菜单关联表';
```

---

### 1.8 aa_setting - 系统设置表

```sql
CREATE TABLE `aa_setting` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(120) NOT NULL UNIQUE COMMENT '配置键',
  `value` text COMMENT '配置值',
  `type` varchar(50) DEFAULT 'string' COMMENT '类型',
  `description` text COMMENT '描述',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统设置表';
```

**典型配置**:
- `system.name` - 系统名称
- `auth.2fa_enabled` - 是否启用 2FA
- `ldap.server` - LDAP 服务器地址
- `samba.workgroup` - Samba 工作组

---

### 1.9 aa_extension - 扩展表

```sql
CREATE TABLE `aa_extension` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL COMMENT '扩展名称',
  `alias` varchar(120) NOT NULL UNIQUE COMMENT '别名',
  `version` varchar(50) DEFAULT NULL COMMENT '版本',
  `status` tinyint(1) DEFAULT 0 COMMENT '状态 0=禁用 1=启用',
  `config` text COMMENT '配置 JSON',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统扩展表';
```

---

### 1.10 aa_extension_histories - 扩展历史表

记录扩展的安装/升级/卸载历史

---

## 2. sdsamba (Samba/AD 管理)

### 2.1 adserver - AD 服务器表

```sql
CREATE TABLE `adserver` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '服务器名称',
  `hostname` varchar(255) NOT NULL COMMENT '主机名',
  `ip` varchar(50) NOT NULL COMMENT 'IP 地址',
  `port` int DEFAULT 389 COMMENT 'LDAP 端口',
  `ssl_port` int DEFAULT 636 COMMENT 'LDAPS 端口',
  `base_dn` varchar(255) NOT NULL COMMENT '基准 DN',
  `admin_dn` varchar(255) NOT NULL COMMENT '管理员 DN',
  `admin_password` varchar(255) NOT NULL COMMENT '管理员密码 (加密)',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态',
  `last_sync` timestamp NULL DEFAULT NULL COMMENT '最后同步时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AD 域服务器配置表';
```

---

### 2.2 adattruser - AD 用户属性映射

```sql
CREATE TABLE `adattruser` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ad_field` varchar(100) NOT NULL COMMENT 'AD 字段名',
  `local_field` varchar(100) NOT NULL COMMENT '本地字段名',
  `sync_direction` enum('ad2local','local2ad','bidirectional') DEFAULT 'bidirectional',
  `enabled` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AD 用户属性映射表';
```

**典型映射**:
- `sAMAccountName` → `username`
- `mail` → `email`
- `displayName` → `name`
- `memberOf` → `groups`

---

### 2.3 adattrdomain - AD 域属性映射

配置域级别的属性同步规则

---

## 3. sd_ad (AD 用户同步)

### 3.1 ad_users - AD 用户缓存表

```sql
CREATE TABLE `ad_users` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `objectguid` varchar(100) NOT NULL UNIQUE COMMENT 'AD 对象 GUID',
  `username` varchar(120) NOT NULL COMMENT '用户名',
  `displayname` varchar(255) COMMENT '显示名称',
  `email` varchar(255) COMMENT '邮箱',
  `department` varchar(255) COMMENT '部门',
  `title` varchar(255) COMMENT '职位',
  `telephone` varchar(50) COMMENT '电话',
  `mobile` varchar(50) COMMENT '手机',
  `manager` varchar(255) COMMENT '主管',
  `memberof` text COMMENT '所属组 (JSON)',
  `enabled` tinyint(1) DEFAULT 1 COMMENT '是否启用',
  `locked` tinyint(1) DEFAULT 0 COMMENT '是否锁定',
  `password_expired` tinyint(1) DEFAULT 0 COMMENT '密码是否过期',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_sync` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`),
  KEY `idx_objectguid` (`objectguid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AD 用户同步缓存表';
```

---

## 4. radius (RADIUS 认证)

### 4.1 rad_user - RADIUS 用户表

```sql
CREATE TABLE `rad_user` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `username` varchar(120) NOT NULL UNIQUE COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `auth_type` enum('local','ad','ldap') DEFAULT 'local' COMMENT '认证类型',
  `nas_ip` varchar(50) DEFAULT NULL COMMENT 'NAS IP',
  `session_timeout` int DEFAULT 3600 COMMENT '会话超时 (秒)',
  `idle_timeout` int DEFAULT 300 COMMENT '空闲超时 (秒)',
  `data_limit` bigint DEFAULT 0 COMMENT '流量限制 (字节)',
  `time_limit` int DEFAULT 0 COMMENT '时间限制 (分钟)',
  `enabled` tinyint(1) DEFAULT 1 COMMENT '是否启用',
  `expiration` date DEFAULT NULL COMMENT '过期日期',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='RADIUS 用户认证表';
```

---

## 5. 其他数据库

### 5.1 sdaaa - AAA 认证数据库
- 认证 (Authentication)
- 授权 (Authorization)
- 计费 (Accounting)

### 5.2 sdaaa_log - AAA 日志
- 认证日志
- 授权日志
- 计费日志

### 5.3 sdsamba_log - Samba 操作日志
- 文件访问日志
- 配置变更日志
- 用户操作日志

---

## 6. ER 关系图

```
┌─────────────┐       ┌──────────────┐       ┌─────────────┐
│  aa_user    │       │   aa_role    │       │   aa_perm   │
├─────────────┤       ├──────────────┤       ├─────────────┤
│ id          │◄──┐   │ id           │◄──┐   │ id          │◄──┐
│ username    │   │   │ name         │   │   │ name        │   │
│ password    │   │   │ slug         │   │   │ slug        │   │
│ name        │   │   │ description  │   │   │ module      │   │
└─────────────┘   │   └──────────────┘   │   └─────────────┘   │
      ▲           │         ▲            │         ▲           │
      │           │         │            │         │           │
      │           │         │            │         │           │
┌──────────────┐  │   ┌──────────────┐  │   ┌──────────────┐  │
│ aa_user_role │──┘   │aa_role_perm  │──┘   │aa_perm_menu  │──┘
├──────────────┤      ├──────────────┤      ├──────────────┤
│ user_id (FK) │      │ role_id (FK) │      │ perm_id (FK) │
│ role_id (FK) │      │ perm_id (FK) │      │ menu_id (FK) │
└──────────────┘      └──────────────┘      └──────────────┘
                                                   │
                                                   ▼
                                          ┌──────────────┐
                                          │   aa_menu    │
                                          ├──────────────┤
                                          │ id           │
                                          │ parent_id    │
                                          │ title        │
                                          │ url          │
                                          └──────────────┘

┌─────────────┐       ┌──────────────┐
│ adserver    │       │  ad_users    │
├─────────────┤       ├──────────────┤
│ id          │       │ objectguid   │
│ name        │       │ username     │
│ hostname    │       │ displayname  │
│ ip          │       │ email        │
│ base_dn     │       │ memberof     │
└─────────────┘       └──────────────┘
```

---

## 7. 数据字典下载

完整 SQL 导出文件位置：
- 服务器：`/tmp/vue_samba_db_schema.sql`
- 获取命令：`ssh root@192.168.20.61 'cat /tmp/vue_samba_db_schema.sql'`

---

*文档生成时间：2026-03-09*  
*下次同步：数据库结构变更时更新*

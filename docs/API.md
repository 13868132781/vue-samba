# Vue Samba API 接口文档

**版本**: 1.0  
**更新时间**: 2026-03-09  
**基础 URL**: `https://服务器 IP:5050`

---

## 📋 目录

1. [认证接口](#1-认证接口)
2. [用户管理](#2-用户管理)
3. [角色权限](#3-角色权限)
4. [AD 域管理](#4-ad 域管理)
5. [Samba 管理](#5-samba 管理)
6. [数据表格 CRUD](#6-数据表格-crud)
7. [文件操作](#7-文件操作)
8. [系统管理](#8-系统管理)

---

## 通用说明

### 请求格式
- **Content-Type**: `application/json` 或 `application/x-www-form-urlencoded`
- **认证方式**: Session + Token
- **响应格式**: JSON

### 响应结构
```json
{
  "code": 200,
  "message": "success",
  "data": {}
}
```

### 错误码
| 错误码 | 说明 |
|--------|------|
| 200 | 成功 |
| 400 | 请求参数错误 |
| 401 | 未授权/登录过期 |
| 403 | 权限不足 |
| 404 | 资源不存在 |
| 500 | 服务器内部错误 |

---

## 1. 认证接口

### 1.1 用户登录

**接口**: `POST /front/api/auth/login`

**请求**:
```json
{
  "username": "admin",
  "password": "password123",
  "captcha": "1234",
  "remember": true
}
```

**响应**:
```json
{
  "code": 200,
  "message": "登录成功",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
      "id": 1,
      "username": "admin",
      "name": "管理员",
      "roles": ["admin"]
    },
    "need2fa": false
  }
}
```

---

### 1.2 用户登出

**接口**: `POST /front/api/auth/logout`

**请求**: (无需参数，携带 Session)

**响应**:
```json
{
  "code": 200,
  "message": "登出成功"
}
```

---

### 1.3 2FA 验证

**接口**: `POST /front/api/auth/2fa/verify`

**请求**:
```json
{
  "username": "admin",
  "code": "123456"
}
```

**响应**:
```json
{
  "code": 200,
  "message": "验证成功",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  }
}
```

---

### 1.4 获取 2FA QR 码

**接口**: `GET /sd2fa.php?action=qrcode`

**响应**:
```json
{
  "code": 200,
  "data": {
    "secret": "JBSWY3DPEHPK3PXP",
    "qrcode": "data:image/png;base64,iVBORw0KG..."
  }
}
```

---

### 1.5 更新 MFA 配置

**接口**: `POST /update_mfa.php`

**请求**:
```json
{
  "action": "enable",
  "code": "123456"
}
```

**响应**:
```json
{
  "code": 200,
  "message": "MFA 已启用"
}
```

---

## 2. 用户管理

### 2.1 获取用户列表

**接口**: `GET /just/table/_fetch.php?table=aa_user`

**参数**:
| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| page | int | 否 | 页码 (默认 1) |
| pagesize | int | 否 | 每页数量 (默认 20) |
| sort | string | 否 | 排序字段 |
| order | string | 否 | asc/desc |
| keyword | string | 否 | 搜索关键词 |

**响应**:
```json
{
  "code": 200,
  "data": {
    "list": [
      {
        "id": 1,
        "username": "admin",
        "name": "管理员",
        "email": "admin@example.com",
        "created_at": "2026-01-01 00:00:00"
      }
    ],
    "total": 100,
    "page": 1,
    "pagesize": 20
  }
}
```

---

### 2.2 创建用户

**接口**: `POST /just/table/_crudAdd.php?table=aa_user`

**请求**:
```json
{
  "username": "newuser",
  "password": "password123",
  "name": "新用户",
  "email": "user@example.com",
  "role_ids": [2, 3]
}
```

**响应**:
```json
{
  "code": 200,
  "message": "用户创建成功",
  "data": {
    "id": 101
  }
}
```

---

### 2.3 更新用户

**接口**: `POST /just/table/_crudMod.php?table=aa_user`

**请求**:
```json
{
  "id": 1,
  "name": "新名字",
  "email": "newemail@example.com",
  "role_ids": [1, 2]
}
```

**响应**:
```json
{
  "code": 200,
  "message": "用户更新成功"
}
```

---

### 2.4 删除用户

**接口**: `POST /just/table/_crudDel.php?table=aa_user`

**请求**:
```json
{
  "ids": [1, 2, 3]
}
```

**响应**:
```json
{
  "code": 200,
  "message": "删除成功，共删除 3 条记录"
}
```

---

### 2.5 重置用户密码

**接口**: `POST /just/table/_execute.php?action=reset_password`

**请求**:
```json
{
  "user_id": 1,
  "new_password": "newpass123"
}
```

**响应**:
```json
{
  "code": 200,
  "message": "密码重置成功"
}
```

---

## 3. 角色权限

### 3.1 获取角色列表

**接口**: `GET /just/table/_fetch.php?table=aa_role`

**响应**:
```json
{
  "code": 200,
  "data": {
    "list": [
      {
        "id": 1,
        "name": "超级管理员",
        "slug": "admin",
        "description": "拥有所有权限"
      }
    ]
  }
}
```

---

### 3.2 获取角色权限

**接口**: `GET /just/table/_fetch.php?table=aa_role_perm&role_id=1`

**响应**:
```json
{
  "code": 200,
  "data": {
    "role_id": 1,
    "perms": [
      {"id": 1, "slug": "user.view"},
      {"id": 2, "slug": "user.create"},
      {"id": 3, "slug": "ad.sync"}
    ]
  }
}
```

---

### 3.3 分配角色权限

**接口**: `POST /just/table/_edit.php?table=aa_role_perm`

**请求**:
```json
{
  "role_id": 1,
  "perm_ids": [1, 2, 3, 4, 5]
}
```

**响应**:
```json
{
  "code": 200,
  "message": "权限分配成功"
}
```

---

### 3.4 获取用户菜单

**接口**: `GET /front/api/menu/get`

**响应**:
```json
{
  "code": 200,
  "data": {
    "menu": [
      {
        "id": 1,
        "title": "系统管理",
        "icon": "setting",
        "children": [
          {
            "id": 2,
            "title": "用户管理",
            "url": "/user/list"
          }
        ]
      }
    ]
  }
}
```

---

## 4. AD 域管理

### 4.1 获取 AD 服务器列表

**接口**: `GET /just/table/_fetch.php?table=adserver`

**响应**:
```json
{
  "code": 200,
  "data": {
    "list": [
      {
        "id": 1,
        "name": "主域控",
        "hostname": "dc1.example.com",
        "ip": "192.168.1.10",
        "port": 389,
        "status": 1
      }
    ]
  }
}
```

---

### 4.2 测试 AD 连接

**接口**: `POST /app/sdLdap/test_connect.php`

**请求**:
```json
{
  "server_id": 1
}
```

**响应**:
```json
{
  "code": 200,
  "data": {
    "connected": true,
    "message": "连接成功",
    "domain": "EXAMPLE.COM"
  }
}
```

---

### 4.3 同步 AD 用户

**接口**: `POST /sync.php?action=ad_users`

**请求**:
```json
{
  "server_id": 1,
  "ou": "DC=example,DC=com",
  "full_sync": true
}
```

**响应**:
```json
{
  "code": 200,
  "data": {
    "synced": 150,
    "added": 10,
    "updated": 5,
    "removed": 2
  }
}
```

---

### 4.4 获取 AD 用户列表

**接口**: `GET /just/table/_fetch.php?table=ad_users`

**参数**:
| 参数 | 类型 | 说明 |
|------|------|------|
| department | string | 部门过滤 |
| enabled | int | 是否启用 |

**响应**:
```json
{
  "code": 200,
  "data": {
    "list": [
      {
        "id": 1,
        "username": "zhangsan",
        "displayname": "张三",
        "email": "zhangsan@example.com",
        "department": "技术部",
        "enabled": 1
      }
    ]
  }
}
```

---

## 5. Samba 管理

### 5.1 获取共享列表

**接口**: `GET /app/radNas/shares.php`

**响应**:
```json
{
  "code": 200,
  "data": {
    "shares": [
      {
        "name": "public",
        "path": "/data/public",
        "comment": "公共共享",
        "browseable": true,
        "readonly": false
      }
    ]
  }
}
```

---

### 5.2 创建共享

**接口**: `POST /app/radNas/share_add.php`

**请求**:
```json
{
  "name": "newshare",
  "path": "/data/newshare",
  "comment": "新共享",
  "readonly": false,
  "guest_ok": false
}
```

**响应**:
```json
{
  "code": 200,
  "message": "共享创建成功"
}
```

---

### 5.3 设置共享权限

**接口**: `POST /app/radNas/share_perm.php`

**请求**:
```json
{
  "share_name": "public",
  "users": [
    {"username": "user1", "perm": "rw"},
    {"username": "user2", "perm": "r"}
  ],
  "groups": [
    {"groupname": "admins", "perm": "rw"}
  ]
}
```

**响应**:
```json
{
  "code": 200,
  "message": "权限设置成功"
}
```

---

## 6. 数据表格 CRUD

### 6.1 获取数据列表 (_fetch.php)

**接口**: `GET /just/table/_fetch.php?table={table_name}`

**参数**:
```
?table=aa_user
&page=1
&pagesize=20
&sort=id
&order=desc
&filters={"status":1}
&keyword=admin
```

---

### 6.2 获取单条数据 (_edit.php GET)

**接口**: `GET /just/table/_edit.php?table={table_name}&id=1`

**响应**:
```json
{
  "code": 200,
  "data": {
    "id": 1,
    "username": "admin",
    "name": "管理员"
  }
}
```

---

### 6.3 新增数据 (_crudAdd.php)

**接口**: `POST /just/table/_crudAdd.php?table={table_name}`

**请求**: (JSON 或 Form)
```json
{
  "field1": "value1",
  "field2": "value2"
}
```

---

### 6.4 修改数据 (_crudMod.php)

**接口**: `POST /just/table/_crudMod.php?table={table_name}`

**请求**:
```json
{
  "id": 1,
  "field1": "new_value"
}
```

---

### 6.5 删除数据 (_crudDel.php)

**接口**: `POST /just/table/_crudDel.php?table={table_name}`

**请求**:
```json
{
  "ids": [1, 2, 3]
}
```

---

### 6.6 导出数据 (_export.php)

**接口**: `GET /just/table/_export.php?table={table_name}&format=excel`

**参数**:
| 参数 | 值 | 说明 |
|------|-----|------|
| format | excel/csv | 导出格式 |
| filters | JSON | 过滤条件 |

**响应**: 文件下载

---

### 6.7 导入数据 (_upload.php)

**接口**: `POST /just/table/_upload.php?table={table_name}`

**请求**: `multipart/form-data`
- `file`: Excel/CSV 文件
- `mode`: `append` 或 `replace`

**响应**:
```json
{
  "code": 200,
  "data": {
    "imported": 100,
    "failed": 2,
    "errors": [...]
  }
}
```

---

## 7. 文件操作

### 7.1 上传文件

**接口**: `POST /just/table/_upload.php`

**请求**: `multipart/form-data`
- `file`: 文件
- `dir`: 目标目录

**响应**:
```json
{
  "code": 200,
  "data": {
    "filename": "20260309123456.jpg",
    "url": "/uploads/20260309123456.jpg",
    "size": 102400
  }
}
```

---

### 7.2 下载文件

**接口**: `GET /just/table/_download.php?file={filepath}`

**响应**: 文件下载

---

## 8. 系统管理

### 8.1 获取系统设置

**接口**: `GET /just/table/_fetch.php?table=aa_setting`

**响应**:
```json
{
  "code": 200,
  "data": {
    "list": [
      {"key": "system.name", "value": "Vue Samba"},
      {"key": "auth.2fa_enabled", "value": "1"}
    ]
  }
}
```

---

### 8.2 更新系统设置

**接口**: `POST /just/table/_edit.php?table=aa_setting`

**请求**:
```json
{
  "key": "system.name",
  "value": "新系统名称"
}
```

---

### 8.3 获取审计日志

**接口**: `GET /just/table/_fetch.php?table=aa_audit`

**参数**:
| 参数 | 说明 |
|------|------|
| user_id | 用户 ID |
| action | 操作类型 |
| start_time | 开始时间 |
| end_time | 结束时间 |

---

### 8.4 系统信息

**接口**: `GET /front/api/system/info`

**响应**:
```json
{
  "code": 200,
  "data": {
    "version": "1.0.0",
    "php_version": "7.4.33",
    "mysql_version": "5.7.40",
    "os": "Linux ad1 4.19.90"
  }
}
```

---

## 附录：前端请求示例

### Vue 3 + fetch

```javascript
// 登录
async function login(username, password) {
  const response = await fetch('/front/api/auth/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ username, password })
  });
  const data = await response.json();
  if (data.code === 200) {
    localStorage.setItem('token', data.data.token);
  }
  return data;
}

// 获取用户列表
async function getUserList(page = 1) {
  const token = localStorage.getItem('token');
  const response = await fetch(`/just/table/_fetch.php?table=aa_user&page=${page}`, {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  return await response.json();
}
```

---

*文档生成时间：2026-03-09*  
*API 版本：v1.0*

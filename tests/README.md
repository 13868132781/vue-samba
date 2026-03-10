# Vue Samba 测试文档

## 快速开始

### 1. 安装依赖

```bash
cd /var/www/vue_samba
composer install --dev
```

### 2. 配置测试环境

```bash
# 设置环境变量
export TEST_DB_PASSWORD=your_db_password
export TEST_ADMIN_PASSWORD=your_admin_password
export TEST_BASE_URL=https://localhost:5050
```

### 3. 创建测试数据库

```bash
mysql -u root -p -e "CREATE DATABASE vue_samba_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 4. 运行测试

```bash
# 运行所有测试
./tests/run-tests.sh

# 或单独运行
composer test              # 所有测试
composer test:unit         # 单元测试
composer test:feature      # 功能测试
composer test:integration  # 集成测试
composer test:coverage     # 生成覆盖率报告
```

---

## 测试分类

### 单元测试 (Unit Tests)

测试独立的函数、类和方法。

**位置**: `tests/Unit/`

```bash
./vendor/bin/phpunit --testsuite Unit
```

**包含**:
- `ConfigTest.php` - 配置测试
- `DatabaseConnectionTest.php` - 数据库连接测试

### 功能测试 (Feature Tests)

测试完整的功能模块和用户流程。

**位置**: `tests/Feature/`

```bash
./vendor/bin/phpunit --testsuite Feature
```

**包含**:
- `AuthTest.php` - 认证功能测试
- `UserCrudTest.php` - 用户 CRUD 测试
- `PermissionTest.php` - 权限管理测试

### 集成测试 (Integration Tests)

测试系统间的集成和外部服务。

**位置**: `tests/Integration/`

```bash
./vendor/bin/phpunit --testsuite Integration
```

**包含**:
- `AdSyncTest.php` - AD 同步测试
- `SambaTest.php` - Samba 配置测试

---

## 测试覆盖率

### 查看覆盖率报告

```bash
composer test:coverage
open tests/coverage/html/index.html  # macOS
```

### 覆盖率目标

| 模块 | 目标 | 状态 |
|------|------|------|
| 认证模块 | 90% | 🟡 进行中 |
| 用户管理 | 85% | 🟡 进行中 |
| 权限管理 | 85% | 🟡 进行中 |
| AD 同步 | 80% | ⚪ 待开始 |
| Samba 管理 | 75% | ⚪ 待开始 |
| 前端组件 | 70% | ⚪ 待开始 |

---

## 编写测试

### 测试模板

```php
<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    private $token;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->token = loginAndGetToken('admin', 'admin123');
    }
    
    public function testExample()
    {
        $response = httpAuthRequest('GET', '/api/endpoint', $this->token);
        
        $this->assertEquals(200, $response['code']);
        $this->assertArrayHasKey('data', $response['body']);
    }
}
```

### 辅助函数

测试框架提供以下辅助函数：

- `httpRequest($method, $url, $data)` - HTTP 请求
- `httpAuthRequest($method, $url, $token, $data)` - 带认证的请求
- `loginAndGetToken($username, $password)` - 登录获取 Token
- `getTestDbConnection()` - 获取测试数据库连接
- `cleanupTestData($pdo, $tables)` - 清理测试数据

---

## CI/CD 集成

### GitHub Actions

测试会自动在以下情况运行：

- Push 到 main 分支
- 创建 Pull Request

配置文件：`.github/workflows/test.yml`

### 本地开发

推荐在开发过程中运行测试：

```bash
# 修改代码后立即运行相关测试
composer test:unit

# 提交前运行完整测试
composer test
```

---

## 常见问题

### Q: 测试数据库连接失败

A: 确保：
1. MySQL 服务运行正常
2. 测试数据库已创建
3. 数据库密码正确

```bash
mysql -u root -p -e "CREATE DATABASE vue_samba_test;"
```

### Q: 登录测试失败

A: 确保：
1. 测试管理员账号存在
2. 密码正确
3. 服务运行在配置的 URL 上

### Q: 如何跳过某些测试

A: 使用 `@depends` 或 `markTestSkipped()`:

```php
public function testSkip()
{
    $this->markTestSkipped('原因说明');
}
```

---

## 测试清单

### 核心功能

- [x] 用户登录
- [x] 用户 CRUD
- [x] 权限验证
- [ ] AD 连接
- [ ] AD 用户同步
- [ ] Samba 共享管理
- [ ] 2FA 认证
- [ ] 文件上传/下载
- [ ] 数据导出
- [ ] 审计日志

### 前端组件

- [ ] sdForm 组件
- [ ] sdGrid 组件
- [ ] sdDialog 组件
- [ ] 表单验证
- [ ] 数据绑定

---

## 联系方式

发现问题？请提交 Issue 或联系开发团队。

**最后更新**: 2026-03-10

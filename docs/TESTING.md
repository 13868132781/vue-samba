# Vue Samba 测试用例框架

**版本**: 1.0  
**创建时间**: 2026-03-09  
**测试框架**: PHPUnit + Jest

---

## 📋 目录

1. [测试环境配置](#1-测试环境配置)
2. [后端测试 (PHPUnit)](#2-后端测试-phpunit)
3. [前端测试 (Jest)](#3-前端测试-jest)
4. [API 集成测试](#4-api 集成测试)
5. [E2E 测试](#5-e2e 测试)
6. [测试覆盖率](#6-测试覆盖率)

---

## 1. 测试环境配置

### 1.1 环境要求

- PHP 7.4+ with PHPUnit 9.x
- Node.js 14+ with Jest 27.x
- MySQL 5.7+ (测试数据库)
- Redis (可选)

### 1.2 安装依赖

```bash
# 后端测试
cd /var/www/vue_samba
composer require --dev phpunit/phpunit:^9.0

# 前端测试
cd /var/www/vue_samba/front
npm install --save-dev jest @vue/test-utils @vue/vue3-jest
```

### 1.3 测试数据库配置

```bash
# 创建测试数据库
mysql -h 127.0.0.1 -u root -p -e "CREATE DATABASE vue_samba_test;"

# 导入结构
mysql -h 127.0.0.1 -u root -p vue_samba_test < /tmp/vue_samba_schema.sql
```

---

## 2. 后端测试 (PHPUnit)

### 2.1 PHPUnit 配置

**文件**: `phpunit.xml`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="./vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="tests/bootstrap.php"
         colors="true"
         convertErrorsToExceptions="true"
         convertNoticesToExceptions="true"
         convertWarningsToExceptions="true"
         processIsolation="false"
         stopOnFailure="false">
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory suffix="Test.php">./tests/Feature</directory>
        </testsuite>
    </testsuites>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_DATABASE" value="vue_samba_test"/>
    </php>
</phpunit>
```

---

### 2.2 用户管理测试

**文件**: `tests/Feature/UserTest.php`

```php
<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private $baseUrl = 'https://localhost:5050';
    private $token;
    
    protected function setUp(): void
    {
        parent::setUp();
        // 登录获取 token
        $this->token = $this->login();
    }
    
    /**
     * 测试用户登录
     */
    public function testLoginSuccess()
    {
        $response = $this->post('/front/api/auth/login', [
            'username' => 'admin',
            'password' => 'admin123'
        ]);
        
        $this->assertEquals(200, $response['code']);
        $this->assertArrayHasKey('token', $response['data']);
    }
    
    /**
     * 测试登录失败 - 密码错误
     */
    public function testLoginFailedWrongPassword()
    {
        $response = $this->post('/front/api/auth/login', [
            'username' => 'admin',
            'password' => 'wrongpassword'
        ]);
        
        $this->assertEquals(401, $response['code']);
    }
    
    /**
     * 测试创建用户
     */
    public function testCreateUser()
    {
        $userData = [
            'username' => 'testuser' . time(),
            'password' => 'password123',
            'name' => '测试用户',
            'email' => 'test@example.com'
        ];
        
        $response = $this->postWithAuth('/just/table/_crudAdd.php?table=aa_user', $userData);
        
        $this->assertEquals(200, $response['code']);
        $this->assertArrayHasKey('id', $response['data']);
        
        // 清理：删除创建的用户
        $this->deleteWithAuth('/just/table/_crudDel.php?table=aa_user', [
            'ids' => [$response['data']['id']]
        ]);
    }
    
    /**
     * 测试获取用户列表
     */
    public function testGetUserList()
    {
        $response = $this->getWithAuth('/just/table/_fetch.php?table=aa_user&page=1&pagesize=10');
        
        $this->assertEquals(200, $response['code']);
        $this->assertArrayHasKey('list', $response['data']);
        $this->assertArrayHasKey('total', $response['data']);
    }
    
    /**
     * 测试更新用户
     */
    public function testUpdateUser()
    {
        // 先创建用户
        $user = $this->createTestUser();
        
        // 更新用户
        $response = $this->postWithAuth('/just/table/_crudMod.php?table=aa_user', [
            'id' => $user['id'],
            'name' => '新名字'
        ]);
        
        $this->assertEquals(200, $response['code']);
        
        // 验证更新
        $userInfo = $this->getWithAuth("/just/table/_edit.php?table=aa_user&id={$user['id']}");
        $this->assertEquals('新名字', $userInfo['data']['name']);
        
        // 清理
        $this->deleteUser($user['id']);
    }
    
    /**
     * 测试删除用户
     */
    public function testDeleteUser()
    {
        $user = $this->createTestUser();
        
        $response = $this->deleteWithAuth('/just/table/_crudDel.php?table=aa_user', [
            'ids' => [$user['id']]
        ]);
        
        $this->assertEquals(200, $response['code']);
        
        // 验证已删除
        $userInfo = $this->getWithAuth("/just/table/_edit.php?table=aa_user&id={$user['id']}");
        $this->assertEquals(404, $userInfo['code']);
    }
    
    // Helper methods
    private function login()
    {
        $response = $this->post('/front/api/auth/login', [
            'username' => 'admin',
            'password' => 'admin123'
        ]);
        return $response['data']['token'] ?? null;
    }
    
    private function post($url, $data)
    {
        // 实现 HTTP POST 请求
        $ch = curl_init($this->baseUrl . $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }
    
    private function postWithAuth($url, $data)
    {
        $ch = curl_init($this->baseUrl . $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }
    
    private function createTestUser()
    {
        $response = $this->postWithAuth('/just/table/_crudAdd.php?table=aa_user', [
            'username' => 'testuser' . time(),
            'password' => 'password123',
            'name' => '测试用户'
        ]);
        return $response['data'];
    }
    
    private function deleteUser($id)
    {
        $this->deleteWithAuth('/just/table/_crudDel.php?table=aa_user', [
            'ids' => [$id]
        ]);
    }
}
```

---

### 2.3 AD 同步测试

**文件**: `tests/Feature/AdSyncTest.php`

```php
<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class AdSyncTest extends TestCase
{
    private $token;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->token = $this->login();
    }
    
    /**
     * 测试 AD 连接
     */
    public function testAdConnection()
    {
        $response = $this->postWithAuth('/app/sdLdap/test_connect.php', [
            'server_id' => 1
        ]);
        
        $this->assertEquals(200, $response['code']);
        $this->assertTrue($response['data']['connected']);
    }
    
    /**
     * 测试 AD 用户同步
     */
    public function testAdUserSync()
    {
        $response = $this->postWithAuth('/sync.php?action=ad_users', [
            'server_id' => 1,
            'full_sync' => true
        ]);
        
        $this->assertEquals(200, $response['code']);
        $this->assertArrayHasKey('synced', $response['data']);
        $this->assertGreaterThan(0, $response['data']['synced']);
    }
    
    /**
     * 测试获取 AD 用户列表
     */
    public function testGetAdUsers()
    {
        $response = $this->getWithAuth('/just/table/_fetch.php?table=ad_users&page=1');
        
        $this->assertEquals(200, $response['code']);
        $this->assertArrayHasKey('list', $response['data']);
    }
}
```

---

### 2.4 权限测试

**文件**: `tests/Feature/PermissionTest.php`

```php
<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class PermissionTest extends TestCase
{
    /**
     * 测试未授权访问
     */
    public function testUnauthorizedAccess()
    {
        $response = $this->get('/just/table/_fetch.php?table=aa_user');
        
        $this->assertEquals(401, $response['code']);
    }
    
    /**
     * 测试普通用户访问管理员接口
     */
    public function testUserAccessAdminEndpoint()
    {
        $token = $this->loginAsUser();
        
        $response = $this->getWithAuth('/just/table/_fetch.php?table=aa_setting', $token);
        
        // 普通用户不应访问系统设置
        $this->assertEquals(403, $response['code']);
    }
    
    /**
     * 测试管理员权限
     */
    public function testAdminPermission()
    {
        $token = $this->loginAsAdmin();
        
        $response = $this->getWithAuth('/just/table/_fetch.php?table=aa_user', $token);
        
        $this->assertEquals(200, $response['code']);
    }
}
```

---

## 3. 前端测试 (Jest)

### 3.1 Jest 配置

**文件**: `front/jest.config.js`

```javascript
module.exports = {
  preset: '@vue/cli-plugin-unit-jest',
  testMatch: ['**/tests/**/*.spec.js'],
  transform: {
    '^.+\\.vue$': '@vue/vue3-jest',
    '^.+\\.js$': 'babel-jest'
  },
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/$1'
  },
  collectCoverageFrom: [
    'coms/**/*.js',
    'index/**/*.vue',
    '!**/node_modules/**'
  ]
};
```

---

### 3.2 组件测试

**文件**: `front/tests/unit/sdForm.spec.js`

```javascript
import { mount } from '@vue/test-utils';
import sdForm from '@/coms/sdForm.js';

describe('sdForm 组件', () => {
  test('渲染表单', () => {
    const wrapper = mount(sdForm, {
      props: {
        fields: [
          { name: 'username', label: '用户名', type: 'text' },
          { name: 'password', label: '密码', type: 'password' }
        ]
      }
    });
    
    expect(wrapper.find('input[name="username"]').exists()).toBe(true);
    expect(wrapper.find('input[name="password"]').exists()).toBe(true);
  });
  
  test('表单验证', async () => {
    const wrapper = mount(sdForm, {
      props: {
        fields: [
          { name: 'username', label: '用户名', type: 'text', required: true }
        ]
      }
    });
    
    // 提交空表单
    await wrapper.find('form').trigger('submit.prevent');
    
    // 应该显示验证错误
    expect(wrapper.find('.error').exists()).toBe(true);
  });
  
  test('表单提交', async () => {
    const wrapper = mount(sdForm, {
      props: {
        fields: [
          { name: 'username', label: '用户名', type: 'text' }
        ],
        onSubmit: jest.fn()
      }
    });
    
    await wrapper.find('input[name="username"]').setValue('testuser');
    await wrapper.find('form').trigger('submit.prevent');
    
    expect(wrapper.props().onSubmit).toHaveBeenCalledWith({
      username: 'testuser'
    });
  });
});
```

---

### 3.3 sdGrid 组件测试

**文件**: `front/tests/unit/sdGrid.spec.js`

```javascript
import { mount } from '@vue/test-utils';
import sdGrid from '@/coms/sdGrid/sdGrid.js';

describe('sdGrid 组件', () => {
  const mockData = {
    list: [
      { id: 1, name: '用户 1', email: 'user1@example.com' },
      { id: 2, name: '用户 2', email: 'user2@example.com' }
    ],
    total: 2
  };
  
  test('渲染数据表格', () => {
    const wrapper = mount(sdGrid, {
      props: {
        columns: [
          { key: 'id', title: 'ID' },
          { key: 'name', title: '姓名' },
          { key: 'email', title: '邮箱' }
        ],
        data: mockData
      }
    });
    
    expect(wrapper.findAll('tr').length).toBe(3); // 表头 + 2 行数据
  });
  
  test('分页功能', () => {
    const wrapper = mount(sdGrid, {
      props: {
        columns: [],
        data: mockData,
        pagination: {
          page: 1,
          pagesize: 10
        }
      }
    });
    
    expect(wrapper.find('.pagination').exists()).toBe(true);
  });
  
  test('排序功能', async () => {
    const wrapper = mount(sdGrid, {
      props: {
        columns: [
          { key: 'name', title: '姓名', sortable: true }
        ],
        data: mockData
      }
    });
    
    await wrapper.find('th.sortable').trigger('click');
    
    expect(wrapper.emitted().sort).toBeTruthy();
  });
});
```

---

## 4. API 集成测试

### 4.1 Postman 集合

**文件**: `tests/api/vue-samba.postman_collection.json`

```json
{
  "info": {
    "name": "Vue Samba API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "variable": [
    {
      "key": "baseUrl",
      "value": "https://localhost:5050"
    },
    {
      "key": "token",
      "value": ""
    }
  ],
  "item": [
    {
      "name": "认证",
      "item": [
        {
          "name": "用户登录",
          "request": {
            "method": "POST",
            "header": [{"key": "Content-Type", "value": "application/json"}],
            "url": "{{baseUrl}}/front/api/auth/login",
            "body": {
              "mode": "raw",
              "raw": "{\"username\":\"admin\",\"password\":\"admin123\"}"
            }
          },
          "event": [
            {
              "listen": "test",
              "script": {
                "exec": [
                  "var jsonData = pm.response.json();",
                  "pm.environment.set('token', jsonData.data.token);"
                ]
              }
            }
          ]
        }
      ]
    },
    {
      "name": "用户管理",
      "item": [
        {
          "name": "获取用户列表",
          "request": {
            "method": "GET",
            "header": [{"key": "Authorization", "value": "Bearer {{token}}"}],
            "url": "{{baseUrl}}/just/table/_fetch.php?table=aa_user"
          }
        }
      ]
    }
  ]
}
```

---

### 4.2 Newman 命令行测试

```bash
# 运行 Postman 集合
newman run tests/api/vue-samba.postman_collection.json \
  --environment tests/api/test.env.json \
  --reporters cli,json \
  --reporter-json-export tests/results/api-test-results.json
```

---

## 5. E2E 测试

### 5.1 Playwright 配置

**文件**: `tests/e2e/playwright.config.js`

```javascript
module.exports = {
  testDir: './specs',
  timeout: 30000,
  use: {
    baseURL: 'https://localhost:5050',
    headless: true,
    screenshot: 'only-on-failure'
  }
};
```

---

### 5.2 登录流程测试

**文件**: `tests/e2e/specs/login.spec.js`

```javascript
const { test, expect } = require('@playwright/test');

test('用户登录流程', async ({ page }) => {
  // 访问登录页
  await page.goto('/');
  
  // 输入用户名和密码
  await page.fill('input[name="username"]', 'admin');
  await page.fill('input[name="password"]', 'admin123');
  
  // 点击登录
  await page.click('button[type="submit"]');
  
  // 等待跳转
  await page.waitForURL('/index');
  
  // 验证登录成功
  await expect(page.locator('.user-name')).toContainText('管理员');
});

test('2FA 登录流程', async ({ page }) => {
  await page.goto('/');
  
  await page.fill('input[name="username"]', 'admin');
  await page.fill('input[name="password"]', 'admin123');
  await page.click('button[type="submit"]');
  
  // 等待 2FA 输入框
  await page.waitForSelector('input[name="2fa_code"]');
  
  // 输入 2FA 码 (需要使用测试码)
  await page.fill('input[name="2fa_code"]', '123456');
  await page.click('button[type="submit"]');
  
  await page.waitForURL('/index');
  await expect(page.locator('.user-name')).toContainText('管理员');
});
```

---

## 6. 测试覆盖率

### 6.1 运行测试

```bash
# 后端测试
cd /var/www/vue_samba
./vendor/bin/phpunit --coverage-html tests/coverage/php

# 前端测试
cd /var/www/vue_samba/front
npm test -- --coverage

# 生成覆盖率报告
composer test-coverage
```

### 6.2 覆盖率目标

| 模块 | 目标覆盖率 |
|------|-----------|
| 认证模块 | 90% |
| 用户管理 | 85% |
| AD 同步 | 80% |
| Samba 管理 | 75% |
| 前端组件 | 70% |

---

## 7. CI/CD 集成

### 7.1 GitHub Actions

**文件**: `.github/workflows/test.yml`

```yaml
name: Tests

on: [push, pull_request]

jobs:
  php-tests:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:5.7
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: vue_samba_test
        ports:
          - 3306:3306
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '7.4'
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: ./vendor/bin/phpunit --coverage-clover=coverage.xml

  js-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup Node
        uses: actions/setup-node@v2
        with:
          node-version: '14'
      - name: Install dependencies
        run: npm install
      - name: Run tests
        run: npm test
```

---

## 8. 测试用例清单

### 8.1 必测用例

- [ ] 用户登录成功
- [ ] 用户登录失败 (密码错误)
- [ ] 用户登录失败 (用户不存在)
- [ ] 2FA 验证
- [ ] 创建用户
- [ ] 更新用户
- [ ] 删除用户
- [ ] 用户列表分页
- [ ] 用户搜索
- [ ] 角色分配
- [ ] 权限验证
- [ ] AD 连接测试
- [ ] AD 用户同步
- [ ] Samba 共享创建
- [ ] 文件上传
- [ ] 文件下载
- [ ] 数据导出
- [ ] 数据导入

---

*文档生成时间：2026-03-09*  
*测试框架版本：PHPUnit 9.x + Jest 27.x*

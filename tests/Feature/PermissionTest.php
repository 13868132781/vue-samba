<?php
/**
 * 权限管理测试
 * 
 * 测试 RBAC 权限控制功能
 */

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class PermissionTest extends TestCase
{
    private $baseUrl;
    private $adminToken;
    private $pdo;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->baseUrl = $GLOBALS['TEST_BASE_URL'];
        
        try {
            $this->adminToken = loginAndGetToken(
                $GLOBALS['TEST_ADMIN']['username'],
                $GLOBALS['TEST_ADMIN']['password']
            );
            $this->pdo = getTestDbConnection();
        } catch (\Exception $e) {
            $this->markTestSkipped('初始化失败：' . $e->getMessage());
        }
    }
    
    protected function tearDown(): void
    {
        if ($this->pdo) {
            cleanupTestData($this->pdo, ['aa_user', 'aa_role', 'aa_user_role']);
        }
        parent::tearDown();
    }
    
    /**
     * 测试获取权限列表
     */
    public function testGetPermissionList()
    {
        $response = httpAuthRequest(
            'GET',
            $this->baseUrl . '/just/table/_fetch.php?table=aa_perm&page=1&pagesize=10',
            $this->adminToken
        );
        
        $this->assertEquals(200, $response['code']);
        $this->assertArrayHasKey('list', $response['body']['data']);
    }
    
    /**
     * 测试获取角色列表
     */
    public function testGetRoleList()
    {
        $response = httpAuthRequest(
            'GET',
            $this->baseUrl . '/just/table/_fetch.php?table=aa_role&page=1&pagesize=10',
            $this->adminToken
        );
        
        $this->assertEquals(200, $response['code']);
        $this->assertArrayHasKey('list', $response['body']['data']);
    }
    
    /**
     * 测试创建角色
     */
    public function testCreateRole()
    {
        $roleData = [
            'name' => '测试角色_' . time(),
            'slug' => 'test_role_' . time(),
            'description' => '自动化测试创建的角色'
        ];
        
        $response = httpAuthRequest(
            'POST',
            $this->baseUrl . '/just/table/_crudAdd.php?table=aa_role',
            $this->adminToken,
            $roleData
        );
        
        $this->assertEquals(200, $response['code']);
        $this->assertArrayHasKey('id', $response['body']['data']);
        
        return $response['body']['data']['id'];
    }
    
    /**
     * 测试分配角色给用户
     * 
     * @depends testCreateRole
     */
    public function testAssignRoleToUser($roleId = null)
    {
        if (!$roleId) {
            $this->markTestSkipped('角色创建失败');
        }
        
        // 创建测试用户
        $userId = $this->createTestUser();
        
        $response = httpAuthRequest(
            'POST',
            $this->baseUrl . '/just/table/_crudAdd.php?table=aa_user_role',
            $this->adminToken,
            [
                'user_id' => $userId,
                'role_id' => $roleId
            ]
        );
        
        $this->assertEquals(200, $response['code']);
    }
    
    /**
     * 测试普通用户权限限制
     */
    public function testRegularUserPermissionLimit()
    {
        // 创建普通用户
        $userId = $this->createTestUser();
        $userToken = $this->loginAsUser($userId);
        
        // 尝试访问管理员接口
        $response = httpAuthRequest(
            'GET',
            $this->baseUrl . '/just/table/_fetch.php?table=aa_setting&page=1',
            $userToken
        );
        
        // 普通用户应该被拒绝访问系统设置
        $this->assertGreaterThanOrEqual(400, $response['code']);
    }
    
    /**
     * 测试管理员权限
     */
    public function testAdminPermission()
    {
        // 管理员应该能访问所有接口
        $response = httpAuthRequest(
            'GET',
            $this->baseUrl . '/just/table/_fetch.php?table=aa_user&page=1',
            $this->adminToken
        );
        
        $this->assertEquals(200, $response['code']);
    }
    
    /**
     * 测试未授权访问
     */
    public function testUnauthorizedAccess()
    {
        $response = httpRequest('GET', $this->baseUrl . '/just/table/_fetch.php?table=aa_user');
        
        $this->assertGreaterThanOrEqual(400, $response['code']);
    }
    
    /**
     * 辅助方法：创建测试用户
     */
    private function createTestUser()
    {
        $userData = [
            'username' => 'test_user_' . time(),
            'password' => 'password123',
            'name' => '测试用户'
        ];
        
        $response = httpAuthRequest(
            'POST',
            $this->baseUrl . '/just/table/_crudAdd.php?table=aa_user',
            $this->adminToken,
            $userData
        );
        
        return $response['body']['data']['id'] ?? null;
    }
    
    /**
     * 辅助方法：以用户身份登录
     */
    private function loginAsUser($userId)
    {
        // 获取用户信息
        $stmt = $this->pdo->prepare("SELECT username FROM aa_user WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            throw new \Exception('用户不存在');
        }
        
        // 使用默认密码登录
        return loginAndGetToken($user['username'], 'password123');
    }
}

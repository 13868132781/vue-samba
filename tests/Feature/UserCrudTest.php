<?php
/**
 * 用户 CRUD 测试
 * 
 * 测试用户的创建、读取、更新、删除功能
 */

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class UserCrudTest extends TestCase
{
    private $baseUrl;
    private $token;
    private $pdo;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->baseUrl = $GLOBALS['TEST_BASE_URL'];
        
        try {
            $this->token = loginAndGetToken(
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
        // 清理测试数据
        if ($this->pdo) {
            cleanupTestData($this->pdo, ['aa_user']);
        }
        parent::tearDown();
    }
    
    /**
     * 测试创建用户
     */
    public function testCreateUser()
    {
        $userData = [
            'username' => 'test_user_' . time(),
            'password' => 'password123',
            'name' => '测试用户' . time()
        ];
        
        $response = httpAuthRequest(
            'POST',
            $this->baseUrl . '/just/table/_crudAdd.php?table=aa_user',
            $this->token,
            $userData
        );
        
        $this->assertEquals(200, $response['code'], '创建用户失败');
        $this->assertArrayHasKey('data', $response['body']);
        $this->assertArrayHasKey('id', $response['body']['data'], '未返回用户 ID');
        
        // 验证用户已创建
        $userId = $response['body']['data']['id'];
        $stmt = $this->pdo->prepare("SELECT * FROM aa_user WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        $this->assertNotFalse($user, '用户未成功创建到数据库');
        $this->assertEquals($userData['username'], $user['username']);
        $this->assertEquals($userData['name'], $user['name']);
        
        return $userId;
    }
    
    /**
     * 测试获取用户列表
     */
    public function testGetUserList()
    {
        $response = httpAuthRequest(
            'GET',
            $this->baseUrl . '/just/table/_fetch.php?table=aa_user&page=1&pagesize=10',
            $this->token
        );
        
        $this->assertEquals(200, $response['code'], '获取用户列表失败');
        $this->assertArrayHasKey('data', $response['body']);
        $this->assertArrayHasKey('list', $response['body']['data'], '未返回列表数据');
        $this->assertArrayHasKey('total', $response['body']['data'], '未返回总数');
        $this->assertIsArray($response['body']['data']['list']);
    }
    
    /**
     * 测试获取单个用户
     * 
     * @depends testCreateUser
     */
    public function testGetSingleUser($userId = null)
    {
        if (!$userId) {
            // 创建测试用户
            $userId = $this->createTestUser();
        }
        
        $response = httpAuthRequest(
            'GET',
            $this->baseUrl . '/just/table/_edit.php?table=aa_user&id=' . $userId,
            $this->token
        );
        
        $this->assertEquals(200, $response['code'], '获取用户详情失败');
        $this->assertArrayHasKey('data', $response['body']);
        $this->assertEquals($userId, $response['body']['data']['id']);
    }
    
    /**
     * 测试更新用户
     * 
     * @depends testCreateUser
     */
    public function testUpdateUser($userId = null)
    {
        if (!$userId) {
            $userId = $this->createTestUser();
        }
        
        $newName = '更新后的名字_' . time();
        
        $response = httpAuthRequest(
            'POST',
            $this->baseUrl . '/just/table/_crudMod.php?table=aa_user',
            $this->token,
            [
                'id' => $userId,
                'name' => $newName
            ]
        );
        
        $this->assertEquals(200, $response['code'], '更新用户失败');
        
        // 验证更新
        $stmt = $this->pdo->prepare("SELECT * FROM aa_user WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        $this->assertEquals($newName, $user['name'], '用户名字未更新成功');
    }
    
    /**
     * 测试删除用户
     * 
     * @depends testCreateUser
     */
    public function testDeleteUser($userId = null)
    {
        if (!$userId) {
            $userId = $this->createTestUser();
        }
        
        $response = httpAuthRequest(
            'POST',
            $this->baseUrl . '/just/table/_crudDel.php?table=aa_user',
            $this->token,
            ['ids' => [$userId]]
        );
        
        $this->assertEquals(200, $response['code'], '删除用户失败');
        
        // 验证已删除
        $stmt = $this->pdo->prepare("SELECT * FROM aa_user WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        $this->assertFalse($user, '用户未被删除');
    }
    
    /**
     * 测试批量删除用户
     */
    public function testBatchDeleteUsers()
    {
        // 创建多个测试用户
        $userIds = [];
        for ($i = 0; $i < 3; $i++) {
            $userId = $this->createTestUser();
            $userIds[] = $userId;
        }
        
        $response = httpAuthRequest(
            'POST',
            $this->baseUrl . '/just/table/_crudDel.php?table=aa_user',
            $this->token,
            ['ids' => $userIds]
        );
        
        $this->assertEquals(200, $response['code'], '批量删除失败');
        
        // 验证所有用户都被删除
        foreach ($userIds as $userId) {
            $stmt = $this->pdo->prepare("SELECT * FROM aa_user WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            $this->assertFalse($user, "用户 {$userId} 未被删除");
        }
    }
    
    /**
     * 测试用户名唯一性
     */
    public function testUsernameUniqueness()
    {
        $username = 'test_duplicate_' . time();
        
        // 创建第一个用户
        $response1 = httpAuthRequest(
            'POST',
            $this->baseUrl . '/just/table/_crudAdd.php?table=aa_user',
            $this->token,
            [
                'username' => $username,
                'password' => 'password123',
                'name' => '用户 1'
            ]
        );
        
        $this->assertEquals(200, $response1['code']);
        
        // 尝试创建同名用户
        $response2 = httpAuthRequest(
            'POST',
            $this->baseUrl . '/just/table/_crudAdd.php?table=aa_user',
            $this->token,
            [
                'username' => $username,
                'password' => 'password123',
                'name' => '用户 2'
            ]
        );
        
        $this->assertGreaterThanOrEqual(400, $response2['code'], '重复用户名应该被拒绝');
    }
    
    /**
     * 辅助方法：创建测试用户
     */
    private function createTestUser()
    {
        $userData = [
            'username' => 'test_user_' . time() . '_' . rand(1000, 9999),
            'password' => 'password123',
            'name' => '测试用户'
        ];
        
        $response = httpAuthRequest(
            'POST',
            $this->baseUrl . '/just/table/_crudAdd.php?table=aa_user',
            $this->token,
            $userData
        );
        
        return $response['body']['data']['id'] ?? null;
    }
}

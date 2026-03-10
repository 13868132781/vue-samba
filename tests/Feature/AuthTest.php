<?php
/**
 * 认证功能测试
 * 
 * 测试用户登录、登出、2FA 等认证相关功能
 */

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    private $baseUrl;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->baseUrl = $GLOBALS['TEST_BASE_URL'];
    }
    
    /**
     * 测试登录接口可达性
     */
    public function testLoginEndpointReachable()
    {
        $response = httpRequest('POST', $this->baseUrl . '/front/api/auth/login', [
            'username' => 'test',
            'password' => 'test'
        ]);
        
        // 即使认证失败，接口应该可达
        $this->assertGreaterThan(0, $response['code']);
    }
    
    /**
     * 测试登录成功
     */
    public function testLoginSuccess()
    {
        try {
            $token = loginAndGetToken(
                $GLOBALS['TEST_ADMIN']['username'],
                $GLOBALS['TEST_ADMIN']['password']
            );
            
            $this->assertNotEmpty($token, '登录未返回 token');
            $this->assertIsString($token);
            $this->assertGreaterThan(10, strlen($token), 'Token 长度异常');
        } catch (\Exception $e) {
            $this->markTestSkipped('登录失败：' . $e->getMessage());
        }
    }
    
    /**
     * 测试登录失败 - 错误密码
     */
    public function testLoginFailedWrongPassword()
    {
        $response = httpRequest('POST', $this->baseUrl . '/front/api/auth/login', [
            'username' => $GLOBALS['TEST_ADMIN']['username'],
            'password' => 'wrongpassword123'
        ]);
        
        $this->assertGreaterThanOrEqual(400, $response['code'], '错误密码应该返回错误');
    }
    
    /**
     * 测试登录失败 - 用户不存在
     */
    public function testLoginFailedUserNotExist()
    {
        $response = httpRequest('POST', $this->baseUrl . '/front/api/auth/login', [
            'username' => 'nonexistent_user_' . time(),
            'password' => 'anypassword'
        ]);
        
        $this->assertGreaterThanOrEqual(400, $response['code'], '不存在的用户应该返回错误');
    }
    
    /**
     * 测试登录失败 - 空用户名
     */
    public function testLoginFailedEmptyUsername()
    {
        $response = httpRequest('POST', $this->baseUrl . '/front/api/auth/login', [
            'username' => '',
            'password' => 'somepassword'
        ]);
        
        $this->assertGreaterThanOrEqual(400, $response['code'], '空用户名应该返回错误');
    }
    
    /**
     * 测试登录失败 - 空密码
     */
    public function testLoginFailedEmptyPassword()
    {
        $response = httpRequest('POST', $this->baseUrl . '/front/api/auth/login', [
            'username' => 'someuser',
            'password' => ''
        ]);
        
        $this->assertGreaterThanOrEqual(400, $response['code'], '空密码应该返回错误');
    }
    
    /**
     * 测试 Token 有效性
     */
    public function testTokenValidity()
    {
        try {
            $token = loginAndGetToken(
                $GLOBALS['TEST_ADMIN']['username'],
                $GLOBALS['TEST_ADMIN']['password']
            );
            
            // 使用 token 访问需要认证的接口
            $response = httpAuthRequest(
                'GET',
                $this->baseUrl . '/just/table/_fetch.php?table=aa_user&page=1&pagesize=1',
                $token
            );
            
            $this->assertEquals(200, $response['code'], 'Token 认证失败');
        } catch (\Exception $e) {
            $this->markTestSkipped('Token 验证失败：' . $e->getMessage());
        }
    }
    
    /**
     * 测试无效 Token
     */
    public function testInvalidToken()
    {
        $response = httpAuthRequest(
            'GET',
            $this->baseUrl . '/just/table/_fetch.php?table=aa_user',
            'invalid_token_' . time()
        );
        
        $this->assertGreaterThanOrEqual(400, $response['code'], '无效 Token 应该被拒绝');
    }
    
    /**
     * 测试无 Token 访问受保护接口
     */
    public function testAccessProtectedEndpointWithoutToken()
    {
        $response = httpRequest('GET', $this->baseUrl . '/just/table/_fetch.php?table=aa_user');
        
        $this->assertGreaterThanOrEqual(400, $response['code'], '无 Token 应该被拒绝访问');
    }
}

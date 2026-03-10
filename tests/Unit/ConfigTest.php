<?php
/**
 * 配置测试
 * 
 * 测试系统配置加载和验证
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * 测试配置文件存在
     */
    public function testConfigFileExists()
    {
        $configFile = BASE_PATH . '/include/fun/config.php';
        $this->assertFileExists($configFile, '配置文件不存在');
    }
    
    /**
     * 测试数据库配置
     */
    public function testDatabaseConfig()
    {
        $configFile = BASE_PATH . '/include/fun/config.php';
        if (!file_exists($configFile)) {
            $this->markTestSkipped('配置文件不存在，跳过测试');
        }
        
        include $configFile;
        
        $this->assertNotEmpty($mysql_host ?? null, '数据库主机未配置');
        $this->assertNotEmpty($mysql_user ?? null, '数据库用户未配置');
        $this->assertNotEmpty($mysql_pass ?? null, '数据库密码未配置');
        $this->assertNotEmpty($mysql_db ?? null, '数据库名称未配置');
    }
    
    /**
     * 测试测试数据库配置
     */
    public function testTestDatabaseConfig()
    {
        $this->assertNotEmpty($GLOBALS['TEST_DB_CONFIG']['host'] ?? null);
        $this->assertNotEmpty($GLOBALS['TEST_DB_CONFIG']['database'] ?? null);
        $this->assertEquals('vue_samba_test', $GLOBALS['TEST_DB_CONFIG']['database']);
    }
    
    /**
     * 测试基础路径配置
     */
    public function testBasePathConfig()
    {
        $this->assertNotEmpty(BASE_PATH);
        $this->assertDirectoryExists(BASE_PATH);
        $this->assertDirectoryExists(BASE_PATH . '/app');
        $this->assertDirectoryExists(BASE_PATH . '/front');
        $this->assertDirectoryExists(BASE_PATH . '/include');
    }
    
    /**
     * 测试测试管理员配置
     */
    public function testTestAdminConfig()
    {
        $this->assertNotEmpty($GLOBALS['TEST_ADMIN']['username'] ?? null);
        $this->assertNotEmpty($GLOBALS['TEST_ADMIN']['password'] ?? null);
    }
}

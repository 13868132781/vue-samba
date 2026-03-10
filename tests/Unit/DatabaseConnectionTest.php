<?php
/**
 * 数据库连接测试
 * 
 * 测试数据库连接功能
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOException;

class DatabaseConnectionTest extends TestCase
{
    /**
     * 测试测试数据库连接
     */
    public function testTestDatabaseConnection()
    {
        try {
            $pdo = getTestDbConnection();
            $this->assertInstanceOf(PDO::class, $pdo, '数据库连接失败');
        } catch (PDOException $e) {
            $this->markTestSkipped('测试数据库不可用：' . $e->getMessage());
        }
    }
    
    /**
     * 测试生产数据库连接
     */
    public function testProductionDatabaseConnection()
    {
        // 这个测试需要实际的生产环境
        $this->markTestSkipped('需要生产环境，跳过测试');
    }
    
    /**
     * 测试数据库表存在
     * 
     * @depends testTestDatabaseConnection
     */
    public function testDatabaseTablesExist()
    {
        try {
            $pdo = getTestDbConnection();
            
            $expectedTables = [
                'aa_user',
                'aa_role',
                'aa_user_role',
                'aa_perm',
                'aa_role_perm',
                'aa_menu',
                'aa_setting'
            ];
            
            $result = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($expectedTables as $table) {
                $this->assertContains($table, $result, "表 {$table} 不存在");
            }
        } catch (PDOException $e) {
            $this->markTestSkipped('无法检查表结构：' . $e->getMessage());
        }
    }
}

#!/bin/bash

# Vue Samba 测试运行脚本

set -e

echo "========================================"
echo "  Vue Samba 测试套件"
echo "========================================"
echo ""

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 检查 PHP 版本
echo "检查 PHP 版本..."
php_version=$(php -v | head -n 1)
echo -e "${GREEN}✓${NC} $php_version"

# 检查 Composer 依赖
if [ ! -d "vendor" ]; then
    echo ""
    echo -e "${YELLOW}⚠${NC} 未找到 vendor 目录，正在安装依赖..."
    composer install --no-interaction
fi

# 检查测试数据库
echo ""
echo "检查测试数据库配置..."
if [ -z "$TEST_DB_PASSWORD" ]; then
    echo -e "${YELLOW}⚠${NC} 未设置 TEST_DB_PASSWORD 环境变量，使用默认值"
fi

# 创建测试数据库（如果不存在）
echo "创建测试数据库（如果不存在）..."
mysql -h 127.0.0.1 -u root -p"${TEST_DB_PASSWORD:-root}" -e "CREATE DATABASE IF NOT EXISTS vue_samba_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || {
    echo -e "${RED}✗${NC} 无法连接数据库，请确保数据库服务运行正常"
    exit 1
}
echo -e "${GREEN}✓${NC} 测试数据库就绪"

# 运行测试
echo ""
echo "========================================"
echo "  运行测试"
echo "========================================"
echo ""

if [ "$1" == "coverage" ]; then
    echo "生成覆盖率报告..."
    ./vendor/bin/phpunit --coverage-html tests/coverage/html
    echo ""
    echo -e "${GREEN}✓${NC} 覆盖率报告已生成：tests/coverage/html/index.html"
elif [ "$1" == "unit" ]; then
    echo "运行单元测试..."
    ./vendor/bin/phpunit --testsuite Unit
elif [ "$1" == "feature" ]; then
    echo "运行功能测试..."
    ./vendor/bin/phpunit --testsuite Feature
elif [ "$1" == "integration" ]; then
    echo "运行集成测试..."
    ./vendor/bin/phpunit --testsuite Integration
else
    echo "运行所有测试..."
    ./vendor/bin/phpunit
fi

echo ""
echo "========================================"
echo -e "${GREEN}✓${NC} 测试完成"
echo "========================================"

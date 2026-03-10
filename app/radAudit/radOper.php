<?php
namespace app\radAudit;

class radOper extends \table {
    public $pageName = '认证审计';
    public $TN = "vpd.oa_auth";
    public $colKey = "id";
    public $colOrder = "time0";
    public $colFid = "";
    public $colName = "auth";
    public $orderDesc = true;
    public $POST = [];

    public function gridBefore($db) {
        // 如果有 leftJoin 或其他预处理逻辑，可保留或删除
        return $db;
    }

    public function gridSet() {
        $gridSet = [
            'columns' => [
                ['col' => 'time0', 'name' => '时间'],
                ['col' => 'user_ip', 'name' => '客户端IP'],
                ['col' => 'user', 'name' => '用户名'],
                ['col' => 'auth', 'name' => '结果'],
                ['col' => 'c_info', 'name' => '认证信息'],
                ['col' => 'session_num', 'name' => '会话编号'],
                ['col' => 'geo', 'name' => '地理位置'],
                ['col' => 'or_id', 'name' => '组ID'],
            ],
            'toolEnable' => true,
            'toolAddEnable' => false,
            'toolExportEnable' => true,
            'toolRefreshEnable' => true,
            'toolDeleteEnable' => false,
            'toolFilterEnable' => true,
            'toolSearchColumn' => [
                'user' => 'like',
                'user_ip' => 'like',
                'auth' => 'like',
                'c_info' => 'like',
            ],
            'operEnable' => false,
            'operModEnable' => false,
            'operDelEnable' => false,
            'fenyeEnable' => true,
            'fenyeNum' => 20,
        ];

        return $gridSet;
    }

    public function filterSet() {
        $back = [
            [
                "name" => "时间",
                "col" => "time0",
                "type" => 'datePick',
                "dateType" => 2,
            ],
            [
                "name" => "客户端IP",
                "col" => "user_ip",
                "type" => 'text',
            ],
            [
                "name" => "用户名",
                "col" => "user",
                "type" => 'text',
            ],
            [
                "name" => "认证结果",
                "col" => "auth",
                "type" => 'select',
                'options' => [
                    'success' => '认证成功',
                    'fail' => '认证失败',
                ],
            ],
            [
                "name" => "组ID",
                "col" => "or_id",
                "type" => 'number',
            ],
        ];
        return $back;
    }
}
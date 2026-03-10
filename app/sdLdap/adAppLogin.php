<?php
namespace app\sdLdap;
class adAppLogin extends \table {
    public $pageName = "认证审计";
    public $TN = "vpd.oa_auth";
    public $colKey = "id";
    public $colOrder = "time0";
    public $colFid = "";
    public $colName = "auth";
    public $orderDesc = true;
    public $POST = [];

    public function gridSet() {
        $gridSet = [
            'columns' => [
                ['col' => 'time0', 'name' => '时间'],
                ['col' => 'user_ip', 'name' => '客户端IP'],
                ['col' => 'user', 'name' => '用户'],
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
            'operEnable' => false,
            'operModEnable' => false,
            'operDelEnable' => false,
            'fenyeEnable' => true,
            'fenyeNum' => 20,
            'toolSearchColumn' => [
                'user' => 'like',
                'user_ip' => 'like',
                'auth' => 'like',
                'c_info' => 'like',
            ],
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
                "name" => "用户",
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
	public function gridTotal() {
    // 构造查询总数的 SQL
    $sql = "SELECT COUNT(*) FROM `" . $this->TN . "`";
    $where = $this->buildWhere(); // 如果有搜索条件
    if ($where) $sql .= " WHERE " . $where;

    // 执行查询并返回整数结果
    return (int)$this->db()->getOne($sql);
}

public function gridData($fenye = []) {
    $now = isset($fenye['now']) ? (int)$fenye['now'] : 1;
    $num = isset($fenye['num']) ? (int)$fenye['num'] : 20;
    $offset = ($now - 1) * $num;

    $sql = "SELECT * FROM `" . $this->TN . "`";
    $where = $this->buildWhere();
    if ($where) $sql .= " WHERE " . $where;
    $sql .= " ORDER BY `" . $this->colOrder . "` " . ($this->orderDesc ? 'DESC' : 'ASC');
    $sql .= " LIMIT $offset, $num";

    // 执行查询并返回数组结果
    return $this->db()->getAll($sql);
}

}
<?php

namespace appsys\entry;

class logEntry extends \table {
	public $pageName="框架entry管理";
	public $TN = "{sysDB}.zlog_entry";
	public $colKey = "deid";
	public $colOrder = "";
	public $colFid = "";
	public $colName = "deid";
	public $orderDesc = true;
	public $POST = [];

	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'de_time','name'=>'时间'],
				['col'=>'de_router','name'=>'路由'],
				['col'=>'de_post','name'=>'参数',
					'width'=>'300px',
					'ellipsis'=>true,
					'showInDlg'=>true,
				],
				['col'=>'de_take','name'=>'耗时(秒)'],
				['col'=>'mystep','name'=>'过程',
					'type'=>'fetch',
					'goto'=>'showStep'
				],
				['col'=>'mystep','name'=>'sqls',
					'type'=>'fetch',
					'goto'=>'showSqls'
				],
			],
			
			'toolEnable' => true,
			'toolAddEnable' => false,
			'toolExportEnable' => false,
			'toolRefreshEnable'=> true,
			'toolDeleteEnable'=>true,
			'toolFilterEnable'=>true,
			'operEnable' => false,
			'operModEnable' => false,
			'operDelEnable'=> true,
			'fenyeEnable'=> true,
			
			'toolSearchColumn'=>[
				'de_router'=>'like',
				'de_post'=>'like',
			],	
			
			'toolExpands'=>[ //form page execute batch html link list
				[
					'name'=>'日志解析',
					'type'=>'execute',
					'router'=>'sys/sysCron/sysCron',
					'goto'=>'doExec',
					'post'=>['key'=>'11'],
				],
			],
			
		];
		
		return $gridSet;
	}

	
	public function filterSet(){
		$back=[
			[
				"name"=>"时间",
				"col"=>"de_time",
				"type"=>'datePick',
				"dateType"=>2,
			],
			[
				"name"=>"客户端",
				"col"=>"al_client",
				"type"=>'text',
			],
			[
				"name"=>"用户",
				"col"=>"al_user",
				"type"=>'text',
			],
			[
				"name"=>"结果",
				"col"=>"al_status",
				"type"=>'select',
				'options'=>[
					'NT_STATUS_OK'=>'认证成功',
					'NT_STATUS_WRONG_PASSWORD'=>'密码错误',
				],
			]
		];
		return $back;
	}
	
	public function fetch_showStep(){
		$this->noAudit=true;
		$row = $this->currentRow;
		
		$data = json_decode($row['de_say'],true);
		
		array_unshift($data,[
			'_is_gridSet_'=>true,
			'columns'=>[
				['col'=>'0','name'=>'离上一步时间','type'=>'text','inStyle'=>'word-break:keep-all;','tdStyle'=>'width:150px'],
				['col'=>'1','name'=>'日志','type'=>'text','inStyle'=>'word-wrap: break-word;word-break:break-all;'],
			]
		]);
		
		return $this->out(0,$data);
	}
	
	public function fetch_showSqls(){
		$this->noAudit=true;
		$row = $this->currentRow;
		
		$data = json_decode($row['de_sql'],true);
		
		array_unshift($data,[
			'_is_gridSet_'=>true,
			'columns'=>[
				['col'=>'Query_ID','type'=>'text','name'=>'',
				'inStyle'=>'word-break:keep-all;','tdStyle'=>'width:50px'],
				
				['col'=>'Duration','type'=>'text','name'=>'耗时',
				'inStyle'=>'word-break:keep-all;','tdStyle'=>'width:150px'],
				
				['col'=>'Query','type'=>'text','name'=>'语句',
				'inStyle'=>'word-wrap: break-word;word-break:break-all;'],
				
				['col'=>'detail','type'=>'fetch','name'=>'详情',
				'tdStyle'=>'width:50px','showColVal'=>true,'popWidth'=>'95%','popHeight'=>'90%']
			]
		]);
		
		return $this->out(0,$data);
	}
	
}
?>
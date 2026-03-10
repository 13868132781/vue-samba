<?php

namespace app\sdLdap;

class adAuditLogin extends \table {
	public $pageName="域登录审计";
	public $TN = "sdsamba_log.adlogin";
	public $colKey = "alid";
	public $colOrder = "";
	public $colFid = "";
	public $colName = "alid";
	public $orderDesc = true;
	public $POST = [];

	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'al_time','name'=>'时间'],
				['col'=>'al_client','name'=>'客户端'],
				['col'=>'al_user','name'=>'用户'],
				['col'=>'al_type','name'=>'类型'],
				['col'=>'al_service','name'=>'认证应用'],
				['col'=>'al_authtype','name'=>'认证类型'],
				['col'=>'al_status','name'=>'结果',
					'valMap'=>[
						'NT_STATUS_OK' =>'认证成功',
						'NT_STATUS_WRONG_PASSWORD'=>'密码错误',
					],
					'dotMap'=>[
						'认证成功'=>'#385E0F',
						'密码错误'=>'#FF9912',
					],
					'align'=>'center',
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
			'fenyeNum'=>20,
			
			'toolSearchColumn'=>[
				'al_client'=>'like',	
				'al_user'=>'like',
				'al_service'=>'like',
				'al_authtype'=>'like',
			],
			
			
			'toolExpands'=>[ //form page execute batch html link list
				[
					'name'=>'日志解析',
					'type'=>'execute',
					'router'=>'sys/sysCron/sysCron',
					'goto'=>'doExec',
					'post'=>['key'=>'7'],
				],
			],
			
		];
		
		
		
		return $gridSet;
	}

	
	public function filterSet(){
		$back=[
			[
				"name"=>"时间",
				"col"=>"al_time",
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
}
?>
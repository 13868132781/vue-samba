<?php

namespace app\sdLdap;

class adAuditLoginOTP extends \table {
	public $pageName="域登录审计";
	public $TN = "sdsamba_log.adloginotp";
	public $colKey = "aoid";
	public $colOrder = "";
	public $colFid = "";
	public $colName = "aoid";
	public $orderDesc = true;
	public $POST = [];

	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'ao_time','name'=>'时间'],
				['col'=>'ao_ip','name'=>'客户端'],
				['col'=>'ao_user','name'=>'用户'],
				['col'=>'ao_code','name'=>'结果',
					'valMap'=>[
						'0' =>'验证成功',
						'_default_'=>'验证错误',
					],
					'dotMap'=>[
						'验证成功'=>'#385E0F',
						'验证错误'=>'#FF9912',
					],
					'align'=>'center',
				],
				['col'=>'ao_result','name'=>'原因'],
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
				'ao_ip'=>'like',	
				'ao_user'=>'like',
			],
			
		];
		
		
		
		return $gridSet;
	}

	
	public function filterSet(){
		$back=[
			[
				"name"=>"时间",
				"col"=>"ao_time",
				"type"=>'datePick',
				"dateType"=>2,
			],
			[
				"name"=>"客户端",
				"col"=>"ao_ip",
				"type"=>'text',
			],
			[
				"name"=>"用户",
				"col"=>"ao_user",
				"type"=>'text',
			],
			[
				"name"=>"结果",
				"col"=>"ao_code",
				"type"=>'select',
				'options'=>[
					'0'=>'认证成功',
					'21'=>'用户不存在',
					'99'=>'口令错误',
				],
			]
		];
		return $back;
	}	
}
?>
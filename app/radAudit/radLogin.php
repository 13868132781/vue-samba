<?php
namespace app\radAudit;

class radLogin extends \table{
	public $pageName='登录审计';
	public $TN = "sdaaa_log.rad_login";
	public $colKey = "loginid";
	public $colOrder = "";
	public $colFid = "";
	public $colName = "";
	public $orderDesc = true;
	public $POST = [];
	
	public function gridBefore($db){
		$db->field('date','callingip','nasip','username','pass','reply','Reply_Message')
		->leftJoin("sdaaa.nas","nasip","na_ip",null,['na_name']);
		
		return $db;
	}
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'date','name'=>'时间'],
				['col'=>'callingip','name'=>'客户端'],
				['col'=>'na_name','name'=>'设备名'],
				['col'=>'nasip','name'=>'设备IP'],
				['col'=>'username','name'=>'用户名'],
				['col'=>'pass','name'=>'密码',
					'modify'=>function($val,$row){
						if($row['reply']=='Access-Accept')
							return '******';
						else
							return $val;
					}
				],
				['col'=>'reply','name'=>'结果',
					'valMap'=>[
						'Access-Accept'=>'成功',
						'_default_'=>'失败',
					],
					'dotMap'=>[
						'成功'=>'#385E0F',
						'失败'=>'#FF9912',
					],
					'align'=>'center',
				],
				['col'=>'Reply_Message','name'=>'回复',
					'width'=>'100px',
					'ellipsis'=>true,
				]
				
			],
			'toolEnable' => true,
			'toolAddEnable' => false,
			'toolExportEnable' => true,
			'toolRefreshEnable'=> true,
			'toolDeleteEnable'=>true,
			'toolFilterEnable'=>true,
			'toolSearchColumn'=>[
				'callingip'=>'like',//likeStart
				'nasip'=>'like',
				'na_name'=>'like',
				'username'=>'like',	
			],
				
			'operEnable' => false ,
			'operModEnable'=> true,
			'operDelEnable'=> true,
				
				
			'fenyeEnable'=> true,
			'fenyeNum'=> 20,//默认20 
			
			
		];
		return $gridSet;
	}
		
	public function filterSet(){
		$back=[
			[
				"name"=>"时间",
				"col"=>"date",
				"type"=>'text',
				"hintMore"=>'如：2023-03-20~2023-03-28',
			],
			[
				"name"=>"设备IP",
				"col"=>"nasip",
				"type"=>'text',
			],
			[
				"name"=>"设备名",
				"col"=>"na_name",
				"type"=>'text',
			],
			[
				"name"=>"客户端",
				"col"=>"callingip",
				"type"=>'text',
			],
			[
				"name"=>"用户名",
				"col"=>"username",
				"type"=>'text',
			],
			[
				"name"=>"结果",
				"col"=>"reply",
				"type"=>'select',
				'options'=>[
					'Access-Accept'=>'成功',
					'Access-Reject'=>'失败',
				],
			]
		];
		return $back;
	}
		
}


?>
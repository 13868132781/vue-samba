<?php

namespace app\radPerm;

class permLimit extends \table{
	public $pageName='限制组';
	public $TN = "sdaaa.permlimit";
	public $colKey = "glid";
	public $colOrder = "";
	public $colFid = "";
	public $colName = "gl_name";
	public $orderDesc = false;
	public $POST = [];
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'gl_name','name'=>'名称'],
				['col'=>'gl_fail_cs','name'=>'失败次数'],
				['col'=>'gl_fail_cl','name'=>'失败处理'],
				['col'=>'gl_login','name'=>'登陆次数'],
				['col'=>'gl_gq_user','name'=>'账户过期'],
				['col'=>'gl_gq_pass','name'=>'密码过期'],
				['col'=>'gl_passnum','name'=>'历史密码数'],
				['col'=>'gl_passcxty','name'=>'密码复杂度'],				
				['col'=>'gl_order','name'=>'排序',
					'type'=>'order',
					'align'=>'center',
					'width'=>'50px',
				],
						
			],
			
		];
		return $gridSet;
	}
	
	
	public function crudAddSet(){//获取编辑字段信息
		$back=[];
		$back[]=[
			"name"=>"名称",
			"col"=>"gl_name",
			"type"=>'text',
			"ask"=>true, 
			'valid'=>[
				'type'=>'text',
				'max'=>20,
				'min'=>2
			]
		];
		
		$back[]=[
			"name"=>"失败次数",
			"col"=>"gl_fail_cs",
			"hintMore"=>'超出失败次数，账户将被锁定，0则不限制',
			"type"=>'text',
			"ask"=>true,
			"value"=>'0',
			'valid'=>[
				'type'=>'number'
			],
		];
		
		$back[]=[
			"name"=>"失败处理",
			"col"=>"gl_fail_cl",
			"hintMore"=>'锁定账户多少分钟解锁，0则一直锁定',
			"type"=>'text',
			"ask"=>true,
			"value"=>'0',
			'valid'=>[
				'type'=>'number'
			],
		];
		
		$back[]=[
			"name"=>"登录次数",
			"col"=>"gl_login",
			"hintMore"=>'允许的登录次数，超出则登陆失败，0则不限制',
			"type"=>'text',
			"ask"=>true,
			"value"=>'0',
			'valid'=>[
				'type'=>'number'
			],
		];
		
		$back[]=[
			"name"=>"账户过期",
			"col"=>"gl_gq_user",
			"hintMore"=>'多少天后账户过期，0则不限制',
			"type"=>'text',
			"ask"=>true,
			"value"=>'0',
			'valid'=>[
				'type'=>'number'
			],
		];
		$back[]=[
			"name"=>"密码过期",
			"col"=>"gl_gq_pass",
			"hintMore"=>'多少天后密码过期，0则不限制',
			"type"=>'text',
			"ask"=>true,
			"value"=>'0',
			'valid'=>[
				'type'=>'number'
			],
		];
		$back[]=[
			"name"=>"历史密码数",
			"col"=>"gl_passnum",
			"hintMore"=>'新密码必须与之前n个历史密码不同，0则不限制',
			"type"=>'text',
			"ask"=>true,
			"value"=>'0',
			'valid'=>[
				'type'=>'number'
			],
		];
		$back[]=[
			"name"=>"密码复杂度",
			"col"=>"gl_passcxty",
			//"hintMore"=>'新密码必须与之前n个历史密码不同，0则不限制',
			"type"=>'cxty',
			//"ask"=>true,
		];
	

		return $back;
	}
	
	public function crudModSet(){
		$back= $this->crudAddSet();
		return $back;
	}
	
}


?>
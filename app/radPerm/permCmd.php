<?php
namespace app\radPerm;

class permCmd extends \table{
	public $pageName='命令组';
	public $TN = "sdaaa.permcmd";
	public $colKey = "gcid";
	public $colOrder = "gc_order";
	public $colFid = "";
	public $colName = "gc_name";
	public $orderDesc = false;
	public $POST = [];
	public $onoffReg=[
		'check'=>'停用/启用'
	];
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'gc_name','name'=>'名称'],
				['col'=>'gc_mark','name'=>'说明'],
				['col'=>'gc_dflt','name'=>'默认',
					'valMap'=>[0=>'拒绝',1=>'允许'],
				],
				/*
				['col'=>'gc_enable','name'=>'启停',
					'jsCtrl'=>[
						'type'=>'onoff',
						'goto'=>'check',
						'align'=>'center',
						'width'=>'50px',
					]
				],	*/
				['col'=>'gc_order','name'=>'排序',
					'type'=>'order',
					'align'=>'center',
					'width'=>'50px',
					
				],
				['col'=>'gc_cmds','name'=>'命令',
					'type'=>'dialog',
					'popTitle'=>'命令管理',
					'router'=>'/radPerm/permCmdList',
					'popWidth'=>'80%',
					'popHeight'=>'80%',
					'align'=>'center'
				],
			],
		];
		return $gridSet;
	}
	
	
	public function crudAddSet(){//获取编辑字段信息
		$back=[];
		$back[]=[
			"name"=>"名称",
			"col"=>"gc_name",
			"type"=>'text',
			"ask"=>true, 
			'valid'=>[
				'type'=>'text',
				'max'=>20,
				'min'=>2
			]
		];
		$back[]=[
			"name"=>"默认动作",
			"col"=>"gc_dflt",
			"type"=>'select',
			"ask"=>true,
			"value"=>'0',
			'options'=>[
				'0'=>'拒绝',
				'1'=>'允许',
			]
		];
		$back[]=[
			"name"=>"说明",
			"col"=>"gc_mark",
			"type"=>'text',
		];
		
		return $back;
	}
	
	public function crudModSet(){
		$back= $this->crudAddSet();
		return $back;
	}
	
	
}
?>
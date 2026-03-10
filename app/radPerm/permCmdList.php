<?php
namespace app\radPerm;

class permCmdList extends \table{
	public $pageName='命令组';
	public $TN = "sdaaa.permcmd_list";
	public $colKey = "gclid";
	public $colOrder = "gcl_order";
	public $colFid = "";
	public $colName = "gcl_cmd";
	public $orderDesc = false;
	public $colKeyList = ["gcl_gcid"];
	public $POST = [];
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'gcl_cmd','name'=>'命令'],
				['col'=>'gcl_perm','name'=>'动作',
					'valMap'=>[0=>'拒绝',1=>'允许'],
				],
				['col'=>'gcl_mark','name'=>'说明'],	
				['col'=>'gcl_sms','name'=>'报警',
					'valMap'=>[
						'0'=>'不发',
						'1'=>'发送'
					],
				],
				
				['col'=>'gcl_order','name'=>'排序',
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
			"name"=>"命令",
			"col"=>"gcl_cmd",
			"type"=>'text',
			"ask"=>true, 
		];
		$back[]=[
			"name"=>"动作",
			"col"=>"gcl_perm",
			"type"=>'select',
			'ask'=>true,
			'value'=>'0',
			'options'=>[
				'0'=>'拒绝',
				'1'=>'允许',
			]
		];
		$back[]=[
			"name"=>"是否发送报警",
			"col"=>"gcl_sms",
			"type"=>'select',
			'ask'=>true,
			'value'=>'0',
			'options'=>[
				'0'=>'不发报警',
				'1'=>'发送报警',
			]
		];
		$back[]=[
			"name"=>"说明",
			"col"=>"gcl_mark",
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
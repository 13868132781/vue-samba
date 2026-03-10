<?php
namespace app\radPerm;

class perm extends \table{
	public $pageName='角色组';
	public $TN = "sdaaa.perm";
	public $colKey = "gpid";
	public $colOrder = "gp_order";
	public $colFid = "";
	public $colName = "gp_name";
	public $orderDesc = false;
	public $POST = [];
	public $radioReg=[
		'enable'=>'修改默认项'
	];
	public $onoffReg=[
		'check'=>'取消全选/全部选定'
	];
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'gpid','name'=>'编号','jsCtrl'=>['width'=>'30px']],
				['col'=>'gp_name','name'=>'名称'],
				['col'=>'gp_mark','name'=>'说明'],
				['col'=>'gp_default','name'=>'默认',
					'type'=>'radio',
					'goto'=>'enable',
					'align'=>'center',
					'width'=>'50px',
				],
				['col'=>'gp_check','name'=>'设备',
					'valMap'=>[
						'0'=>'拒绝',
						'1'=>'允许',
					],
					'modify'=>function($val,&$row){
						if($val=='允许'){
							return [
								'value'=>$val,
								'color'=>'#00f'
							];
						}
						return $val;
					}
				],
				['col'=>'gp_attr','name'=>'属性组',
				'valMap'=>permAttr::options(['0'=>'--'])],
				['col'=>'gp_cmd','name'=>'命令组',
				'valMap'=>permCmd::options(['0'=>'--'])],
				['col'=>'gp_shid','name'=>'时段组',
				'valMap'=>permShid::options(['0'=>'--'])],
				['col'=>'gp_limit','name'=>'限制组',
				'valMap'=>permLimit::options(['0'=>'--'])],
				
				['col'=>'gp_organ','name'=>'机构',
					'type'=>'dialog',
					'overShow'=>'机构单独设置',
					'router'=>'/radPerm/perm_organ',
					'popWidth'=>'80%',
					'popHeight'=>'80%',
					'align'=>'center'
				],
				
				
				['col'=>'gp_order','name'=>'排序',
					'type'=>'order',
					'align'=>'center',
					'width'=>'50px',
				],
			],
			'toolEnable' => true,
			'toolAddEnable' => true,
			'toolExportEnable' => true,
			'toolRefreshEnable'=> true,
				
			'operEnable' => true ,
			'operModEnable'=> true,
			'operDelEnable'=> true,
				
				
			'fenyeEnable'=> true,
			'fenyeNum'=> 20,//默认20 
		];
		return $gridSet;
	}
	
	public function crudAddSet(){
		$back=[];
		$back[]=[
			"name"=>"名称",
			"col"=>"gp_name",
			"type"=>'text',
			'value'=>'',
			"ask"=>true,
		];
		$back[]=[
			"name"=>"说明",
			"col"=>"gp_mark",
			"type"=>'text',
			'value'=>'',
		];
		$back[]=[
			"name"=>"所有设备",
			"col"=>"gp_check",
			"type"=>'radio',
			"options"=>[
				'0'=>'拒绝','1'=>'允许'
			],
			'value'=>'0',
			"ask"=>true,
			'hintMore'=>'是否允许在所有设备上登录',
		];
		$back[]=[
			"name"=>"属性组",
			"col"=>"gp_attr",
			"type"=>'select',
			"options"=>permAttr::options(['0'=>'不使用']),
			'value'=>'0',
			"ask"=>true,
		];
		$back[]=[
			"name"=>"命令组",
			"col"=>"gp_cmd",
			"type"=>'select',
			"options"=>permCmd::options(['0'=>'不使用']) ,
			'value'=>'0',
			"ask"=>true,
		];
		$back[]=[
			"name"=>"时段组",
			"col"=>"gp_shid",
			"type"=>'select',
			"options"=>permShid::options(['0'=>'不使用']) ,
			'value'=>'0',
			"ask"=>true,
		];
		$back[]=[
			"name"=>"限制组",
			"col"=>"gp_limit",
			"type"=>'select',
			"options"=>permLimit::options(['0'=>'不使用']),	
			'value'=>'0',
			"ask"=>true,			
		];
		return $back;
		
	}
	
	public function crudModSet(){
		$back= $this->crudAddSet();
		return $back;
	}
	
}
?>
<?php
namespace app\radNas;
use app\perm\perm;

class nasType extends \table{
	public $pageName='设备类型';
	public $TN = "sdaaa.nas_type";
	public $colKey = "ntid";
	public $colOrder = "nt_order";
	public $colFid = "";
	public $colUnit = "";
	public $colName = "nt_name";
	public $orderDesc = false;
	public $POST = [];
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'nt_name','name'=>'名称'],
				['col'=>'nt_mark','name'=>'说明'],
				['col'=>'nt_order','name'=>'排序',
					'type'=>'order',
					'align'=>'center',
					'width'=>'50px',
				],
				['col'=>'gp_organ','name'=>'属性',
					'type'=>'dialog',
					'popTitle'=>'设备类型属性',
					'router'=>'/radNas/nasTypeAttr',
					'popWidth'=>'80%',
					'popHeight'=>'80%',
					'align'=>'center'
				],
				
			],
			'toolEnable' => true,
			'toolAddEnable' => true,
			'toolExportEnable' => false,
			'toolRefreshEnable'=> true,
			'toolFilterEnable'=>false,
			'toolExpands'=>[ 
				[
					'type'=>'dialog',
					'name'=>'公共属性',
					'router'=>'/radNas/nasTypeAttr',
					'post'=>[
						'keyList'=>[0]
					],
					'popWidth'=>'80%',
					'popHeight'=>'80%',
					'align'=>'center'
				],
			],
			
			'operEnable' => true ,
			'operModEnable'=> true,
			'operDelEnable'=> true,
				
				
			'fenyeEnable'=> false,
			'fenyeNum'=> 20,//默认20 
		];
		return $gridSet;
	}
		
	public function crudAddSet(){
		$back=[];
		$back[]=[
			"name"=>"名称",
			"col"=>"nt_name",
			"type"=>'text',
			'value'=>'',
			"ask"=>true,
		];
		$back[]=[
			"name"=>"说明",
			"col"=>"nt_mark",
			"type"=>'text',
			'value'=>'',
		];
		return $back;
	}
	
	public function crudModSet(){
		return $this->crudAddSet();
	}
	
}

?>
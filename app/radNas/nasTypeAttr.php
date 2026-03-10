<?php
namespace app\radNas;
use app\perm\perm;

class nasTypeAttr extends \table{
	public $pageName='设备类型属性';
	public $TN = "sdaaa.nas_typeattr";
	public $colKey = "ntaid";
	public $colOrder = "";
	public $colFid = "";
	public $colUnit = "";
	public $colName = "nta_attr";
	public $colKeyList = ["nta_ntid"];
	public $orderDesc = false;
	public $POST = [];
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'nta_pro','name'=>'协议',
					'valMap'=>[
						0=>'radius',
						1=>'tatacs',
					]
				],
				['col'=>'nta_attr','name'=>'名称'],
				['col'=>'nta_val','name'=>'值'],
				['col'=>'nta_order','name'=>'排序',
					'type'=>'order',
					'align'=>'center',
					'width'=>'50px',
				],
				
			],
			'toolEnable' => true,
			'toolAddEnable' => true,
			'toolExportEnable' => false,
			'toolRefreshEnable'=> true,
			'toolFilterEnable'=>false,
			
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
			"name"=>"类型",
			"col"=>"nta_pro",
			"type"=>'select',
			"options"=>[
				0=>'radius',
				1=>'tacacs',
			],
			'value'=>'0',
			"ask"=>true,
		];
		$back[]=[
			"name"=>"属性名",
			"col"=>"nta_attr",
			"type"=>'text',
			'value'=>'',
			"ask"=>true,
		];
		$back[]=[
			"name"=>"属性值列表",
			"col"=>"nta_val",
			"hintMore"=>'多个值以两个分号;;隔开',
			"type"=>'text',
			'value'=>'',
			"ask"=>true,
		];
		return $back;
	}
	
	public function crudModSet(){
		return $this->crudAddSet();
	}
	
}

?>
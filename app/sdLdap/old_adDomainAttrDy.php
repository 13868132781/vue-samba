<?php

namespace app\sdLdap;

class adDomainAttrDy extends \table {
	public $pageName="系统网络";
	public $TN = "sdsamba.adattrdomain";
	public $colKey = "aadid";
	public $colOrder = "aad_order";
	public $colFid = "";
	public $colName = "aad_name";
	public $orderDesc = false;
	public $POST = [];
	
	public $typeOptions=[
					'text'=>'文本',
					'select'=>'单选',
					'selectm'=>'多选横向',
					'selectms'=>'多选纵向',
				];
	public $adTypeOptions=[
					'n' => '原始值',
					'map' => '单映射',
					'tt1' => '时间(秒)',
					'tt2' => '时间(分)',
					'tt3' => '时间(天)',
					'bit' => '字节位'
				];
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'aad_name','name'=>'名称'],
				['col'=>'aad_key','name'=>'属性'],
				['col'=>'aad_type','name'=>'类型',
					'valMap'=>$this->typeOptions
				],
				['col'=>'aad_mark','name'=>'说明',
					'type'=>'fetch',
					'align'=>'center',
					'goto'=>'showMark',
					'popTitle'=>'详细说明',
					'popWidth'=>'800px',
					'popHeight'=>'500px',
					'modify'=>function($text,$row){
						if(!$text){
							return ['type'=>'text'];
						}
						return $text;
					},
				],
				['col'=>'aad_order','name'=>'排序',
					'type'=>'order',
					'align'=>'center',
					'width'=>'50px',
				],
			],
			
			'toolEnable' => true,
			'toolAddEnable' => true,
			'toolExportEnable' => false,
			'toolRefreshEnable'=> true,
			'operDelEnable'=> true,
			'fenyeEnable'=> false,
			
			'toolSearchColumn'=>[
				'name'=>'like',	
			],
			
		];
		return $gridSet;
	}
	
	
	
	public function crudAddSet(){
		$post=$this->POST;
		
		$back=[];
		$back[]=[
				"name"=>"名称",
				"col"=>"aad_name",
				"ask"=>true,
				"type"=>'text',
		];
		$back[]=[
				"name"=>"属性名",
				"col"=>"aad_key",
				"ask"=>true,
				"type"=>'text',
		];
		$back[]=[
				"name"=>"是否可修改",
				"col"=>"aad_modble",
				"type"=>'radio',
				"value"=>'1',
				'options'=>[
					'0' => '不可修改',
					'1' => '可修改'
				],
				"ask"=>true, 
		];
		$back[]=[
				"name"=>"表单类型",
				"col"=>"aad_type",
				"hintMore"=>"指示表单项所用插件",
				"type"=>'select',
				"value"=>'text',
				'options'=>$this->typeOptions,
				"ask"=>true, 
		];
		$back[]=[
				"name"=>"显示方式",
				"col"=>"aad_adtype",
				"hintMore"=>"用于 显示值--AD值--表单值 之间转换",
				"type"=>'select',
				"value"=>'n',
				'options'=>$this->adTypeOptions,
				"ask"=>true, 
		];
		$back[]=[
				"name"=>"类型扩展",
				"col"=>"aad_typeopt",
				"type"=>'text',
		];
		$back[]=[
				"name"=>"说明",
				"col"=>"aad_mark",
				"type"=>'text',
		];
		return $back;
	}
	
	
	public function crudModSet(){
		$post=$this->POST;
		
		$back=$this->crudAddSet();
		
		return $back;
	}
	
	
	
	public function fetch_showMark(){
		$post=$this->POST;
		$key = $post['key'];
		$row = $this->getById($key);
		
		return $this->out(0, $row['aad_mark']);
	}
	
	
	
}

?>
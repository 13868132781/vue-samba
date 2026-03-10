<?php

namespace app\radPerm;

class permAttr extends \table{
	public $pageName='属性组';
	public $TN = "sdaaa.permattr";
	public $colKey = "gaid";
	public $colOrder = "ga_order";
	public $colFid = "";
	public $colName = "ga_name";
	public $orderDesc = false;
	public $POST = [];
	public $editReg=[
		'attr'=>'属性设置',
	];
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'ga_name','name'=>'名称'],
				['col'=>'ga_mark','name'=>'说明'],
				['col'=>'ga_order','name'=>'排序',
					'type'=>'order',
					'align'=>'center',
					'width'=>'50px',
				],
				['col'=>'ga_attr','name'=>'属性',
					'type'=>'dialog',
					'popTitle'=>'属性设置',
					'router'=>'/radPerm/permAttrList',
					'align'=>'center',
				],
				/*
				['col'=>'ga_attr','name'=>'属性',
					'type'=>'edit',
					'popTitle'=>'属性设置',
					'goto'=>'attr',
					'align'=>'center',
				],
				*/
			],
			
		];
		return $gridSet;
	}
	
	public function crudAddSet(){//获取编辑字段信息
		$back=[];
		$back[]=[
			"name"=>"名称",
			"col"=>"ga_name",
			"type"=>'text',
			"ask"=>true, 
			'valid'=>[
				'type'=>'text',
				'max'=>20,
				'min'=>2
			]
		];
		$back[]=[
			"name"=>"说明",
			"col"=>"ga_mark",
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
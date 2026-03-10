<?php

namespace app\radPerm;

class permShid extends \table{
	public $pageName='时段组';
	public $TN = "sdaaa.permshid";
	public $colKey = "gsid";
	public $colOrder = "";
	public $colFid = "";
	public $colName = "gs_name";
	public $orderDesc = false;
	public $POST = [];
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'gs_name','name'=>'名称'],
				['col'=>'gs_day','name'=>'每天'],
				['col'=>'gs_week','name'=>'每周'],
				['col'=>'gs_month','name'=>'每月'],
				['col'=>'gs_tear','name'=>'每年'],	
				['col'=>'gs_order','name'=>'排序',
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
			"col"=>"gs_name",
			"type"=>'text',
			"ask"=>true, 
			'valid'=>[
				'type'=>'text',
				'max'=>20,
				'min'=>2
			]
		];
		
		$back[]=[
			"name"=>"每天",
			"col"=>"gs_day",
			"hintMore"=>'多个以逗号隔开，跨时用-隔开，不填表示全时段允许',
			"type"=>'text',
		];
		
		$back[]=[
			"name"=>"每周",
			"col"=>"gs_week",
			"hintMore"=>'多个以逗号隔开，跨时用-隔开，不填表示全时段允许',
			"type"=>'text',
		];
		
		$back[]=[
			"name"=>"每月",
			"col"=>"gs_month",
			"hintMore"=>'多个以逗号隔开，跨时用-隔开，不填表示全时段允许',
			"type"=>'text',
		];
		
		$back[]=[
			"name"=>"每年",
			"col"=>"gs_year",
			"hintMore"=>'多个以逗号隔开，跨时用-隔开，不填表示全时段允许',
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
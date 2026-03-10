<?php
namespace app\radAuth;

class authWay extends \table{
	public $pageName='认证方式';
	public $TN = "sdaaa.authway";
	public $colKey = "awid";
	public $colOrder = "aw_order";
	public $colFid = "";
	public $colUnit = "";
	public $colName = "aw_name";
	public $colNafy = "";
	public $orderDesc = false;
	public $POST = [];
	public $radioReg=[
		'enable'=>'修改默认项'
	];
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'awid','name'=>'编号','jsCtrl'=>['width'=>'30px']],
				['col'=>'aw_name','name'=>'名称'],
				['col'=>'aw_mark','name'=>'说明'],
				['col'=>'aw_key','name'=>'关键字'],
				['col'=>'aw_default','name'=>'默认',
					'type'=>'radio',
					'goto'=>'enable',
					'align'=>'center',
					'width'=>'50px',
				],
				['col'=>'aw_order','name'=>'排序',
					'type'=>'order',
					'align'=>'center',
					'width'=>'50px',
				],
			],
			'toolAddEnable' => false,
			'operDelEnable'=> false,
			'fenyeEnable'=> false,
		];
		
		return $gridSet;
	}
	
	public function crudModSet(){//获取编辑字段信息
		$back=[];
		$back[]=[
			"name"=>"说明",
			"col"=>"aw_mark",
			"type"=>'text',
			"ask"=>"", 
		];
		return $back;
	}
	
}
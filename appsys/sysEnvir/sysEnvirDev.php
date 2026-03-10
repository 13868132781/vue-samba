<?php
namespace appsys\sysEnvir;

class sysEnvirDev extends \table{
	public $pageName='环境变量';
	public $TN = "{sysDB}.envir";
	public $colKey = "enid";
	public $colOrder = "en_order";
	public $colFid = "";
	public $colName = "en_name";
	public $colNafy = "";
	public $orderDesc = false;
	public $POST = [];
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'en_name','name'=>'名称'],
				['col'=>'en_key','name'=>'key'],
				['col'=>'en_type','name'=>'数据类型'],
				['col'=>'en_mark','name'=>'说明'],
				['col'=>'en_order','name'=>'排序',
					'type'=>'order',
					'align'=>'center'
				],
			],
		];
		return $gridSet;
	}
	
	public function crudAddSet(){
		
		$back=[];
		$back[]=[
			"name"=>"名称",
			"col"=>"en_name",
			"type"=>'text',
			"ask"=>true, 
		];
		$back[]=[
			"name"=>"键",
			"col"=>"en_key",
			"type"=>'text',
			"ask"=>true, 
		];
		$back[]=[
			"name"=>"值类型",
			"col"=>"en_type",
			"type"=>'select',
			"options"=>[
				'text'=>'文本',
				'select'=>'下拉框',
			],
			"ask"=>true, 
		];
		$back[]=[
			"name"=>"选项",
			"col"=>"en_mode",
			"type"=>'text',
			"hintMore"=>'下拉框选项json串', 
		];
		return $back;
	}
	
	public function crudModSet(){
		return $this->crudAddSet();
	}
	
}
?>
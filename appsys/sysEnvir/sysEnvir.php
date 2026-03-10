<?php
namespace appsys\sysEnvir;

class sysEnvir extends \table{
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
				['col'=>'en_val','name'=>'值',
					"modify"=>function($val,$row){
						if($row['en_type']=='select'){
							$options = json_decode($row['en_mode'],true);
							if(isset($options[$val])){
								return $options[$val];
							}
						}
						return $val;
					}
				],
				['col'=>'en_mark','name'=>'说明'],
				['col'=>'en_order','name'=>'排序',
					'type'=>'order',
					'align'=>'center'
				],
			],
			'toolEnable' => false,
			'operDelEnable'=> false,
		];
		return $gridSet;
	}
	
	public function crudModSet(){
		$key = $this->POST['key'];
		$row = $this->getById($key);
		
		$back=[];
		$back[]=[
			"name"=>"名称",
			"col"=>"en_name",
			"type"=>'show',
			"ask"=>true, 
		];
		$back[]=[
			"name"=>"键",
			"col"=>"en_key",
			"type"=>'show',
			"ask"=>true, 
		];
		$back[]=[
			"name"=>"值",
			"col"=>"en_val",
			"type"=>$row['en_type'],
			"options"=>json_decode($row['en_mode'],true),
			"ask"=>true, 
		];
		return $back;
	}
	
	
	public static function eGet($key){
		$th=new static();
		return $th->DB()->where("en_key",$key)->value('en_val');
	}
	
	public static function eGets(){
		$th=new static();
		$rows = $th->DB()->get();
		$back=[];
		foreach($rows as $row){
			$back[$row['en_key']]=$row['en_val'];
		}
		return $back;
	}
	
}
?>
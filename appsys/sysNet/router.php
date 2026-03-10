<?php

namespace appsys\sysNet;

class router extends \table {
	public $pageName="系统网络";
	public $TN = "---";
	public $colKey = "target";
	public $colOrder = "";
	public $colFid = "";
	public $colName = "target";
	public $orderDesc = true;
	public $POST = [];
	public $zdyBackend=true;

	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'target','name'=>'名称'],
				['col'=>'mask','name'=>'掩码'],
				['col'=>'gate','name'=>'网关'],
				['col'=>'eth','name'=>'网卡'],
				
			],
				
			
			'toolEnable' => true,
			'toolAddEnable' => true,
			'toolExportEnable' => false,
			'toolRefreshEnable'=> true,
			'operModEnable'=> false,
			'fenyeEnable'=> false,
		];
		return $gridSet;
	}
	
	public function zdyData(){
		$datamap=$this->getRoute();
		$data=[];
		foreach($datamap as $k=>$da){
			if($k=='default' or $k=='localnet'){
				$da['_operDelEnable_']=false;
			}
			$data[]=$da;
		}
		
		return $data;
	}
	
	
	public function crudAddSet(){
		$post=$this->POST;

		$back=[];
		$back[]=[
			"name"=>"ip",
			"col"=>"ip",
			"type"=>'text',
			"ask"=>true, 
			'valid'=>[
				'type'=>'ip',
			]
		];
		$back[]=[
			"name"=>"掩码",
			"col"=>"mask",
			"type"=>'text',
			"ask"=>true, 
			'valid'=>[
				'type'=>'ip',
			]
		];
		$back[]=[
			"name"=>"网关",
			"col"=>"gate",
			"type"=>'text',
			"ask"=>true, 
			'valid'=>[
				'type'=>'ip',
			]
		];
		
		return $back;
	}
	
	public function crudAddBefore(){
		
		return $this->out(1,'','开发者');	
	}
	
	public function crudDelBefore(){
		
		return $this->out(1,'','开发者');
	}
	
	
	public function getRoute(){
		exec("route| grep -v \"routing\" | grep -v \"Destination\" | awk '{print $1\"@\"$3\"@\"$2\"@\"$8}'",$routes);
		//dump($routes);
		$data=[];
		foreach($routes as $routs){
			$route = explode('@',$routs);
			//$key = str_replace('.','_',$route[0]);
			$data[$route[0]]=[
				'target'=>$route[0],
				'mask'=>$route[1],
				'gate'=>$route[2],
				'eth'=>$route[3],
			];
		}
		return $data;
	}
	
	
}

?>
<?php
namespace app\radNas;
use appsys\auth\auth;

class radNasOrgan extends \table{
	public $pageName='设备机构';
	public $TN = "sdaaa.nasorgan";
	public $colKey = "onid";
	public $colOrder = "on_order";
	public $colFid = "on_fid";
	public $colName = "on_name";
	public $orderDesc = false;
	public $POST = [];
	
	public function __myconstruct(){
		$this->treeBeginId = (new auth)->getAuthAcctOrganNas();
	}
	
	
	public function gridSet(){
		$that = $this;
		
		$gridSet=[
			'columns'=>[
				['col'=>'on_name','name'=>'名称'],
				['col'=>'on_mark','name'=>'说明'],
				[
					'col'=>'on_order',
					'name'=>'排序',
					'type'=>'order',//text html button order
					'width'=>'',
					'align'=>'middle',	
				]
				
			],
			
			'treeInfo' => [
				'col'=>'on_name',
				'depth'=>-1,//默认打开层级，-1所有层级
			],
			
			'toolEnable' => true,
			'toolAddEnable' => true,
			'toolExportEnable' => true,
			'toolRefreshEnable'=> true,
				
			'operEnable' => true ,
			'operModEnable'=> true,
			'operDelEnable'=> true,
			'operDelHintMsg'=> "此机构下的设备将全被删除。",
				
			'fenyeEnable'=> false,
			'fenyeNum'=> 20,//默认20 
		];
		return $gridSet;
	}
	
	public function crudAddSet(){//获取编辑字段信息
		$back=[];
		$back[]=[
			"name"=>"机构名",
			"col"=>"on_name",
			"type"=>'text',
			"ask"=>true, 
		];
		$back[]=[
			"name"=>"说明",
			"col"=>"on_mark",
			"type"=>'text',
			"ask"=>"", 
		];
		$back[]=[
			"name"=>"所属",
			"col"=>"on_fid",
			"type"=>'treePick',
			"value"=>'0',
			"router"=>'/radNas/radNasOrgan',
			//"ask"=>true, 
		];
		return $back;
	}
	
	public function crudModSet(){
		return $this->crudAddSet();
	}
	
	public function crudDelAfter(){
		$post = $this->POST;
		$key = $post['key'];
		//删除机构下的设备
		radNas::DB()->where('na_organ',$key)->delete();
	}
	
	
	public function getFirst(){
		if($this->treeBeginId){
			$obj=$this->DB()->where($this->colKey,$this->treeBeginId);
		}else{
			$obj=$this->DB()
			->where($this->colFid,0)
			->orderBy($this->colOrder);
		}
			
		$row = $obj->first();
		return $row;
	}
	
}


?>
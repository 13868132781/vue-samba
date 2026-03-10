<?php
namespace app\radUser;
use appsys\auth\auth;
use app\radPerm\perm;

class radUserOrgan extends \table{
	public $pageName='用户机构';
	public $TN = "sdaaa.raduserorgan";
	public $colKey = "ouid";
	public $colOrder = "ou_order";
	public $colFid = "ou_fid";
	public $colName = "ou_name";
	public $orderDesc = false;
	public $POST = [];
	
	public function __myconstruct(){
		$this->treeBeginId = (new auth)->getAuthAcctOrganUser();
	}
	
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'ou_name','name'=>'名称'],
				['col'=>'ou_mark','name'=>'说明'],
				[
					'col'=>'ou_order',
					'name'=>'排序',
					
					'type'=>'order',//text html button order
					'width'=>'',
					'align'=>'center',
				]
				
			],
			'treeInfo' => [
				'col'=>'ou_name',
				'depth'=>-1,//默认打开层级，-1所有层级
			],
			
			'toolEnable' => true,
			'toolAddEnable' => true,
			'toolExportEnable' => true,
			'toolRefreshEnable'=> true,
				
			'operEnable' => true ,
			'operModEnable'=> true,
			'operDelEnable'=> true,
			'operDelHintMsg'=> "此机构下的用户将全被删除。",
				
			'fenyeEnable'=> false,
			'fenyeNum'=> 20,//默认20 
		];
		return $gridSet;
	}
	
	public function crudAddSet(){
		$back=[];
		$back[]=[
			"name"=>"机构名",
			"col"=>"ou_name",
			"type"=>'text',
			"ask"=>true, 
		];
		$back[]=[
			"name"=>"说明",
			"col"=>"ou_mark",
			"type"=>'text',
			"ask"=>"", 
		];
		$back[]=[
			"name"=>"所属",
			"col"=>"ou_fid",
			"type"=>'treePick',
			"value"=>'',
			"sqlValue"=>'0',//form传来如果为空，就填这个值
			"router"=>'/radUser/radUserOrgan',
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
		//删除机构下的用户
		radUser::DB()->where('us_organ',$key)->delete();
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
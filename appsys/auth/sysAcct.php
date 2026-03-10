<?php
/*
登录验证
function acctVerify()
*/

namespace appsys\auth;
use appsys\auth\auth;
use appsys\sysEnvir\sysEnvir;

class sysAcct extends \table{
	public $pageName='系统管理员';
	public $TN = "{sysDB}.sysacct";
	public $colKey = "suid";
	public $colOrder = "";
	public $colFid = "";
	public $colName = "su_name";
	public $colNafy = "su_user";
	public $orderDesc = false;
	public $POST = [];
	
	public $colType="su_type";
	public $colOrganUser="su_organuser";
	public $colOrganNas="su_organnas";
	
	public $suType = [
				'super'=>'超级管理员',
				'normal'=>'普通管理员',
				'audit'=>'审计员'
			];
	
	public function gridBefore($db){
		$post = $this->POST;
		
		$loginRow = (new auth)->getAuthAcctInfo();
		
		if($loginRow['su_type']!='super'){
			return $db->where("suid",$loginRow['suid']);
		}
	}

	
	
	public function gridSet(){
		$loginRow = (new auth)->getAuthAcctInfo();

		$gridSet=[
			'columns'=>[
				['col'=>'su_name','name'=>'姓名'],
				['col'=>'su_user','name'=>'登录名'],
				['col'=>'su_type','name'=>'类型',
					'valMap'=>$this->suType
				],
				//['col'=>'su_mark','name'=>'说明'],
				//['col'=>'su_status','name'=>'状态'],
			],
			'rowOper'=>function($row){
				if($row['su_user']=='system'){
					//$row['_operModEnable_']=false;
					$row['_operDelEnable_']=false;
				}
				return $row;
			},
			
			'toolEnable' => true,
			'toolAddEnable' => $loginRow['su_type']=='super'?true:false,
			'toolExportEnable' => false,
			'toolRefreshEnable'=> true,
				
			'operEnable' => true ,
			'operModEnable'=> true,
			'operDelEnable'=> $loginRow['su_type']=='super'?true:false,
				
				
			'fenyeEnable'=> true,
			'fenyeNum'=> 20,//默认20 
		];
		return $gridSet;
	}
	
	public function crudAddSet(){//获取编辑字段信息
		$back=[];
		
		$back[]=[
			"name"=>"姓名",
			"col"=>"su_name",
			"type"=>'text',
			"ask"=>true,
		];
		$back[]=[
			"name"=>"登录名",
			"col"=>"su_user",
			"type"=>'text',
			"ask"=>true, 
		];
		$back[]=[
			"name"=>"密码",
			"col"=>"su_pass",
			"type"=>'password',
			"crypt"=>'md5',
			"ask"=>true, 
			'valid'=>[
				'type'=>'password',
				'cxty'=>sysEnvir::eGet("admin_pass_cxty"),
			]
		];
		$back[]=[
			"name"=>"密码确认",
			"col"=>"su_pass1",
			"type"=>'password',
			"ask"=>true, 
			"ignore"=>true,
			'valid'=>[
				'type'=>'same',
				'as' =>'su_pass',
			],
		];
		
		$row = (new auth)->getAuthAcctInfo();
		if($row['su_type']=='super'){
		$back[]=[
			"name"=>"类型",
			"col"=>"su_type",
			"type"=>'select',
			"options"=>$this->suType,
			"value"=>'0',
			"ask"=>true, 
		];
		/*
		$back[]=[
			"name"=>"所属用户机构",
			"hintMore"=>'不选则管理全部',
			"col"=>"su_organuser",
			"type"=>'treePick',
			"router"=>'/radUser/radUserOrgan',
		];
		$back[]=[
			"name"=>"所属设备机构",
			"hintMore"=>'不选则管理全部',
			"col"=>"su_organnas",
			"type"=>'treePick',
			"router"=>'/radNas/radNasOrgan',
		];*/
		}
		return $back;
	}
	public function crudModSet(){
		$back= $this->crudAddSet();
		foreach($back as $k=>$b){
			if($b['col']=='su_pass'){
				$back[$k]['ask']=false;
			}
			if($b['col']=='su_pass1'){
				$back[$k]['ask']=false;
			}
		}
		return $back;
	}
	
	
	public function acctVerify($args){
		//echo json_encode($args);
		if(!isset($args['user']) or !isset($args['pass'])){
			return 1;
		}
		$user = $args['user'];
		$pass = $args['pass'];
		$c=$this->DB()
			->where("su_user",$user)
			->where("su_pass",md5($pass))
			->count();
		
		$code=1;
		if($c>0){
			$code=0;
		}
		return $code;//返回成败
	}
	
}


?>
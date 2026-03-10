<?php

/*
审计日志
function auditWrite()
*/

namespace appsys\auth;
use appsys\auth\auth;

class sysOper extends \table{
	public $pageName='系统操作审计';
	public $TN = "{sysDB}.zlog_sysoper";
	public $colKey = "soid";
	public $colOrder = "";
	public $colFid = "";
	public $colName = "so_acctname";
	public $colNafy = "so_acctuser";
	public $orderDesc = true;
	public $POST = [];
	
	public function gridBefore($db){
		$post = $this->POST;
		
		$loginRow = (new auth)->getAuthAcctInfo();
		
		if($loginRow['su_type']!='super'){
			return $db->where("so_acctuser",$loginRow['su_user']);
		}
	}
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'so_time','name'=>'时间'],
				['col'=>'so_acctname','name'=>'用户'],
				['col'=>'so_acctuser','name'=>'登录名'],
				['col'=>'so_page','name'=>'页面'],
				['col'=>'so_oper','name'=>'操作'],
				['col'=>'so_name','name'=>'对象'],
				['col'=>'so_client','name'=>'客户端'],
				['col'=>'so_code','name'=>'结果',
					'modify'=>function($text){
						if($text=='0'){
							return '1';
						}else{
							return '0';
						}
					},
					'type'=>'state',
					'disable'=>true,//表示点击无效
					'align'=>'center',
					'width'=>'50',
				],
				['col'=>'so_msg','name'=>'信息',
					'width'=>'150px',
					'ellipsis'=>true,
					'showInDlg'=>true,
				],
				/*
				['col'=>'so_detail','name'=>'详情',
					'type'=>'fetch',
					'goto'=>'operDetal',
				],
				*/
			],
			
			
			'toolEnable' => true,
			'toolAddEnable' => false,
			'toolExportEnable' => true,
			'toolRefreshEnable'=> true,
				
			'operEnable' => false ,
			'operModEnable'=> true,
			'operDelEnable'=> true,
				
				
			'fenyeEnable'=> true,
			'fenyeNum'=> 20,//默认20 
			
			'toolSearchColumn'=>[
				'so_acctname'=>'like',	
				'so_page'=>'like',
				'so_oper'=>'like',
				'so_name'=>'like',
				'so_client'=>'like',
			],
			
		];
		return $gridSet;
	}
	
	
	public function fetch_operDetal(){
		$this->noAudit = true;//不审计该操作
		$post = &$this->POST;
		$row = $this->currentRow;
		$data=[];
		foreach($row as $k=>$v){
			$data[]=[$k,$v];
		}
		return $this->out(0,$data);
	}
	
	
	public static function auditWrite($page,$oper,$name,$nafy,$code,$msg){
		$client = $_SERVER['REMOTE_ADDR'];
		$acctName= (new auth)->getAuthAcctName();
		$acctUser= (new auth)->getAuthAcctNafy();
		$session=(new auth)->getSeid();
		
		self::DB()->insert([
			'so_time'=>\DB::raw('now()'),
			'so_page'=> $page,
			'so_oper'=> $oper,
			'so_name'=> $name,
			'so_nafy'=> $nafy,
			'so_code'=> $code,
			'so_msg'=> $msg,
			'so_acctname'=> $acctName,
			'so_acctuser'=> $acctUser,
			'so_client'=> $client,
			'so_session'=> $session,
		]);
		
		//\app\syslog\syslog::send('sysoper',$acctUser.' '.$oper.' '.$name.'('.$name.') '.$code);
		
	}
	
	
}


?>
<?php

/*
登录检查，会话是否有效
function loginCheck()
登录日志
function loginWrite()
登出日志
function logoutWrite()
*/

namespace appsys\auth;
use appsys\auth\auth;
use appsys\sysEnvir\sysEnvir;

class sysLogin extends \table{
	public $pageName='系统登录审计';
	public $TN = "{sysDB}.zlog_syslogin";
	public $colKey = "slid";
	public $colOrder = "";
	public $colFid = "";
	public $colName = "sl_acctname";
	public $colNafy = "sl_acctuser";
	public $orderDesc = true;
	public $POST = [];
	
	public function gridBefore($db){
		$post = $this->POST;
		
		$loginRow = (new auth)->getAuthAcctInfo();
		
		if($loginRow['su_type']!='super'){
			return $db->where("sl_acctuser",$loginRow['su_user']);
		}
	}
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'sl_acctname','name'=>'用户名'],
				['col'=>'sl_acctuser','name'=>'登录名'],
				['col'=>'sl_client','name'=>'客户端'],
				['col'=>'sl_starttime','name'=>'开始'],
				['col'=>'sl_stoptime','name'=>'结束'],
				['col'=>'sl_status','name'=>'状态',
					'modify'=>function($text){
						if($text=='0'){
							return '离线';
						}else{
							return '在线';
						}
					},
					'dotMap'=>[
						'在线'=>'#385E0F',
						'离线'=>'#FF9912',
					],
					'align'=>'center',
					'width'=>'50',
				],
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
				'sl_acctname'=>'like',	
				'sl_acctuser'=>'like',
				'sl_client'=>'like',
			],
		];
		return $gridSet;
	}
	
	//关闭已经过期失效的会话
	public function loginCloseInvalid(){
		$outtime='30';//30分钟
		$outtime=sysEnvir::eGet("timeout");
		$this->DB()
			->where('sl_status','1')
			->where('sl_stoptime','<',\DB::raw('DATE_SUB(now(),INTERVAL '.$outtime.' MINUTE)'))
			->update([
				'sl_status'=>'0',
			]);
	}
	
	
	public function loginCheck($seid,$update){//检查seid是否有效
		$outtime='30';//30分钟
		$outtime=sysEnvir::eGet("timeout");
		$row = $this->DB()
			->where($this->colKey,$seid)
			->where('sl_status','1')
			//->where('sl_stoptime','>',\DB::raw('DATE_SUB(now(),INTERVAL '.$outtime.' MINUTE)'))
			->first();
		$code=0;
		if(!$row){
			return 1;
		}
		if(time()-strtotime($row['sl_stoptime'])>$outtime*60){//会话无效
			$this->DB()
			->where($this->colKey,$seid)
			->update([
				'sl_status'=>'0',
			]);
			$code=1;
		}else{//会话还有效
			if($update){//访问页面时触发的登录检查
				//避免频繁更新表以及并发冲突,隔60-120内随机一段时间，再更新
				if(time()-strtotime($row['sl_stoptime'])>mt_rand(60,120)){
					$this->DB()
					->where($this->colKey,$seid)
					->update([
						'sl_stoptime'=>\DB::raw('now()'),
					]);
				}
			}else{//每分钟的定时检测
				//触发一次关闭失效会话
				if(time()-strtotime($row['sl_stoptime'])>mt_rand(600,1200)){
					$this->loginCloseInvalid();
				}
			}
		}
		return $code;
	}
	
	public function loginWrite($user,$name){//写登录日志
		$seid=$this->DB()->insert([
			'sl_starttime'=>\DB::raw('now()'),
			'sl_stoptime'=>\DB::raw('now()'),
			'sl_acctname' => $name,
			'sl_acctuser'=>$user,
			'sl_client'=>$_SERVER['REMOTE_ADDR'],
			'sl_status'=>'1',
		]);
		
		//\app\syslog\syslog::send('syslogin',$user.' '.$name.' '.$_SERVER['REMOTE_ADDR']);
		
		//触发一次关闭失效会话
		$this->loginCloseInvalid();
		
		return $seid;
	}
	public function logoutWrite($seid){//更新状态为0
		$this->DB()
		->where($this->colKey,$seid)
		->update([
			'sl_status'=>'0',
			'sl_stoptime'=>\DB::raw('now()'),
		]);
		
	}
	
}


?>
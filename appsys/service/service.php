<?php
namespace appsys\service;
use appsys\sysAlarm\sysAlarm;

class service extends \table{
	public $pageName="系统服务";
	public $TN = "{sysDB}.service";
	public $colKey = "svid";
	public $colOrder = "sv_order";
	public $colFid = "";
	public $colName = "sv_name";
	public $orderDesc = false;
	public $POST = [];
	public $classPrex = "appsys\service\srv_";
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'sv_name','name'=>'名称'],
				['col'=>'sv_mark','name'=>'说明'],
				['col'=>'onoff_start','name'=>'启停',
					'type'=>'onoff',
					'goto'=>'start'
				],
				['col'=>'onoff_debug','name'=>'调试',
					'modify'=>function($val,$row){
						if(!$row['sv_debugable']){
							return [
								'value'=>'',
								'type'=>'text',
							];
						}
						return $val;
					},
					'type'=>'onoff',
					'goto'=>'debug'
				],
				['col'=>'sv_log','name'=>'日志',
					'type'=>'fetch',
					'popTitle'=>'日志信息',
					'align'=>'center',
					'zdyCom'=>'srvDebug',
					'goto'=>'debug',
					'popWidth'=>'90%',
					'popHeight'=>'90%',
				],
			],
			
			'toolEnable' => false,
			'operEnable' => false,
			'fenyeEnable'=> false,
			
		];
		return $gridSet;
	}
	
	public function gridAfter(&$data){
		foreach($data as $k=>$row){
			$class = $this->classPrex.$row['sv_class'];
			$class::do_init($row['sv_script'],$row['sv_debug']);
			$res = $class::do_status();
			$data[$k]['onoff_start'] = $res;
			if($row['sv_debugable']){
				$res = $class::do_statusDebug();
				$data[$k]['onoff_debug'] = $res;
			}
		}
	}
	
	public function onoffBefore_start(){
		$post=$this->POST;
		$key = $post['key'];
		$row = $this->getById($key);
		$class = $this->classPrex.$row['sv_class'];
		$class::do_init($row['sv_script'],$row['sv_debug']);
		$sv_start = $post['val'];
		if($sv_start==0){
			$res = $class::do_stop();
		}else{
			$res = $class::do_start();
		}
		if(!$res){
			$sety = $sv_start;
			$this->setDBSety($key,$sety);
			
			$msg = $sv_start==0?'停止服务':'启动服务';
			return $this->out(0,'',$msg.'成功',true,'hlc.$emit("onServiceUpdate");');
		}else{
			return $this->out(1,'',$res);
		}
		
	}
	
	public function onoffBefore_debug(){
		$post=$this->POST;
		$key = $post['key'];
		$row = $this->getById($key);
		$class = $this->classPrex.$row['sv_class'];
		$class::do_init($row['sv_script'],$row['sv_debug']);
		$sv_debug = $post['val'];
		if($sv_debug==0){
			$res = $class::do_stopDebug();
		}else{
			$res = $class::do_startDebug();
		}
		if(!$res){
			$sety = $sv_debug?'2':'1';
			$this->setDBSety($key,$sety);
			
			$msg = $sv_debug==0?'停止调试':'启动调试';
			return $this->out(0,'',$msg.'成功',true,'hlc.$emit("onServiceUpdate");');
		}else{
			return $this->out(1,'',$res);
		}
		
	}
	
	public function fetch_debug(){
		$this->noAudit=true;
		$post=$this->POST;
		$key = $post['key'];
		$row = $this->getById($key);
		$class = $this->classPrex.$row['sv_class'];
		$class::do_init($row['sv_script'],$row['sv_debug']);
		$res = $class::do_debugInfo();
		return $this->out(0,$res);
	}
	
	////给后台维护服务调用的，清理所以服务得日志文件，避免过大
	public function logClear(){
		$data = $this->DB()->get();
		foreach($data as $row){
			$class = $this->classPrex.$row['sv_class'];
			$class::do_init($row['sv_script'],$row['sv_debug']);
			$res = $class::do_status();
			$class::do_logClear();
		}
	}
	
	//给后台维护服务调用的，检测服务状态和设定状态是否一致
	public function statusCheck(){
		$data = $this->DB()->get();
		foreach($data as $row){
			if($row['sv_name']=='系统维护'){
				continue;
			}
			
			$class = $this->classPrex.$row['sv_class'];
			$class::do_init($row['sv_script'],$row['sv_debug']);
			$res = $class::do_status();
			$ress = $class::do_statusdebug();
			$back=[];
			$msg='';
			if($row['sv_sety']==0){
				if($res==1){
					$msg="设定不启动，结果为启动";
				}
				if($ress==1){
					$msg="设定不启动，结果为调试启动";
				}
			}
			if($row['sv_sety']==1){
				if($res==0){
					$msg="设定为启动，结果未启动";
				}
				if($ress==1){
					$msg="设定为启动，结果为调试启动";
				}
			}
			if($row['sv_sety']==2){
				if($res==0){
					$msg="设定为调试启动，结果未启动";
				}
				if($ress==0 and $res==1){
					$msg="设定为调试启动，结果为启动";
				}
			}
			if($msg){
				(new sysAlarm)->alarmMake([
					'from'=>'service','name'=>$row['sv_name'],'msg'=>$msg
				]);
			}
			//$class::do_logClear();
		}
	}
	
	//设置表里的设定状态值，该值用于检测服务状态是否符合设定
	public function setDBSety($key,$sety){
		$this->DB()->where($this->colKey,$key)
			->update([
				'sv_sety'=>$sety,
		]);
	}
	
}


?>
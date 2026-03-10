<?php
namespace appsys\sysCron;
use appsys\sysAlarm\sysAlarm;

class sysCron extends \table{
	public $pageName='系统调度';
	public $TN = "{sysDB}.cron";
	public $colKey = "crid";
	public $colOrder = "cr_order";
	public $colFid = "";
	public $colName = "cr_name";
	public $colNafy = "";
	public $orderDesc = false;
	public $POST = [];
	
	public function gridAfter(&$data){
		require(__DIR__.'/ht/check.php');
		
		foreach($data as $ri=>$row){
			$id = $row['crid'];
			$result = checkDIsPsLine($id);
			if(count($result)>0){
				$data[$ri]['cr_isexec']='正在运行/'.$result[0]['start'];
			}
		}
	}
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'cr_name','name'=>'名称'],
				['col'=>'cr_time','name'=>'周期'],
				['col'=>'cr_timeout','name'=>'超时'],
				['col'=>'cr_count','name'=>'次数'],
				
				['col'=>'cr_enable','name'=>'启停',
					'type'=>'onoff',
					'goto'=>'enable'
				],
				['col'=>'en_order','name'=>'排序',
					'type'=>'order',
					'align'=>'center'
				],
				['col'=>'cr_isexec','name'=>'执行',
					'type'=>'execute',
					'name'=>'执行',
					'goto'=>'doOne',
					'modify'=>function($text){
						if($text){
							return [
								'type'=>'text',
								'value'=>$text,
							];
						}
					}
				],
				
				
				['col'=>'cr_last_time','name'=>'最后执行: 时间',
					'width'=>'150px',
					//'colspan'=>[
					//	'num'=>4,
					//	'name'=>'最后一次执行的信息'
					//]
				],
				['col'=>'cr_last_code','name'=>'结果',
					'width'=>'50px',
					'valMap'=>[
						'0'=>'成功',
						'_default_'=>'失败',
					],
					'dotMap'=>[
						'成功'=>'#385E0F',
						'失败'=>'#FF9912',
					],
				],
				['col'=>'cr_last_uset','name'=>'耗时','width'=>'60px',],
				['col'=>'cr_last_log1','name'=>'详情','width'=>'50px',
					'type'=>'fetch',
					//'name'=>'最后一次执行的信息',
					'goto'=>'lastInfo'
				],
				
				
				/*
				['col'=>'cr_lastinfo','name'=>'结果',
					'type'=>'fetch',
					//'name'=>'最后一次执行的信息',
					'goto'=>'lastInfo'
				],
				*/
			],
			'toolEnable' => false,
			'operDelEnable'=> false,
		];
		return $gridSet;
	}
	
	public function crudAddSet(){
		
		$back=[];
		$back[]=[
			"name"=>"姓名",
			"col"=>"cr_name",
			"type"=>"show",
			"ask"=>true, 
		];
		$back[]=[
			"name"=>"周期",
			"col"=>"cr_time",
			"type"=>"text",
			"hintMore" => "每分钟：* * * * *，每10分钟：*/10 * * * *，每3小时：0 */3 * * *，每天4点：0 4 * * *",
			"ask"=>true, 
		];
		$back[]=[
			"name"=>"超时",
			"col"=>"cr_timeout",
			"hintMore"=>"10s / 10m / 10h / 10d 默认12h",
			"type"=>"text",
		];
		$back[]=[
			"name"=>"命令",
			"col"=>"cr_script",
			"type"=>"show",
			"ask"=>true,
		];
		return $back;
	}
	
	public function crudModSet(){
		$key = $this->POST['key'];
		$row = $this->getById($key);
		
		
		return $this->crudAddSet();
	}
	
	public function crudModAfter(){
		$this->writeToFile();
	}
	
	public function onoffAfter_enable(){
		$this->writeToFile();
	}
	
	public function execute_doOne(){
		//忽略客户端断开状态，即apache即使感知到浏览器断开，也不终止本脚本
		ignore_user_abort(true);
		$key = $this->POST['key'];
		$scpath = realpath(__DIR__."/ht/doCron.php");
		exec("sudo php ".$scpath." ".$key." 1 2>&1",$res,$code);
		if($code!=0){
			return $this->out(1,'',join('.',$res));
		}
		return $this->out(0);
	}
	
	//功能和上面一样，不过是给外部类用的
	public function execute_doExec(){
		//忽略客户端断开状态，即apache即使感知到浏览器断开，也不终止本脚本
		ignore_user_abort(true);
		$key = $this->POST['key'];
		$scpath = realpath(__DIR__."/ht/doCron.php");
		exec("sudo php ".$scpath." ".$key." 1 2>&1",$res,$code);
		if($code!=0){
			return $this->out(1,'',join('.',$res));
		}
		//外部调用通常调用成功后，要刷新页面
		return $this->out(0,'','执行成功',true);
	}
	
	
	public function fetch_lastInfo(){
		$this->noAudit=true; //不审计该操作
		
		$key = $this->POST['key'];
		$row = $this->getById($key);
		
		$data='';
		$data.="时间：".$row['cr_last_time']."\n\n";
		$data.="结果：".$row['cr_last_code']."\n\n";
		$data.="耗时：".$row['cr_last_uset']."\n\n";
		$data.="过程：\n".$row['cr_last_log']."\n\n";
		
		return $this->out(0,$data);
	}
	
	//给后台维护服务调用的，检测调度最后一次运行是否出错
	public function statusCheck(){
		$isRun=[];
		$rows = $this->DB()->where('cr_enable','1')->get();
		foreach($rows as $row){
			if($row['cr_last_code']!='0'){
				(new sysAlarm)->alarmMake([
					'from'=>'cron',
					'name'=>$row['cr_name'],
					'msg'=>'运行出错，详情参考调度页面',
					'htime'=>$row['cr_last_time'],
				]);
			}
			
		}
	}
	
	
	public function writeToFile(){
		global $sysCfgInfo;
		$sysDB = $sysCfgInfo['sysDB'];//config.php里设定的站点名
		
		$filename = '/etc/cron.d/sdcron_do_not_modify_'.$sysDB;
		$tempname = '/tmp/sdcron_'.$sysDB;
		exec('sudo rm '.$filename);
		exec('sudo rm '.$tempname);
		
		$myfile = fopen($tempname, "w");
		fwrite($myfile, 'SHELL=/bin/sh'."\r\n" );
		fwrite($myfile, 'PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin'."\r\n" );
			
		$rows = $this->DB()->get();
			
		foreach($rows as $row){
			if($row['cr_enable']=='0'){
				continue;
			}
			$id = $row['crid'];
			//fwrite($myfile, "#".$row->cr_name." ".$row->cr_mark );
			$scpath = realpath(__DIR__."/ht/doCron.php");
			fwrite($myfile, $row['cr_time']." root php ".$scpath." ".$id." 0 \n" );
		}
		fclose($myfile); 
		
		exec('sudo -u root mv '.$tempname.' '.$filename);
		
		//fopen打开的文件，是www-data用户的
		//但cron只执行root用户的文件
		exec('sudo chgrp root '.$filename);
		exec('sudo chown root '.$filename);
		
	}
	
	
	public function cronTime2Good($time){
		$parts=explode(' ',$time);
		if(count($parts)!=5){
			return;
		}
		
		$readStr=['分','时','天','月','周'];
		$readable = '';
		foreach($parts as $i=>$parto){
			if($parto=='*'){
				if($readStr[$i]=='周'){
					$readable.= '周内每天';
				}else{
					$readable.= '每'.$readStr[$i];	
				}
			}elseif(stristr($parto,'/')){
				$pernum = explode('/',$parto)[0];
				if($readStr[$i]=='周'){
					$readable.= '周内每'.$pernum.'天';
				}else{
					$readable.= '每'.$pernum.$readStr[$i];	
				}
			}else{
				if($readStr[$i]=='周'){
					$readable.= '周'.str_ireplace(',',',周',$parto);
				}else{
					$readable.= '第'.$parto.$readStr[$i];
				}
			}
			$readable.= " ";
		}
		return $readable;
	}
	
	
}
?>
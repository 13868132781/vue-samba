<?php
/*
所以服务控制类的基类

self::取的是srv_
static::取的是srv_default

*/
namespace appsys\service;

class srv_{
	
	public static function do_init($script,$debug){
		if(method_exists(static::class , "init")){
			static::init($script,$debug);
		}
	}
	
	
	public static function do_status(){
		$res = static::status();
		$st = 0;
		if($res){
			$st = 1;
		}
		return $st;
	}
	
	public static function do_statusDebug(){
		$res = static::statusDebug();
		$st = 0;
		if($res){
			$st = 1;
		}
		return $st;
	}
	
	public static function do_start(){
		self::do_stop();
		$err = static::start();
		if($err){
			return $err;
		}else{
			$res=self::do_status();
			if($res){
				return ;
			}else{
				return '启动失败';
			}	
		}
	}
	public static function do_stop(){
		$err = static::stop();
		if($err){
			return $err;
		}else{
			$res=self::do_status();
			if($res){
				return '关闭失败';
			}else{
				return ;
			}	
		}
	}
	
	public static function do_startDebug(){
		self::do_stop();
		$err = static::startDebug();
		if($err){
			return $err;
		}else{
			$res=self::do_statusDebug();
			if($res){
				return ;
			}else{
				return '启动调试失败';
			}	
		}
	}
	
	public static function do_stopDebug(){
		$err = static::stopDebug();
		if($err){
			return $err;
		}else{
			$res=self::do_statusDebug();
			if($res){
				return '关闭调试失败';
			}else{
				self::do_start();
				return ;
			}	
		}
	}
	
	public static function do_debugInfo(){
		return static::debugInfo();
	}
	
	public static function do_logClear(){
		$logFile = static::getLogFile();
		if($logFile and file_exists($logFile)){
			$fileSize = filesize($logFile);
			if($fileSize> 1024*1024){
				exec("sudo echo '' > ".$logFile." 2>&1");
			}
		}
	}
	
	
	//下面两个函数，获取和杀死子孙进程
	public static function killAllPids($keyStr){
		//这样杀进程有些注意事项
		//有些程序，子进程删了，父进程会立即重建
		//有些程序，子进程删了，父进程也会立即退出
		//kill是发信号停止程序，有一定延迟性
		//若从前往后杀的话，子进程可能成为僵尸进程
		$keyStr = str_ireplace("'","",$keyStr);
		$keyStr = str_ireplace('"',"",$keyStr);
		exec("ps -ef |grep '".$keyStr."'|grep -v grep|awk '{print $2}'",$res);
		$pids=[];
		foreach($res as $pid){
			$pids[]=$pid;
			$pidsa = self::getSonPids($pid);
			$pids = array_merge($pids,$pidsa);
		}
		self::delSonPids($pids);
	}
	
	public static function getSonPids($fid){
		$backa=[];
		exec("sudo ps -o pid --no-header --ppid ".$fid,$res);
		foreach($res as $ress){
			$backa[]=$ress;
			$back = self::getSonPids($ress);
			$backa = array_merge($backa,$back);
		}
		return $backa;
	}
	public static function delSonPids($allson){
		//$this->sdSay("kill pid: ".join(',',$allson));
		foreach($allson as $son){
			exec("sudo kill -9 ".$son." 2>&1");
		}
	}
	
	
}


?>
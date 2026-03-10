<?php
/*
默认的服务控制类

提供的script，如：
php ./mulu/name.php

若有调试模式的话，规定是接收一个debug参数，如：
php ./mulu/name.php debug

*/
namespace appsys\service;

class srv_default extends srv_{
	public static $cmdScript='gtrhtdrgbtykjrsdrtjhefsfvs';//命令
	public static $grepStr="hytrkjtrhbtrmtuyrtbrhbrt";//grep关键字，得去除引号和双引号
	public static $logFile='/temp/xxxxxxxxxxx.txt';//日志文件路径
	public static $debugArg="debug"; //调试参数
	public static $debugGrep="debug";//grep调试关键字，得去除引号和双引号
	
	public static function init($script,$debug){
		if(!$script){
			return;
		}
		if($debug){
			$debugp = $debug;
			$debugp = str_ireplace('"','',$debugp);
			$debugp = str_ireplace("'",'',$debugp);
			if($debugp){
				self::$debugArg = $debug;
				self::$debugGrep = $debugp;
			}
		}
		$pagepath = realpath(__DIR__.'/../../');
		$script = trim($script);
		if(strstr($script,' ./')){
			$script = str_replace(' ./',' '.$pagepath.'/app/',$script);
		}
		if(strstr($script,' sys/')){
			$script = str_replace(' sys/',' '.$pagepath.'/appsys/',$script);
		}
		if(strstr($script,' just/')){
			$script = str_replace(' just/',' '.$pagepath.'/just/',$script);
		}
		
		self::$cmdScript = $script;
		$grepStr = $script;
		$grepStr = str_ireplace('"','',$grepStr);
		$grepStr = str_ireplace("'",'',$grepStr);
		self::$grepStr = $grepStr;
		
		$debugStr = $script;
		$debugStr = str_ireplace('"','',$debugStr);
		$debugStr = str_ireplace("'",'',$debugStr);
		$debugStr = str_ireplace(" ",'_',$debugStr);
		$debugStr = str_ireplace("/",'_',$debugStr);
		
		self::$logFile = '/temp/'.$debugStr.'.txt';
	}
		
	public static function status(){//0关闭 1 开启
		$grepStr = self::$grepStr;
		$sta = exec("ps -ef |grep '".$grepStr."'|grep -v grep");
		return $sta;
	}
	
	public static function statusDebug(){
		$grepStr = self::$grepStr;
		$debugGrep = self::$debugGrep;
		$sta = exec("ps -ef |grep '".$grepStr."'|grep ' ".$debugGrep."'|grep -v grep");
		return $sta;
	}
	
	public static function start(){//返回空表示成功，否则返回错误信息
		$script = self::$cmdScript;
		$logFile = self::$logFile;
		shell_exec('sudo nohup '.$script.' > '.$logFile.' 2>&1 &');
		sleep(1);
		$res=self::status();
		if(!$res){
			$merrmsg = file_get_contents($logFile);
			return '启动服务失败: '.$merrmsg;
		}
		return ;
	}
	
	public static function stop(){
		$grepStr = self::$grepStr;
		self::killAllPids($grepStr);
		sleep(1);
		$res=self::status();
		if($res){
			return '停止服务失败';
		}
		return ;
	}
	
	public static function startDebug(){
		$script = self::$cmdScript;
		$logFile = self::$logFile;
		$debugArg = self::$debugArg;
		shell_exec('sudo nohup '.$script.' '.$debugArg.' > '.$logFile.' 2>&1 &');
		sleep(1);
		$res=self::status();
		if(!$res){
			$merrmsg = file_get_contents($logFile);
			return '启动服务失败: '.$merrmsg;
		}
		return ;
	}
	
	public static function stopDebug(){
		return self::stop();
	}
	
	
	public static function debugInfo(){
		$logFile = self::$logFile;
		$debug = file_get_contents($logFile);
		$debug = str_replace('<','《',str_replace('>','》',$debug));
		$debug = nl2br($debug);
		return $debug;
	}
	
	
	public static function getLogFile(){
		return self::$logFile;
	}
}

?>
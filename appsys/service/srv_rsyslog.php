<?php

namespace appsys\service;

class srv_rsyslog extends srv_{
		
	public static function status(){//0关闭 1 开启
		$sta = exec("ps -ef |grep 'rsyslogd'|grep -v grep");
		return $sta;
	}
	
	public static function statusDebug(){
		return ;
	}
	
	public static function start(){//返回空表示成功，否则返回错误信息
		exec('sudo systemctl start rsyslog 2>&1 ',$ress,$code);
		if($code){
			return "code:".$code." .msg:".join(',',$ress);
		}
		return ;
	}
	
	public static function stop(){
		/* rsyslog 杀进程杀不掉，杀了又起
		exec("ps -ef |grep 'rsyslogd'|grep -v grep|awk '{print $2}'",$res);
		for($i=count($res)-1;$i>=0;$i--){//从后往前删除
			$pid = $res[$i];
			$res = exec('sudo kill -9 '.$pid.' 2>&1');
			if($res){
				return $res;
			}
		}
		*/
		exec('sudo systemctl stop rsyslog 2>&1',$ress,$code);
		if($code){
			return "code:".$code." .msg:".join(',',$ress);
		}
		return ;
	}
	
	public static function startDebug(){
		return ;
	}
	
	public static function stopDebug(){
		return;
	}
	
	
	public static function debugInfo(){
		
		return '';
	}
	
	public static function getLogFile(){
		return '';
	}
}

?>
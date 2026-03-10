<?php

namespace appsys\service;

class srv_tacacs extends srv_{
		
	public static function status(){//0关闭 1 开启
		$sta = exec("ps -ef |grep 'sdtacacs'|grep -v grep");
		return $sta;
	}
	
	public static function statusDebug(){
		$sta = exec("ps -ef |grep 'sdtacacs'|grep 'debug'|grep -v grep");
		return $sta;
	}
	
	public static function start(){//返回空表示成功，否则返回错误信息
		$res=exec('sudo /var/www/vue/php/aht/AuthFor/sdtacacs -key xxxx -pro /var/www/vue/php/aht/AuthFor/tacexec.php >/dev/null 2>&1 &');
		return $res;
	}
	
	public static function stop(){
		exec("ps -ef |grep 'sdtacacs'|grep -v grep|awk '{print $2}'",$res);
		for($i=count($res)-1;$i>=0;$i--){//从后往前删除
			$pid = $res[$i];
			exec('sudo kill -9 '.$pid);
		}
		return ;
	}
	
	public static function startDebug(){
		exec('sudo /var/www/vue/php/aht/AuthFor/sdtacacs -key xxxx -pro /var/www/vue/php/aht/AuthFor/tacexec.php  -debug 1 > /temp/sdsrvdebug_sdtacacs 2>&1 &');
		return ;
	}
	
	public static function stopDebug(){
		exec("ps -ef |grep 'sdtacacs'|grep -v grep|awk '{print $2}'",$res);
		for($i=count($res)-1;$i>=0;$i--){//从后往前删除
			$pid = $res[$i];
			exec('sudo kill -9 '.$pid);
		}
		exec("echo '' > /temp/sdsrvdebug_sdtacacs");
		return ;
	}
	
	
	public static function debugInfo(){
		$debug = file_get_contents("/temp/sdsrvdebug_sdtacacs");
		$debug = str_replace('<','《',str_replace('>','》',$debug));
		$debug = nl2br($debug);
		
		return $debug;
		
	}
}

?>
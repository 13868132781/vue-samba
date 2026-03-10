<?php

namespace appsys\service;

class srv_aaanotify extends srv_{
	
	public static function status(){//0关闭 1 开启
		
		return 0;
	}
	
	public static function statusDebug(){
		
		return 0;
	}
	
	public static function start(){//返回空表示成功，否则返回错误信息
		
		return '';
	}
	
	public static function stop(){
		
		return '';
	}
	
	public static function startDebug(){
		
		return '';
	}
	
	public static function stopDebug(){
		
		return '';
	}
	
	
	public static function debugInfo(){
		
		return 'zzzzzzzzzzzzcdsvaasdvsd';
	}
}

?>
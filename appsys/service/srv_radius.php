<?php

namespace appsys\service;

class srv_radius extends srv_{
	
	public static function status(){
		$sta = exec("ps -ef |grep 'freeradius'|grep -v grep");
		return $sta;
	}
	
	public static function statusDebug(){
		$sta = exec("ps -ef |grep 'freeradius'|grep 'X'|grep -v grep");
		return $sta;
	}
	
	public static function start(){//返回错误信息
		exec('sudo /usr/sbin/freeradius >/dev/null 2>&1 ');
		return ;
	}
	
	public static function stop(){//返回错误信息
		exec("ps -ef |grep 'freeradius'|grep -v grep|awk '{print $2}'",$res);
		for($i=count($res)-1;$i>=0;$i--){//从后往前删除
			$pid = $res[$i];
			exec('sudo kill -9 '.$pid);
		}
		return ;
	}
	
	public static function startDebug(){//返回错误信息
		exec('sudo /usr/sbin/freeradius -X > /temp/sdsrvdebug_radius 2>&1 &');
		
		return ;
	}
	
	public static function stopDebug(){//返回错误信息
		exec("ps -ef |grep 'freeradius'|grep -v grep|awk '{print $2}'",$res);
		for($i=count($res)-1;$i>=0;$i--){//从后往前删除
			$pid = $res[$i];
			exec('sudo kill -9 '.$pid);
		}
		exec("echo '' > /temp/sdsrvdebug_radius");
		return ;
	}
	
	
	public static function debugInfo(){
		$debug = file_get_contents("/temp/sdsrvdebug_radius");
		$debug = str_replace('<','《',str_replace('>','》',$debug));
		$debug = nl2br($debug);
		
		if(strpos($debug,"Ready to process requests")){
		   $debug=strstr($debug,"Ready to process requests");
		}
		
		$debug=preg_replace('/Received Access-Request Id .*? from (.+?):/',"<p></p><h6 style='display: inline;color:#00f'>收到认证请求,来自: </h6><h6 style='display: inline;color:#00f'>$1</h6> <h6 style='display: inline;color:#00f'>$2</h6> port ",$debug); 
		$debug=preg_replace('/authorize \{/',"<h6 style='display: inline;color:#00f'>开始预处理</h6>",$debug);
		$debug=preg_replace('/Received Accounting-Request/',"<h6 style='display: inline;color:#00f'>收到记账包</h6>",$debug);
		$debug=preg_replace('/Finished request/',"<h6 style='display: inline;color:#00f'>完成该次请求</h6>",$debug);
		
		$debug=preg_replace('/Sent (?!Access-Reject|Access-Accept)(.+?) of id/',"<h6 style='display: inline;color:#0f0'>返回$1</h6> of id",$debug);
		
		$debug=preg_replace('/Sent (Access-Accept) Id/',"<h6 style='display: inline;color:#00f'>返回$1</h6> of id",$debug);
		
		$debug=preg_replace('/Sent (Access-Reject) Id/',"<h6 style='display: inline;color:#f00'>返回$1</h6> of id",$debug);

		$debug=preg_replace('/hlcotp: ERROR/',"<h6 style='display: inline;color:#f00'>动态口令验证失败</h6>",$debug);
		//OTPPAP = reject
		
		return $debug;
	}
}

?>
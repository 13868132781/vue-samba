<?php 
ini_set("display_errors","on");//总开关
ini_set("error_reporting",E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
//ini_set("error_reporting",E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING);

set_time_limit(0);
?>
<?php require_once(dirname(__FILE__).'/../fun/mysqli.php'); ?>
<?php
//echo  __FILE__.; 
//当被别的文件include或require时，获取的是本head文件,且是系统绝对目录
//而且web和cli得到结果一样，如/var/www/html1/include/phpht/head.php

//echo $_SERVER["PHP_SELF"];当被别的文件include或require时，获取的是那个文件，
//对于web，获得的是站点绝对路径，如 /aaa_main/default.php
//对于cli，执行"php aaa/bbb.php",获得的就是aaa/bbb.php

//$_SERVER["SCRIPT_FILENAME"];得到的文件，也是直接执行的文件
//意思是脚本名，其实就是php.exe 后面跟的路径加文件
//对于web，是系统绝对路径，如 /var/www/html1/sso_main/default.php
//对于cli，执行"php aaa/bbb.php",获得的就是aaa/bbb.php

//$_SERVER["SCRIPT_NAME"]; 和$_SERVER["PHP_SELF"]相同
function check_one(){
	global $_SERVER;
	$this_file_name=basename($_SERVER["PHP_SELF"]);
	$result=array();
	$thispid=getmypid();
	$pspid=array();
	exec("ps -ef | grep '".$this_file_name."' | grep -v 'grep' | awk '{print $2\",\"$3}'",$result);
	//判断是否是子进程
	foreach($result as $val){
		$vals=explode(',',$val);
		$pspid[$vals[0]]=$vals[1];
	}
	if($pspid[$pspid[$thispid]]!='') return;//这是个子进程
	//判断是否是子进程结束
	//echo "result:".count($result)."  \n";
	if(count($result)> 1){
		echo $this_file_name." alrady running for ".(count($result)-1)." times! \n";
		exit;
	}

}
#function sdmysql($p=true){ 
#	if($p==true){
#		$hlcphp_mysql_lj = mysql_pconnect("localhost","root","jbgsn!2716888") or trigger_error(mysql_error(),E_USER_ERROR); 
#	}else{
#		$hlcphp_mysql_lj = mysql_connect("localhost","root","jbgsn!2716888",true) or trigger_error(mysql_error(),E_USER_ERROR); 
#	}
#	mysql_query("SET NAMES 'utf8'");
#	return $hlcphp_mysql_lj;
#}
#$mysite=sdmysql();

function tableexist($db,$table){
	global $mysite;
	$exist_obj=mysql_query("select TABLE_NAME from INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA='".$db."' and TABLE_NAME='".$table."'", $mysite) or die(mysql_error());
	return mysql_num_rows($exist_obj);
}

require_once(dirname(__FILE__).'/../fun/envir.php');
require_once(dirname(__FILE__).'/getoutip.php');
require_once(dirname(__FILE__).'/sdexpect.php');
require_once(dirname(__FILE__).'/hostoper.php');
require_once(dirname(__FILE__).'/auto.php');
require_once(dirname(__FILE__).'/../fun/config.php');
require_once(dirname(__FILE__).'/sdthread.php');

//二、读取cookie或session
if(intval($_SERVER['SERVER_PORT'])!=''){
$_HLCPHP['global']=unserialize($_COOKIE[$_SERVER['SERVER_PORT']]);
}
?>

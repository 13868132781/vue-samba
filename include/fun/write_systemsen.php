<?php

//检测流程是
//1 在login 页面调用 create_systemsen， 创建会话
//2 在api.php 里调用 action_systemsen更新人员活动的时间btime
//3 在btm.php 里定时ajax，调用 alive_systemsen，更新页面存在时间ctime
//4 在aaa_sysuser 里，查看之前，调用close_systemsens关闭所有该关闭的会话
//而每次页面刷新时，api.php 都要调用check_systemsen,如果会话超时，就退出
//在btm.php里也会检测是否超时，若是超时，就刷新自己，以触发api.php里的退出动作

#函数
function create_systemsen($res,$user,$keep,$mac='unknow'){
	global $mysite,$_HLCPHP,$_SERVER,$gettype,$getsysname,$_HLCPHP;
	$ss_user=$user;
	$ss_clip=get_client_ip();
	$ss_port=$_SERVER['SERVER_PORT'];
	
	$sql="insert into zlog_system.sys_sen(ss_res,ss_atime,ss_btime,ss_ctime,ss_user,ss_clip,ss_port,ss_status,ss_sysname,ss_webtype,ss_keep,ss_mac) values('".$res."',now(),now(),now(),'".$ss_user."','".$ss_clip."','".$ss_port."','".$res."','".$_HLCPHP["name"]."','".$_HLCPHP["type"]."','".$keep."','".$mac."')";
	
	mysql_query($sql, $mysite) or die(mysql_error());
	
	return mysql_insert_id($mysite);
}

//跟新最后一次页面被人工活动触发的时间
function action_systemsen($sid){
	global $mysite,$_HLCPHP,$_SERVER;
	$sql="update zlog_system.sys_sen set ss_btime=now() where ssid='".$sid."'";
	
	mysql_query($sql, $mysite) or die(mysql_error());
	
}

//跟新最后一次由前台浏览器自动报告的时间
function alive_systemsen($sid){
	global $mysite,$_HLCPHP,$_SERVER;
	
	$sql="update zlog_system.sys_sen set ss_ctime=now() where ssid='".$sid."'";
	
	mysql_query($sql, $mysite) or die(mysql_error());
	
}





function check_systemsen($sid){
	global $mysite,$_HLCPHP,$_SERVER;
	$checksen_sql="select * from zlog_system.sys_sen where ssid='".$sid."' and ss_res='1' and ss_status='1' and ADDDATE(ss_ctime,interval 20 second)>now() and ADDDATE(ss_btime,interval ".$_HLCPHP['envir']['timeout']." second)>now()";
	//echo $checksen_sql;  
	$checksen_obj= mysql_query($checksen_sql, $mysite) or die(mysql_error());
	$checksen_row=mysql_fetch_assoc($checksen_obj);
	$checksen_num=mysql_num_rows($checksen_obj);
	return $checksen_num;
}

//这个函数会关闭所有超时会话，以及由sid指定id的会话
function close_systemsens($sid){
	global $mysite,$_HLCPHP,$_SERVER;
	$sid = $sid?:'0';
	$checksen_sql="update zlog_system.sys_sen set ss_status='0' where (ADDDATE(ss_ctime,interval 20 second) < now() and ss_status='1') or (ADDDATE(ss_btime,interval ".$_HLCPHP['envir']['timeout']." second) < now() and ss_status='1') or ssid='".$sid."'";
	//print_r("<pre>".$checksen_sql."</pre>");
	$checksen_obj= mysql_query($checksen_sql,$mysite) or die(mysql_error());
	//print_r("<pre>".$checksen_obj."</pre>");
       // $checksen_row=mysql_fetch_assoc($checksen_obj);
	//$checksen_num=mysql_num_rows($checksen_obj);
	return $checksen_num;
	
}

function keep_systemsen($mac){
	global $mysite,$_HLCPHP,$_SERVER;
	$checksen_sql="SELECT * FROM zlog_system.sys_sen where ss_res='1' and ss_mac='".$mac."' and ss_clip='".get_client_ip()."' and ss_port='".$_SERVER['SERVER_PORT']."' order by ssid desc limit 0,1";
	$checksen_obj= mysql_query($checksen_sql, $mysite) or die(mysql_error());
	$checksen_row=mysql_fetch_assoc($checksen_obj);
	$checksen_num=mysql_num_rows($checksen_obj);
	
	return $checksen_row;

}
?>

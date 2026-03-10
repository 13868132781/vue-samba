<?php

$_HLCPHP['lockwhy'][1]="手动锁定";
$_HLCPHP['lockwhy'][2]="密码已经过期";
$_HLCPHP['lockwhy'][3]="连续登录失败";
$_HLCPHP['lockwhy'][4]="临时次数用完";
$_HLCPHP['lockwhy'][5]="限定天数用完";
$_HLCPHP['lockwhy'][6]="处于初始状态";
$_HLCPHP['lockwhy'][7]="时间范围之外";

$_HLCPHP['usersafe']="_HLCFUNC_USER_SAFE";
$_HLCPHP['usersafecheck']="_HLCFUNC_USER_SAFE_CHECK";
$_HLCPHP['usersafelock']="_HLCFUNC_USER_SAFE_LOCK";

function _HLCFUNC_USER_SAFE(){   
	global $mysite,$_HLCPHP;
	$query_get_class = "select * from system.abc_usersafe order by bl_order";
    $temp_class= mysql_query($query_get_class, $mysite) or die(mysql_error());
    $row_class = mysql_fetch_assoc($temp_class);
    $totalRows_class = mysql_num_rows($temp_class); 
	$lists=array();
	if ($totalRows_class !=0){
	   do{
			$lists['keyv'][$row_class['blid']]=$row_class['bl_name'];
			$lists['maps'][$row_class['blid']]=$row_class;
			$lists['list'][]=$row_class;
	   }while($row_class = mysql_fetch_assoc($temp_class));
	}
	return $lists;
}


function _HLCFUNC_USER_SAFE_CHECK($db,$id,$sql){ 
	global $mysite,$_HLCPHP;

	$limit_list=$_HLCPHP['usersafe']();
	$limit_sql=$sql; 
	$limit_obj=mysql_query($limit_sql, $mysite) or die(mysql_error());
	while($limit_row=mysql_fetch_assoc($limit_obj)){
		$listone=$limit_list['maps'][$limit_row['safe_id']];
		if($listone=='') continue;
		if($listone['bl_fails']!='0' and $limit_row['safe_fails']>=$listone['bl_fails']){
			mysql_query("update ".$db." set safe_status='3' where ".$id."='".$limit_row[$id]."' ", $mysite) or die(mysql_error());
			if($listone['bl_fdeal']!='' and $listone['bl_fdeal']!='0' and $limit_row['safe_status']=='3'){
				mysql_query("update ".$db." set safe_status='0',safe_fails='0' where ADDDATE(safe_failstime,interval ".$listone['bl_fdeal']." minute)< now() and ".$id."='".$limit_row[$id]."' ", $mysite) or die(mysql_error());
			}
		}
		if($listone['bl_login']!='0' and $limit_row['safe_login']>=$listone['bl_login']){
			mysql_query("update ".$db." set safe_status='4' where ".$id."='".$limit_row[$id]."' ", $mysite) or die(mysql_error());
		}
		if($listone['bl_pass']!='0'){
			mysql_query("update ".$db." set safe_status='2' where ADDDATE(safe_passtime,interval ".$listone['bl_pass']." day)< now() and ".$id."='".$limit_row[$id]."' ", $mysite) or die(mysql_error());
		}
		if($listone['bl_day']!='0'){
			mysql_query("update ".$db." set safe_status='5' where ADDDATE(safe_daytime,interval ".$listone['bl_day']." day)< now() and ".$id."='".$limit_row[$id]."' ", $mysite) or die(mysql_error());
		}
		if($listone['bl_range']!=''){
			$ranges=explode("~",$listone['bl_range']);
			if(!$ranges[1]) $ranges[1]=$ranges[0];
			if($ranges[0]) $ranges[0].=" 00:00:00";
			if($ranges[1]) $ranges[1].=" 23:59:59";
			mysql_query("update ".$db." set safe_status='7' where (now()<'".$ranges[0]."' or  now() > '".$ranges[1]."') and ".$id."='".$limit_row[$id]."' ", $mysite) or die(mysql_error());
			if($limit_row['safe_status']=='7'){
				mysql_query("update ".$db." set safe_status='0' where (now()>'".$ranges[0]."' and  now() < '".$ranges[1]."') and ".$id."='".$limit_row[$id]."' ", $mysite) or die(mysql_error());
			}
		}elseif($limit_row['safe_status']=='7'){
			mysql_query("update ".$db." set safe_status='0' where ".$id."='".$limit_row[$id]."' ", $mysite) or die(mysql_error());
			
		}
		
	}
}


function _HLCFUNC_USER_SAFE_LOCK($db,$id,$lock,$unlock){ 
	global $mysite,$_HLCPHP;
	
	if ($lock != "") {
		mysql_query("update ".$db." set safe_status='1' where ".$id."='".$lock."'", $mysite) or die(mysql_error());
	}

	if ($unlock != "") {
		mysql_query("update ".$db." set safe_status='0',safe_passtime=now(),safe_daytime=now(),safe_login='0',safe_fails='0' where ".$id."='".$unlock."'", $mysite) or die(mysql_error());
	}


}

?>
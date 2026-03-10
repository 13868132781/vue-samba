<?php
    function write_systemlog($db,$act,$objtype,$objname,$type,$mark){
		global $_HLCPHP;
	   $sql="insert into zlog_system.sys_log(sl_suser,sl_time,sl_cip,sl_act,sl_objtype,sl_objname,sl_type,sl_mark) values('".$_HLCPHP['global']['user']."',now(),'".$_HLCPHP['global']['client']."','".$act."','".$objtype."','".$objname."','".$_HLCPHP["type"]."','".$mark."')";
	
	
		mysql_query($sql, $db) or die(mysql_error());
	
	}
	
	function write_systemlogs($a){
		global $_HLCPHP;
	   $sql="insert into zlog_system.sys_log(sl_suser,sl_time,sl_cip,sl_act,sl_objtype,sl_objname,sl_type,sl_mark) values('".$_HLCPHP['global']['user']."',now(),'".$_HLCPHP['global']['client']."','".$a['act']."','".$a['objtype']."','".$a['objname']."','".$a['type']."','".$a['mark']."')";
	
	
		mysql_query($sql, $a['db']) or die(mysql_error());
	
	}
?>
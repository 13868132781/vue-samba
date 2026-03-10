<?php

	mysql_select_db($database_syslog, $mysite);
    $query_get_value = "select * from syslog.aaa_facty";
    $temp_value= mysql_query($query_get_value, $mysite) or die(mysql_error());
    $row_value = mysql_fetch_assoc($temp_value);
    $totalRows_value = mysql_num_rows($temp_value); 
	$facty_list=array();
	$res_facty=array();
	$t=0;
	if ($totalRows_value !=0){
	   do{
	      $facty_list[$row_value['code']]=$row_value['mark'];
	      $res_facty[$t]['id']=$row_value['code'];
		  $res_facty[$t]['name']=$row_value['mark'];
		  $t++;
	   }while($row_value = mysql_fetch_assoc($temp_value));
	}
	$_facty_list=json_encode($facty_list);
	$_res_facty=json_encode($res_facty);
	
	
	
	mysql_select_db($database_syslog, $mysite);
    $query_get_value = "select * from syslog.aaa_level";
    $temp_value= mysql_query($query_get_value, $mysite) or die(mysql_error());
    $row_value = mysql_fetch_assoc($temp_value);
    $totalRows_value = mysql_num_rows($temp_value); 
	$level_list=array();
	$res_level=array();
	$t=0;
	if ($totalRows_value !=0){
	   do{
	      $level_list[$row_value['code']]=$row_value['mark'];
	      $res_level[$t]['id']=$row_value['code'];
		  $res_level[$t]['name']=$row_value['mark'];
		  $t++;
	   }while($row_value = mysql_fetch_assoc($temp_value));
	}
	$_level_list=json_encode($level_list);
	$_res_level=json_encode($res_level);
	
	
	echo "<script language=\"JavaScript\" type=\"text/JavaScript\"> " ;  
	echo "var facty_list = $_facty_list; ";  
    echo "var res_facty = $_res_facty; ";
	
	echo "var level_list = $_level_list; ";  
    echo "var res_level = $_res_level; ";
    echo "</script>" ;
?>
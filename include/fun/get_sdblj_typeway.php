<?php

//      |-----类型
//资源--|-----版本
//      |-----连接-----工具

//一个资源对应一个版本， 一个资源对应多个连接， 一个连接对应多个工具， 若果一个连接要根据资源来选工具的话， 就要拆分连接， 如sqlserver， 要根据资源的版本来选客户端工具，就要拆分sqlserver连接为sqlserver2000，sqlserver2005，sqlserver2008
 
//资源类型




//不同大类完全隔开(h\d\w\f)


//type-------conn--------tool  (上包含下) 一个连接所提供的工具应该都能被包含它的type使用，如果，不能，就要划分
// |
// |_________vers(下隶属于上)


?>
<?php

	$blj_head_tag['h']="主机";
	$blj_head_tag['d']="数据库";
	$blj_head_tag['w']="web应用";
	$blj_head_tag['f']="ftp/sftp";
	$blj_head_tag['n']="网络设备";



//设备类型
    mysql_select_db($database_sdblj, $mysite);
    $query_get_value = "(SELECT * FROM sdblj.aaa_bt_type) union (select class_id as ttid, 'n' as tt_head,Concat('5',class_id) as tt_num,class_name as tt_name, ',s1,2,3,' as tt_ways,null as tt_versns from radius.aaa_nas_class)";
    $temp_value= mysql_query($query_get_value, $mysite) or die(mysql_error());
    $row_value = mysql_fetch_assoc($temp_value);
    $totalRows_value = mysql_num_rows($temp_value); 
	$blj_tt_lst=array();
	if ($totalRows_value !=0){
	   do{
	      $blj_tt_lst[$row_value['tt_head']][$row_value['tt_num']]=$row_value['tt_name'];
		  $blj_type_ways_list[$row_value['tt_num']]=$row_value['tt_ways'];
	   }while($row_value = mysql_fetch_assoc($temp_value));
	}
	
	
//连接协议	
	mysql_select_db($database_sdblj, $mysite);
    $query_get_value = "select * from sdblj.aaa_bt_ways order by tw_order";
    $temp_value= mysql_query($query_get_value, $mysite) or die(mysql_error());
    $row_value = mysql_fetch_assoc($temp_value);
    $totalRows_value = mysql_num_rows($temp_value); 
	$blj_tc_lst=array();
	$blj_tc_tol=array();
	$blj_tc_pot=array();
	$blj_ways_list=array();
	$blj_ways_arrs=array();
	if ($totalRows_value !=0){
	   do{ 
		   $wayheads=explode('_',$row_value['tw_head']);
		   foreach($wayheads as $wayheadval){
			  $blj_tc_lst[$wayheadval][$row_value['twid']]=$row_value['tw_name'];
			  $blj_tc_pot[$wayheadval][$row_value['twid']]=$row_value['tw_port']; 
		   }
			$blj_ways_list[]=$row_value;
			$blj_ways_arrs[$row_value['twid']]=$row_value;
			
	   }while($row_value = mysql_fetch_assoc($temp_value));
	}



?>
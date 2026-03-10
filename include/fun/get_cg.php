<?php
    mysql_select_db($database_mysite, $mysite);
    $query_get_value = "select * from radius.g_cs_group order by cg_oid";
    $temp_value= mysql_query($query_get_value, $mysite) or die(mysql_error());
    $row_value = mysql_fetch_assoc($temp_value);
    $totalRows_value = mysql_num_rows($temp_value); 
	$cg_list=array();
	$res_cg=array();
	$t=0;
	if ($totalRows_value !=0){
	   do{
	      $cg_list[$row_value['cg_id']]=$row_value['cg_name'];
	      $res_cg[$t]['id']=$row_value['cg_id'];
		  $res_cg[$t]['name']=$row_value['cg_name'];
		  $res_cg[$t]['enable']=$row_value['cg_enable'];
		  $res_cg[$t]['static']=$row_value['cg_static'];
		  $t++;
	   }while($row_value = mysql_fetch_assoc($temp_value));
	}
	if($get_ag_cg_php_without_js==''){
		$_cg_list=json_encode($cg_list);
		$_res_cg=json_encode($res_cg);
		echo "<script language=\"JavaScript\" type=\"text/JavaScript\"> " ;  
		echo "var cg_list = $_cg_list; ";  
		echo "var res_cg = $_res_cg; ";   
		echo "</script>" ;
	}
?>
<?php
    mysql_select_db($database_mysite, $mysite);
    $query_get_value = "select * from radius.g_attr_group";
    $temp_value= mysql_query($query_get_value, $mysite) or die(mysql_error());
    $row_value = mysql_fetch_assoc($temp_value);
    $totalRows_value = mysql_num_rows($temp_value); 
	$ag_list=array();
	$res_ag=array();
	$t=0;
	if ($totalRows_value !=0){
	   do{
	      $ag_list[$row_value['ag_id']]=$row_value['ag_name'];
	      $res_ag[$t]['id']=$row_value['ag_id'];
		  $res_ag[$t]['name']=$row_value['ag_name'];
		  $res_ag[$t]['enable']=$row_value['ag_enable'];
		  $res_ag[$t]['static']=$row_value['ag_static'];
		  $t++;
	   }while($row_value = mysql_fetch_assoc($temp_value));
	}
	if($get_ag_cg_php_without_js==''){
		$_ag_list=json_encode($ag_list);
		$_res_ag=json_encode($res_ag);
		echo "<script language=\"JavaScript\" type=\"text/JavaScript\"> " ;  
		echo "var ag_list = $_ag_list; ";  
		echo "var res_ag = $_res_ag; ";   
		echo "</script>" ;
	}
?>
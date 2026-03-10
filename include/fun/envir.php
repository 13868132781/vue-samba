<?php
error_reporting(0);
$query_get_value = "select * from system.aaa_envir where en_web='all' or en_web like'%".$_HLCPHP["type"]."%'";
$temp_value= mysql_query($query_get_value, $mysite) or die(mysql_error());
$row_value = mysql_fetch_assoc($temp_value);
$totalRows_value = mysql_num_rows($temp_value);
if ($totalRows_value !=0){
	do{
		$_HLCPHP["envir"][$row_value['en_key']]=$row_value['en_val'];
	}while($row_value = mysql_fetch_assoc($temp_value));
}
?>
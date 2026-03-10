<?php

function sdmysql($p=true){
	if($p==true){
		$hlcphp_mysql_lj = mysql_pconnect("localhost","root","jbgsn!2716888") or trigger_error(mysql_error(),E_USER_ERROR); 
	}else{
		$hlcphp_mysql_lj = mysql_connect("localhost","root","jbgsn!2716888") or trigger_error(mysql_error(),E_USER_ERROR); 
	}
	mysql_query("SET NAMES 'utf8'");
	return $hlcphp_mysql_lj;
}
$mysite=sdmysql();

?>
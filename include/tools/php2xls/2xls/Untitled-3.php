<?php
function zlog_radius.rad_logn($reply){
     if ($row_rs_prod['reply'] == '')  
	 echo '&nbsp;' ;
	 else if ($row_Recordset1['reply'] == 'Access-Reject') 
	 echo '验证失败'; 
	 else echo '验证成功';
	 return;
}	 
?>
<?php zlog_radius.rad_logn($row_rs_prod['reply']);
?>
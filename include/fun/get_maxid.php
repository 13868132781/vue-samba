<?php
    function get_max_id($db,$oid){
	   global $mysite;
	   $query_Recordset1="select max(".$oid.") as oid from ".$db;
	  // echo $query_Recordset1;
       $Recordset1 = mysql_query($query_Recordset1, $mysite) or die(mysql_error());
       $row_Recordset1 = mysql_fetch_assoc($Recordset1);
       $totalRows_Recordset1 = mysql_num_rows($Recordset1);
       if ($totalRows_Recordset1!=0 && $row_Recordset1['oid']!=""){
          $new_oid=$row_Recordset1['oid']+1;
       }else{
          $new_oid=1;
	   }
	   return $new_oid;
	}
$_HLCPHP['maxid']="get_max_id";
?>
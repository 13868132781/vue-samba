<?php require_once(dirname(__FILE__).'/../fun/api.php'); ?>
<?php

set_time_limit(180000);

$exportdata=json_decode($_POST['exportdata'],true);


mysql_select_db($database_mysite, $mysite);
$query_rs_prod = stripslashes($exportdata['sql']); 
$rs_prod = mysql_query($query_rs_prod, $mysite) or die(mysql_error()); 
$row_rs_prod = mysql_fetch_assoc($rs_prod); 
$totalRows_rs_prod = mysql_num_rows($rs_prod);



$fname = tempnam("/tmp", $exportdata['name'].".csv");

$file = fopen($fname, 'ab'); 

foreach( $exportdata['fields'] as $fields){
	$thisval = iconv("utf8","gb2312",$fields['name']);
	$thisval = str_replace('"','\"',$thisval);
	$thisval = str_replace('\r','\\r',$thisval);
	$thisval = str_replace('\n','\\n',$thisval);
	fwrite($file, '"'.$thisval.'",' );
} 
fwrite($file, "\r\n");

if($totalRows_rs_prod!=0){    
   do{
		foreach( $exportdata['fields'] as $keys => $fields){  	
			if($fields['value'][$row_rs_prod[$keys]]['name']!='')
				$thisval=$fields['value'][$row_rs_prod[$keys]]['name'];
			else
				$thisval=$row_rs_prod[$keys];
			
			$thisval = iconv("utf8","gb2312",$thisval);
			$thisval = str_replace('"','""',$thisval);
			//$thisval = str_replace("\r",'\r',$thisval);
			//$thisval = str_replace("\n",'\n',$thisval);
			fwrite($file, '"'.$thisval.'",' );
		}
		fwrite($file, "\r\n");
   } while ($row_rs_prod = mysql_fetch_assoc($rs_prod)); 
}
fclose($file);


header("Accept-Ranges:bytes");
header("Content-Type: application/x-msexcel; name=\"".$exportdata['name'].".xls\"");
header("Content-Disposition: inline; filename=\"".$exportdata['name'].".csv\"");
$fh=fopen($fname, "rb");
fpassthru($fh);
unlink($fname);




?>
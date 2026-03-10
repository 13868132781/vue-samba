<?php require_once(dirname(__FILE__).'/../fun/api.php'); ?>
<?php require_once(dirname(__FILE__)."/php2xls/class.writeexcel_workbook.inc.php");?>
<?php require_once(dirname(__FILE__)."/php2xls/class.writeexcel_worksheet.inc.php");?>
<?php


set_time_limit(180000);

$exportdata=json_decode($_POST['exportdata'],true);


mysql_select_db($database_mysite, $mysite);
$query_rs_prod = stripslashes($exportdata['sql']); 
$rs_prod = mysql_query($query_rs_prod, $mysite) or die(mysql_error()); 
$row_rs_prod = mysql_fetch_assoc($rs_prod); 
$totalRows_rs_prod = mysql_num_rows($rs_prod);



$fname = tempnam("/tmp", $exportdata['name'].".xls");
$workbook = new writeexcel_workbook($fname);
$worksheet = $workbook->addworksheet();

# Set the column width for columns 1, 2, 3 and 4
//$worksheet->set_column(0, 9, 20);
$worksheet->set_column(0, 1, 20);
$worksheet->set_column(1, 2, 20);
$worksheet->set_column(2, 3, 20);
$worksheet->set_column(3, 4, 20);
$worksheet->set_column(4, 5, 15);
$worksheet->set_column(5, 6, 15);
$worksheet->set_column(6, 7, 15);

# Create a format for the column headings
$header = $workbook->addformat();
$header->set_bold();
$header->set_size(12);
$header->set_color('blue');

# Write out the data



$i=0;
foreach( $exportdata['fields'] as $fields){  
	 if ($fields['width']!='')
	    $width= $fields['width'];
	 else
	    $width=30;    
	$worksheet->write(0, $i, iconv("utf8","gb2312",$fields['name']),  $header);
    $i++;
} 


$header = $workbook->addformat();
$header->set_size(12);


$q=1;
if($totalRows_rs_prod!=0){    
   do                                                                              
   {	
		$i=0;
		
		$color0=255;$color1=255;$color2=255;
		foreach( $exportdata['color'] as $keyc => $colors){
			if($colors[$row_rs_prod[$keyc]]!=''){
				$newcolor=explode(",",$colors[$row_rs_prod[$keyc]]);
				$color0=(int)$newcolor[0];$color1=(int)$newcolor[1];$color2=(int)$newcolor[2];
			}
		}
		
		
		foreach( $exportdata['fields'] as $keys => $fields){  
			if ($fields['width']!='')
				$width= $fields['width'];
			else
				$width=30;  
			
			
			if($fields['value'][$row_rs_prod[$keys]]['color']!=''){
				$newcolor=explode(",",$fields['value'][$row_rs_prod[$keys]]['color']);
				//$header->set_color($newcolor[0],$newcolor[1],$newcolor[2]);
			}else{
				//$header->set_color($color0,$color1,$color2);
			}

			
			if($fields['value'][$row_rs_prod[$keys]]['name']!='')
				$thisval=$fields['value'][$row_rs_prod[$keys]]['name'];
			else
				$thisval=$row_rs_prod[$keys];
			
			$worksheet->write($q, $i, iconv("utf8","gb2312",$thisval),  $header);
			$i++;
		}
		$q++;
   } while ($row_rs_prod = mysql_fetch_assoc($rs_prod)); 
}





$workbook->close();


header("Accept-Ranges:bytes");
header("Content-Type: application/x-msexcel; name=\"".$exportdata['name'].".xls\"");
header("Content-Disposition: inline; filename=\"".$exportdata['name'].".xls\"");
$fh=fopen($fname, "rb");
fpassthru($fh);
unlink($fname);

?>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
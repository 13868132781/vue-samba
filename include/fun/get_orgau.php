<?php

function getorgaus($ids,$allorgaus){
	global $database_mysite, $mysite;
	
	if($ids==''){
		return $allorgaus;
	}
	$quray="select * from radius.orgau where or_id in (".$ids.")";
	$obj = mysql_query($quray, $mysite) or die(mysql_error());
	$row=mysql_fetch_assoc($obj);
	$count = mysql_num_rows($obj);
	if($count!=''){
		do{
			if(strstr(','.$allorgaus.',',','.$row['or_id'].',')){
				continue;
			}
			if($allorgaus=='')
				$allorgaus=$row['or_id'];
			else
				$allorgaus=$allorgaus.','.$row['or_id'];
				
			if($row['or_ids']!=''){
				$allorgaus=getorgaus($row['or_ids'],$allorgaus);
			}
		
		}while($row=mysql_fetch_assoc($obj));
	}
	return $allorgaus;
}


?>
<?php
$db= mysql_pconnect("localhost","root","jbgsn!2716888") or trigger_error(mysql_error(),E_USER_ERROR); 

//$data_sql = "select * from radius.nas limit 100,200 into outfile '/tmp/test.xls'";
//$data_obj=mysql_query($data_sql, $db) or die(mysql_error());
//$data_num=mysql_num_rows($data_obj);


//echo "========".mysql_affected_rows()."========";


function export_txt($a){
	
	global $_HLCPHP;
	$time=date('YmdHis', time()); 
	$pathpre="/temp/".$time ;
	
	//echo $pathpre;
	exec("sudo rm -rf ".$pathpre);
	
	exec("sudo mkdir -p ".$pathpre);
	exec("sudo chmod 777 ".$pathpre); 
	
	$limit = 10000;
	if($a['limit']!=''){
		$limit=$a['limit'];
	}
	
	$split=',';
	if($a['split']!=''){
		$split=$a['split'];
	}
	
	for($i=0;$i<count($a['data']);$i++){
		$filenum=1;
		$start=0;
		while(true){
			$filename=$pathpre."/".$a['data'][$i]['name']."_".$filenum.".csv";
			$data_sql=$a['data'][$i]['sql']." limit ".$start.", ".$limit." into outfile '".$filename."' FIELDS TERMINATED BY '".$split."' "; 
			$filenum++;
			$data_obj=mysql_query($data_sql, $a['db']) or die(mysql_error());
			$data_num=mysql_affected_rows($a['db']);
			if($data_num==0){
				exec("sudo rm ".$filename);
				break;
			}
			$start=$start+$data_num;
		}
	}
	
	$outfile=$a['file'];
	if($outfile==''){
		$outfile="export";
	}
	$zipfile=$pathpre."/".$outfile."_".$time.".zip";
	
	exec("cd ".$pathpre." && sudo zip -q -r ".$zipfile." *");
	//exec("sudo rm -rf ".$pathpre);

	sddownload($zipfile);

	exec("sudo rm -rf ".$pathpre );
	
}






?>
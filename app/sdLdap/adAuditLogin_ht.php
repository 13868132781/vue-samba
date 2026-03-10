<?php require_once(__DIR__.'/../../just/a.php');?>
<?php

//保留最近1000条
clearDBTable('sdsamba_log.adlogin','id','alid','1000');
		
$fileName = "/var/log/samba/log.samba";
$fileNameTmp = "/temp/sdsambalog";
		
exec('sudo cp '.$fileName.' '.$fileNameTmp.' 2>&1',$res,$code);
if($code){
	echo join('。',$res);
	exit($code);
}
exec("sudo -i chmod 777 /var/log/samba 2>&1",$res,$code);
exec("sudo -i chmod 777 /var/log/samba/log.samba 2>&1",$res,$code);
exec("sudo -i echo 'ddd' > /var/log/samba/log.samba 2>&1",$res,$code);
if($code){
	echo join('。',$res);
	exit($code);
}
//exec("sudo touch ".$fileName." 2>&1",$res,$code);
//if($code){
//	echo join('。',$res);
//	exit($code);
//}
		
$logCount=0;	
$file = fopen($fileNameTmp, "r");
if ($file) {
	while (($line = fgets($file)) !== false) {
		$lineJson = json_decode(trim($line),true);
		if($lineJson===null or !isset($lineJson['Authentication']['authDescription'])){
			continue;
		}
		$Authentication = $lineJson['Authentication'];
		$al_time = str_ireplace('T',' ',explode('.',$lineJson['timestamp'])[0]);
		$type = $lineJson['type'];
		$status = $Authentication['status'];
		$client = $Authentication['remoteAddress'];
		$serviceDescription = $Authentication['serviceDescription'];
		$authDescription = $Authentication['authDescription'];
		$user = $Authentication['clientAccount'];
				
		$clientArr = explode(':',$client);
		if(count($clientArr)==3){
			$client = $clientArr[1];
		}
				
		$userarr = explode('@',str_ireplace("\\",'',$user));
		if(count($userarr)>1){
			$user = $userarr[0].'@'.$userarr[1];
		}
		\DB::table('sdsamba_log.adlogin')
		->insert([
			'al_time'=>$al_time,
			'al_client'=> $client,
			'al_user' => $user,
			'al_status' => $status,
			'al_type'=>$type,
			'al_service'=>$serviceDescription,
			'al_authtype'=>$authDescription,
					
		]);
		$logCount++;				
	}
	fclose($file);
}
exec('sudo rm '.$fileNameTmp.' 2>&1',$res,$code);	
if($code){
	echo join('。',$res);
	exit($code);
}

echo 'add '.$logCount.' log!';

?>
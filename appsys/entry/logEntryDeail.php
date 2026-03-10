<?php require_once(__DIR__.'/../../just/a.php');?>
<?php


$tbName = $sysCfgInfo['sysDB'].".zlog_entry";

$logFile = __DIR__."/logEntryFile.log";
$fp=fopen($logFile,'r');
if(!$fp){
	echo "open file failed \n";
}
$count = 0;
while(($line = fgets($fp))!==false){
	$line = trim($line);
	if(!$line){continue;}
	$count++;
	$data = json_decode($line,true);
	
	\DB::table($tbName)
		->insert([
			'de_time'=>$data['time'],
			'de_router'=>$data['router'],
			'de_post'=>$data['post'],
			'de_take'=>$data['take'],
			'de_say'=>$data['say'],
			'de_sql'=>$data['sql'],
			
		]);
}
exec("echo ''> ".$logFile);
fclose($fp);

echo "get ".$count." log\n";


clearDBTable($tbName,'id','deid','1000');
?>
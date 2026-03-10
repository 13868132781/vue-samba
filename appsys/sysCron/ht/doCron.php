<?php require_once(__DIR__.'/../../../just/a.php');?>
<?php require_once(__DIR__.'/check.php');?>
<?php
set_time_limit(0);


$crid = '';
$hand = 0;//是手动还是调度

if(count($argv)>2){
	$crid = $argv[1];
	$hand = $argv[2];
}else{
	echo "need argv\n";
	exit(1);
}

$msg = checkOne($crid);
if($msg){
	echo $msg;
	exit(1);
}

$cronTable = $sysCfgInfo['sysDB'].'.cron';

$cron_row = \DB::table($cronTable)->where('crid',$crid)->first();

if(!$cron_row){
	echo "no info in mysql\n";
	exit(1);
}

putenv('sd_cron_debug=on');

$pagepath = realpath(__DIR__.'/../../../');
$script = trim($cron_row['cr_script']);
if(strstr($script,' ./')){
	$script = str_replace(' ./',' '.$pagepath.'/app/',$script);
}
if(strstr($script,' sys/')){
	$script = str_replace(' sys/',' '.$pagepath.'/appsys/',$script);
}

$timeout = '12h';
if($cron_row['cr_timeout']!=''){
	$timeout = $cron_row['cr_timeout'];
}
$t1 = microtime(true);
$time_start = date('Y-m-d H:i:s');
exec("timeout -k 10s ".$timeout."  ".$script." 2>&1 ",$res,$code);
$time_stop = date('Y-m-d H:i:s');
$t2 = microtime(true);

$tmdm = round($t2-$t1,3);//如 62.012s
//$tmdm = 3*24*60*60+6*60*60+7*60+34.432;//测试
$realsj = '';
if(floor($tmdm/(24*60*60))>0){
	$realsj .= floor($tmdm/(60*60*24)).'d';
	$tmdm = $tmdm%(60*60*24);
}
if(floor($tmdm/(60*60))>0){
	$realsj .= ' '.floor($tmdm/(60*60)).'h';
	$tmdm = $tmdm%(60*60);
}
if(floor($tmdm/(60))>0){
	$realsj .= ' '.floor($tmdm/(60)).'m';
	$tmdm = $tmdm%(60);
}
if($tmdm>0){
	$realsj .= ' '.$tmdm.'s';
}

if($code==124){//timeout
	$res[]='timeout in '.$realsj;
}else{
	$res[]='finish in '.$realsj;
}

$ress = $script."\n".implode("\n",$res);


\DB::table($cronTable)
	->where('crid',$crid)
	->update([
		"cr_count" => \DB::raw("cr_count+1"),
		"cr_last_time" => $time_start,
		"cr_last_code" => $code,
		"cr_last_log" => $ress,
		"cr_last_uset" => $realsj,
	]);
	
echo $ress;
exit($code);

?>
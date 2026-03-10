<?php
$timestart = microtime(true);
set_time_limit(0);
?>
<?php require_once(__DIR__.'/../../a.php');?>
<?php require_once(__DIR__.'/a.php');?>
<?php require_once(__DIR__.'/AuthFor.php');?>
<?php

$args = disArgs();

if($args['section']=='authen'){
	$obj = new AuthStep($args);
	$obj->radAuthen();
}elseif($args['section']=='author'){
	$obj = new AuthStep($args);
	$obj->radAuthor();
}else{
	echoOut(1,'unknow section: '.$args['section']);
}


//php radexec.php "section=authen||nas=192.168.0.202||user=admin||pass=qqq000,,,||nac=192.168.0.204||state="

//state有值，表示第二步认证

?>
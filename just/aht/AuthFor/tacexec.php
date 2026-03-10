<?php
$timestart = microtime(true);
set_time_limit(0);
?>
<?php require_once(__DIR__.'/../../a.php');?>
<?php require_once(__DIR__.'/a.php');?>
<?php require_once(__DIR__.'/AuthFor.php');?>
<?php




$args = disArgs();


//exec("echo '".date('Y-m-d H:i:s')." ".$args['section']."'>> /temp/tacacstime.txt");

//sleep(10);


if($args['section']=='getkey'){
	$obj = new AuthStep($args);
	$obj->tacGetKey();
}elseif($args['section']=='authen'){
	$obj = new AuthStep($args);
	$obj->tacAuthen();
}elseif($args['section']=='author'){
	$obj = new AuthStep($args);
	$obj->tacAuthor();
}else{
	echoOut(1,'unknow section: '.$args['section']);
}


//php tacexec.php "section=author||nas=192.168.0.202||user=admin||pass=qqq000,,,||nac=192.168.0.204||state=||service=1||args="

//state有值，表示第二步认证
//service=2,表示是enable认证，tacacs用户名密码认证和enable认证是分开的

?>
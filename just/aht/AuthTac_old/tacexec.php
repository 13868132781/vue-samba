<?php require_once(__DIR__.'/../a.php');?>
<?php require_once(__DIR__.'/a.php');?>
<?php require_once(__DIR__.'/getKey.php');?>
<?php require_once(__DIR__.'/authen.php');?>
<?php require_once(__DIR__.'/author.php');?>
<?php require_once(__DIR__.'/acct.php');?>
<?php

$timestart = microtime(true);
set_time_limit(0);


$args = disArgs();

if($args['section']==''){
	echoOut(1,'lack of arg section');
}		

$funcName = "tacSection_".$args['section'];

$funcName($args);


//php tacexec.php "section=getkey||nas=192.168.0.202"

?>
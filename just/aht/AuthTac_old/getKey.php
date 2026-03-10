<?php

function tacSection_getkey($args){
	global $sdmysql;
	$msg = \DB::table("sdaaa.nas")
		->where('na_ip',$args['nas'])
		->value('na_secret');
	echoOut(0,$msg);
}


?>
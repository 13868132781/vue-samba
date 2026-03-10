<?php
function get_client_ip(){
	if(!empty($_SERVER['HTTP_CLIENT_IP'])){
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	}else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
		$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
	}else{
		$ip=$_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}
$_HLCPHP['client']=	"get_client_ip";
	
function get_server_ip(){
	static $serverip = NULL;
	if ($serverip !== NULL){
		return $serverip;
	}
	if (isset($_SERVER)){
		if (isset($_SERVER['SERVER_ADDR'])){
			$serverip = $_SERVER['SERVER_ADDR'];
		}else{
			$serverip = '0.0.0.0';
		}
	}else{
		$serverip = getenv('SERVER_ADDR');
	}
	return $serverip;
}
$_HLCPHP['server']=	"get_server_ip";
?>
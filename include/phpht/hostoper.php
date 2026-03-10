<?php
function hostoper($ip,$send){
	
	set_time_limit(0); 
	$host = $ip;    
	$port = 55555;    
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	if(!$socket){
		$result['code']='1';
		$result['data']='Could not create  socket';
		return $result;
	}  
		 
	$connection = socket_connect($socket, $host, $port);
	if(!$socket){
		$result['code']='1';
		$result['data']='Could not connet server';
		return $result;
	}

	//设置接送和发送数据时的超时时间
	//socket_set_option($socket,SOL_SOCKET,SO_RCVTIMEO,array("sec"=>10, "usec"=>0 ) );
	//socket_set_option($socket,SOL_SOCKET,SO_SNDTIMEO,array("sec"=>10, "usec"=>0 ) );

	socket_write($socket, json_encode($send)) or die("Write failed\n"); 
	$buffs="";
	while($buff = socket_read($socket, 5024, PHP_BINARY_READ)) { 
		if(strstr($buff,'hlcfinishsenddata')){
			$buffs.=str_replace('hlcfinishsenddata','',$buff);
			break;
		}
		$buffs.=$buff;
	}

	$result=json_decode($buffs ,true);


	if($_POST['oper']=='fileget' and $result['code']=='0'){
		$fileget="";
		while($buff = socket_read($socket, 5024, PHP_BINARY_READ)) { 
			if(strstr($buff,'hlcfinishsenddata')){
				$fileget.=str_replace('hlcfinishsenddata','',$buff);
				break;
			}
			$fileget.=$buff;
		}
	}


	if($_POST['oper']=='fileset' and ($result['code']=='0' or $send['force']!='')){
		$fp= fopen($_POST['args'],"r");
		$buffer_size = 1024;
		while($buffer = fread($fp,$buffer_size)){
			socket_write($socket, $buffer) or die("Write failed\n"); 
		}
		socket_write($socket, "hlcfinishsenddata") or die("Write failed\n"); 
	}




	socket_close($socket);  	
		
	return $result;
	
}


?>
<?php
function systemip_get($returnstring){
	$iparray=array();
	$system_ip_string="";
	$inter=0;	
	$t=0;			
	$file_handle = fopen("/etc/network/interfaces", "r");
	while (!feof($file_handle)) {
	   $line = fgets($file_handle);
	   if (stristr($line,"auto")&&!stristr($line,"lo")){
		  if(stristr($line,":")){
			 $t++;
		  }else{
			 $inter=trim(substr(stristr($line,"eth"),3));
			 $t=0;
		  }
	   }else if(stristr($line,"address")){
		   $iparray[$inter][$t]['address']=trim(substr($line,8),"\n");
		   if($system_ip_string=="")
			  $system_ip_string="'".trim(substr($line,8),"\n")."'";
		   else
			  $system_ip_string=$system_ip_string.",'".trim(substr($line,8),"\n")."'";
	   }else if(stristr($line,"netmask")){
		   $iparray[$inter][$t]['netmask']=trim(substr($line,8),"\n");
	   }else if(stristr($line,"gateway")){
		   $iparray[$inter][$t]['gateway']=trim(substr($line,8),"\n");
	   }
	}

	fclose($file_handle);
	if($returnstring!=''){
			return $system_ip_string;
	}else{
		return $iparray;
	}
}



function ifconfig_read_vfgvfdergsfvrfverevferbrr(){
	exec("ifconfig",$res);
	$lastName = "";
	$lastSub = "";
	foreach($res as $line){
		if(strstr($line,"flags=")){
			$d = explode("flags=",$line);
			$ns = explode(":",trim($d[0]));
			$lastName = $ns[0];
			$lastSub = $ns[1];
			if($lastSub==''){$lastSub='@';}
			$back[$lastName]['name']=$lastName;
			
		}else if(strstr($line,"ether ")){
			$mac = explode("  txqueuelen",explode("ether ",$line)[1])[0];
			$back[$lastName]['mac']=$mac;
		
			
		}else if(strstr($line,"inet ")){
			$ip = explode("  netmask",explode("inet ",$line)[1])[0];
			$back[$lastName]['list'][$lastSub]['ip']=$ip;
			
			$mask = explode("  broadcast",explode("netmask ",$line)[1])[0];
			$back[$lastName]['list'][$lastSub]['mask']=$mask;
			
		}else if(strstr($line,"inet6 ")){
			$ipv6 = explode("  prefixlen",explode("inet6 ",$line)[1])[0];
			$back[$lastName]['list'][$lastSub]['ipv6']=$ipv6;
		}	
	}
	//print_r($back);
	return $back;
	
}

$_HLCPHP['ifconfig']= "ifconfig_read_vfgvfdergsfvrfverevferbrr";  

?>
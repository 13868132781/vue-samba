<?php
function getoutip($dest){
	exec("route |awk '{print $1,$2,$3,$8,$4}'",$res);
	$trueeth="";
	foreach($res as $key=> $val){
		if(stristr($val,"Kernel")) continue;
		if(stristr($val,"Destination")) continue;
		$valarr=explode(" ",$val);
		if(stristr($val,"default")){
			$trueeth=$valarr[3];
			continue;
		}
		
		$ips = getSubNetIPList($valarr[0], $valarr[2],$dest);
		if($ips=="getit"){
			$trueeth=$valarr[3];
			break;
		}
	}
	
	return exec("ifconfig ".$trueeth." | grep 'inet' |grep -v 'inet6'|awk '{printf $2}'");
}


function getSubNetIPList($ip, $subnetMask,$chekip="")
{
    // ip址网掩码转换整数
    $ipNum = ip2long($ip);
    $subnetMaskNum = ip2long($subnetMask);
	// 计算网络号应整数（址网段起始址表示网段所能给主机使用）
	$netNum = ($ipNum & $subnetMaskNum);
    // 计算网段结束IP址（址网段结束IP址广播址所能给主机使用）
	$broadcastIPNum = $netNum | (~$subnetMaskNum);
	// 存放IP址列表
    $ipAddrs = array();
    for ($num = $netNum + 1; $num <= $broadcastIPNum - 1; $num++) {
        $ipAddrs[] = long2ip($num);
		if($chekip==long2ip($num)){
			return "getit";
		}
    }
    return $ipAddrs;
}

?>
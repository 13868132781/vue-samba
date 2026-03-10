<?php
require_once(__DIR__."/hlcws.php");
$ws=new WS('wss','43210','bin');


exec("sudo chmod 777 /temp/terserver.txt");
exec("sudo echo '' > /temp/terserver.txt");
$logFile = fopen("/temp/terserver.txt", "a");
function doLog($msg){
	global $logFile;
	fwrite($logFile, $msg."\n");
}


$reqArgs=[];
foreach($argv as $argvi=>$argvo){
	if(strstr($argvo,'GET:')){//GET参数
		doLog($argvo);
		parse_str(explode('GET:',$argvo)[1],$reqArgs);
	}
}

// 打开标准输入
$stdin = fopen("php://stdin", "r");
$ssReadsR = [$stdin];

$desc = array(
	0 => array("pipe", "r"),// 标准输入，子进程从此管道中读取数据
	1 => array("pipe", "w"),// 标准输出，子进程向此管道中写入数据
	2 => array("pipe", "w"),// 标准错误，
);
$cwd = '/';
$env = [
	'TERM' => 'xterm-256color',
	/* sudo -s 会直接用root的env，不过TERM得我们自己设置，否则是unknown
	'LANG' => 'zh_CN.UTF-8',
	*/
];
$process = null;
$pipes=null;

if(isset($reqArgs['nas'])){//连接到一台nas
	$inCmd='';
	if(isset($reqArgs['cols'])){
		$inCmd .= "stty cols ".$reqArgs['cols'].";";
	}
	if(isset($reqArgs['rows'])){
		$inCmd .= "stty rows ".$reqArgs['rows'].";";
	}
	$inCmd .= "clear;";
	$inCmd.="ssh -t ".$reqArgs['nas'];
	
	$realcmd = "sudo -s script -q -c '".$inCmd."' /dev/null";
	$process = proc_open( $realcmd ,$desc,$pipes,$cwd,$env);
	if(!is_resource($process)){
		echo 'proc_open fail';
		goto finish;
	}	
	
	stream_set_blocking($pipes[1], 0);
	stream_set_blocking($pipes[2], 0);
	
}else{
	$realcmd = "sudo -s script -q /dev/null";
	$process = proc_open( $realcmd ,$desc,$pipes,$cwd,$env);
	if(!is_resource($process)){
		echo 'proc_open fail';
		goto finish;
	}
	
	stream_set_blocking($pipes[1], 0);
	stream_set_blocking($pipes[2], 0);

	$initExec=false;
	if(isset($reqArgs['cols'])){
		fwrite ($pipes[0], "stty cols ".$reqArgs['cols']."\n");
		$initExec = true;
	}
	if(isset($reqArgs['rows'])){
		fwrite ($pipes[0], "stty rows ".$reqArgs['rows']."\n");
		$initExec = true;
	}
	if($initExec){
		fwrite ($pipes[0], "clear\n");	
		sleep(1);
	}

}

$ssReadsR[] = $pipes[1];
$ssReadsR[] = $pipes[2];

while(true){
	$ssReads = $ssReadsR;
	$ssWrite = NULL;
	$except = NULL;
	doLog("=====================");
	stream_select($ssReads, $ssWrite, $except, NULL); 
	foreach ($ssReads as $ssRead){
		if($ssRead==$stdin){
			doLog("do input");
			$buffer=fread($ssRead,1024);
			doLog('['.$buffer.']');
			if(feof($ssRead)){
				//标准输出被关闭后，再echo输出，会出错，导致退出进程
				//所以在父进程里，应该只关闭标准输入
				echo 'stdin close';
				goto finish;
			}
			fwrite ($pipes[0], $buffer);
		}else{
			doLog("do output");
			$buffer=fread($ssRead,1024);
			doLog("do output end fread");
			echo $buffer;
			if(feof($pipes[1])){
				echo 'stty close';
				goto finish;
			}
		}
	}
}





finish:
doLog("do finish");
fclose($stdin);
fclose($pipes[0]);
fclose($pipes[1]);
fclose($pipes[2]);
proc_close($process);
//若子进程没结束,proc_close可能卡住，不过父进程60秒后会清理所有子孙进程
doLog("do exit");
fclose($logFile);
exit();
?>
<?php
require_once(__DIR__."/hlcws.php");
new WS([
	'pro' => 'wss',//协议 加密的wss，不加密的ws
	'port' => '43210', //端口
	'type' => 'bin', //数据类型，text文本，bin二进制
	'timeout' => 30, //默认30分钟
],
function($wsp,$client,$reqInfo){//请求处理函数
	$wsp->sdSay("do work");
	$ssReadsR = [$client];
	$reqArgs = [];
	parse_str($reqInfo['GET'],$reqArgs);//url参数
	
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
	
	if(!isset($reqArgs['nas'])){
		$reqArgs['nas']='127.0.0.1';
	}
	
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
			$wsp->sdLog('proc_open fail');
			goto finish;
		}	
		
		stream_set_blocking($pipes[1], 0);
		stream_set_blocking($pipes[2], 0);
		
	}else{
		$realcmd = "sudo -s script -q /dev/null";
		$process = proc_open( $realcmd ,$desc,$pipes,$cwd,$env);
		if(!is_resource($process)){
			$wsp->sdLog('proc_open fail');
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
		$wsp->sdLog("do while");
		if($wsp->myDoExit){
			goto finish;
		}
		$ssReads = $ssReadsR;
		$wsp->streamSelect($ssReads);
		foreach ($ssReads as $ssRead){
			if($ssRead==$client){
				$wsp->sdLog("do input");
				$buffer=$wsp->read($ssRead);
				$wsp->sdLog('['.$buffer.']');
				if(!is_resource($ssRead) or feof($ssRead)){
					$wsp->sdLog('client close');
					goto finish;
				}
				fwrite ($pipes[0], $buffer);
			}else{
				$wsp->sdLog("do output");
				$buffer=fread($ssRead,1024);
				$wsp->write($client, $buffer);
				if(!is_resource($ssRead) or feof($ssRead)){
					$wsp->sdLog('stty close'); 
					goto finish;
				}
			}
		}
	}
	
	finish:
	$wsp->sdSay("do finish");
	$wsp->close();
	$wsp->procClose($process,$pipes);	
	$wsp->sdSay("do exit");
	exit();
});
?>
<?php

class WS {
	var $master;
	var $debug = false;
	var $clientList=[];//client handshake pipes 
	var $ssReads=[];
	var $opcode='text'; //数据类型，是文本（text）还是二进制（bin）
	var $closeStr="this_is_a_websocket_close_packege";
	
	var $identyArg = "__qwehonglichengrty__";
	
	var $pidToKill=[];
	
	function __construct($pro,$port,$opcode='text'){
		global $argv;
		foreach($argv as $argvo){
			if($argvo == $this->identyArg){
				return;
			}
			if($argvo == 'debug'){
				$this->debug = true;
			}
		}
		
		$this->opcode = ($opcode=='text'?'text':'bin');
		
		//清除占用端口的进程
		exec("sudo lsof -i :".$port." | grep -v PID |awk '{print $2}'",$pids);
		foreach($pids as $pido){
			exec("sudo kill -9 ".$pido);
		}
		
		if($pro=='wss'){
			//for wss:客户端用h5的websocket没问题，但用web_socket_js时会报错
			$context = stream_context_create();
			stream_context_set_option($context,'ssl','local_cert','/etc/zcert/server.pem');
			stream_context_set_option($context,'ssl','local_pk','/etc/zcert/server.key');
			stream_context_set_option($context,'ssl','passphrase','2716888');
			stream_context_set_option($context,'ssl','allow_self_signed',true);
			stream_context_set_option($context,'ssl','verify_peer',false);
			stream_context_set_option($context,'ssl','verify_peer_name',false);
			
			$this->master=stream_socket_server("ssl://0.0.0.0:".$port, $errno, $errstr, STREAM_SERVER_BIND|STREAM_SERVER_LISTEN, $context);
			if(!$this->master){
				$this->say($errstr);
				exit();
			}
			//for wss end
		}else{
			//for ws start:
			$this->master=stream_socket_server("tcp://0.0.0.0:".$port, $errno, $errstr);
			if(!$this->master){
				$this->say($errstr);
				exit();
			}
			//for ws end
		}
		$this->ssReads[] = $this->master;
		$this->say("Server Started : ".date('Y-m-d H:i:s'));
		$this->say("Listening port : ".$port);
		$this->say("Master socket  : ".$this->master."\n");
		
		
		//终端退出，会结束连接，js会立刻感应到，应该时close tcp时通知的吧
		//如果时js端关闭的话，可能发关闭包，也可能不发，所以服务端要定时发ping
		// 上一次发送心跳包的时间戳
		$lastHeartbeatTime = microtime(true);
		$heartbeatInterval = 60; // 心跳间隔（秒）
		
		while(true){
			$socketArr = $this->ssReads;
			$ssWrite = NULL;
			$except = NULL;
			$this->sdLog("\n===================\n");
			stream_select($socketArr, $ssWrite, $except, $heartbeatInterval);
			
			foreach ($socketArr as $socket){
				$this->sdLog("\n==========".$socket."=========\n");
				
				//连接请求
				if ($socket==$this->master){  //主机
					$this->sdLog("do socket_accept()");
					//accpt函数会报ssl警告，但不影响使用
					$client = @stream_socket_accept($socket);
					if (!$client){
						$this->sdLog("socket_accept() failed");
						
					} else{
						
						$this->ssReadsAdd($client);
						
						$this->clientList[]=[
							'client'=>$client,
						];
						
						$this->say("\n" . $client . " CONNECTED!");
						$this->say(date("Y-n-d H:i:s"));
					}
					break;
				}
				
				//交互过程
				foreach($this->clientList as $ci=>$clientO){
					if(isset($clientO['client']) and $socket==$clientO['client']){
						$this->sdLog("do client input");
						$buffer = @fread($socket,2048);
						if ($buffer===false or $buffer===''){//断开连接
							$this->sdLog("do disconnect");
							$this->disConnect($clientO,$ci);
							break;
							
						}elseif(!isset($clientO['handshake'])){
							$this->sdLog("do HandShake");
							$reqInfo = $this->doHandShake($socket, $buffer);
							if(!$reqInfo){
								$this->disConnect($clientO,$ci);
								break;
							}
							$this->clientList[$ci]['handshake'] = true;
							
							list($process, $pipes) = $this->myProcOpen($reqInfo);
							if(!is_resource($process)){
								$this->disConnect($clientO,$ci);
								break;
							}
							
							$this->clientList[$ci]['process'] = $process;
							$this->clientList[$ci]['pipes'] = $pipes;
							$this->ssReadsAdd($pipes[1]);
							$this->ssReadsAdd($pipes[2]);
							
						}else{
							$this->sdLog("do fwrite");
							$buffer = $this->decode($buffer);
							if($buffer == $this->closeStr){
								$this->disConnect($clientO,$ci);
								break;
							}
							$this->sdLog("recv[".$buffer."]");
							if($buffer!=''){
								fwrite ($clientO['pipes'][0], $buffer);
							}
						}
						break;
							
					}elseif(isset($clientO['pipes']) and $socket==$clientO['pipes'][1]){
						$this->sdLog("do bash stdin input");
						$output=fread($clientO['pipes'][1],1024);//stream_get_contents
						$this->sdLog('['.$output.']');
						
						
						if($output!='' and $clientO['handshake']){
							$this->send($clientO['client'], $output);
						}
						if(feof($clientO['pipes'][1])){
							$this->disConnect($clientO,$ci);
						}
						break;
						
					}elseif(isset($clientO['pipes']) and $socket==$clientO['pipes'][2]){
						$this->sdLog("do bash error input");
						$output=fread($clientO['pipes'][2],1024);//stream_get_contents
						$this->sdLog('['.$output.']');
						if($output!='' and $clientO['handshake']){
							$this->send($clientO['client'], $output);
						}
						break;
					}
				}
			}
			
			// 发送心跳包
			$currentTime = microtime(true);
			if ($currentTime - $lastHeartbeatTime >= $heartbeatInterval) {
				foreach($this->clientList as $ci=>$clientO){
					$pingmsg = $this->frame('Hello','ping');
					fwrite($clientO['client'], $pingmsg);
				}
				$lastHeartbeatTime = $currentTime;
				$this->say("send ping");
				
				$this->delAllSonPid();
			}
			
			
		}
	}
	
	function myProcOpen($reqInfo){
		$desc = array(
			0 => array("pipe", "r"),// 标准输入，子进程从此管道中读取数据
			1 => array("pipe", "w"),// 标准输出，子进程向此管道中写入数据
			2 => array("pipe", "w"),// 标准错误，
		);
		$cwd = '/tmp';
		$env = array('some_option' => 'aeiou');
		
		$scriptPath = realpath($_SERVER['SCRIPT_FILENAME']);
		exec("sudo chmod 777 ".$scriptPath);
		$argStr="";
		global $argv;
		foreach($argv as $argvi=>$argvo){
			if($argvi==0){continue;}
			$argStr.=" '".$argvo."'";
		}
		$cmd = "php ".$scriptPath." ".$argStr." 'GET:".$reqInfo['GET']."' '".$this->identyArg."' ";
		$this->sdLog($cmd);
		
		$process = proc_open( $cmd ,$desc,$pipes,$cwd,$env);
		stream_set_blocking($pipes[1], 0);
		stream_set_blocking($pipes[2], 0);
		return array($process, $pipes);
	}
	
	
	function doConnect($client){
		
		
	}
	
	function disConnect($clientO,$ci){
		$this->ssReadsDel($clientO['client']);
		fclose($clientO['client']);
		
		if(isset($clientO['pipes'])){
			$this->ssReadsDel($clientO['pipes'][1]);	
			$this->ssReadsDel($clientO['pipes'][2]);
			$this->getAllSonPid($clientO);
			fclose($clientO['pipes'][0]);
		}
		
		/*
		//关闭pipes[1],pipes[2]和process,时，
		//子进程在检测到标准输入eof后，在结束动作里若有输出
		//那样可能导致子进程卡住或报错中断退出
		//因为此时，子进程输出管道已经关闭了
		//所以不关闭pipes2和pipes3，这两个不关也没关系
		//但当子进程中止后，不运行proc_close的话，子进程会成为僵尸进程
		//但再子进程没结束的情况下运行proc_close，proc_close又会卡住
		//所以，目前做法时，把这些保存下来，60秒后再处理
		
		fclose($clientO['pipes'][1]);
		fclose($clientO['pipes'][2]);
		
		//如果子进程没结束，这个函数会卡住
		proc_close($clientO['process']);//会自动关闭各个管道
		*/
		
		array_splice($this->clientList,$ci,1);
		
		$this->say($clientO['client']." DISCONNECTED!");
	}
	
	function send($client, $msg){
		//$this->sdLog("send:[".$msg."]");
		$msg = $this->frame($msg);
		fwrite($client, $msg);
		
	}
	function ssReadsAdd($client){
		array_push($this->ssReads, $client);
	}
	function ssReadsDel($client){
		$index = array_search($client, $this->ssReads);
		if ($index >= 0){
			array_splice($this->ssReads, $index, 1); 
		}
	}
	function doHandShake($socket, $buffer){
		if (0 === strpos($buffer, 'GET')){
			$this->sdLog("\nRequesting handshake...");
			$this->sdLog($buffer);
			list($reqArgs, $host, $origin, $key) = $this->getHeaders($buffer);
			if(strstr($reqArgs,'?')){
				$reqArgs = explode('?',$reqArgs)[1];
			}
			$this->say("request args: ".$reqArgs);
			$this->sdLog(" ws share key: ".$key);
			$new_key=base64_encode(sha1($key."258EAFA5-E914-47DA-95CA-C5AB0DC85B11", true));
			$this->sdLog("Handshaking...");
			$upgrade  = "HTTP/1.1 101 Switching Protocol\r\n" .
						"Upgrade: websocket\r\n" .
						"Connection: Upgrade\r\n" .
						"Sec-WebSocket-Version: 13\r\n".
						"Server: softdomain\r\n".
						"Sec-WebSocket-Accept: ".$new_key."\r\n\r\n";
						//必须以两个回车结尾
			$this->sdLog($upgrade);
			$sent = fwrite($socket, $upgrade);
			$this->sdLog("Done handshaking...");
			return ['type'=>'get','GET'=>$reqArgs, 'host'=>$host, 'origin'=>$origin];
			
		}elseif(0 === strpos($buffer, '<polic')){
			$policy_xml='<?xml version="1.0"?><cross-domain-policy><site-control permitted-cross-domain-policies="all"/><allow-access-from domain="*" to-ports="*"/></cross-domain-policy>' . "\0";
			$sent = fwrite($socket,$policy_xml);
			return ['type'=>'xml','GET'=>''];
			
		}
		$errorhtml="HTTP/1.1 400 Bad Request\r\n\r\n<b>400 Bad Request</b><br>Invalid handshake data for websocket. <br> See <a href=\"http://wiki.workerman.net/Error1\">http://wiki.workerman.net/Error1</a> for detail.";
		$sent = fwrite($socket,$errorhtml);
		return false;
	}

	function getHeaders($req){
		$r = $h = $o = $key = null;
		if (preg_match("/GET (.*) HTTP/"              ,$req,$match)) { $r = $match[1]; }
		if (preg_match("/Host: (.*)\r\n/"             ,$req,$match)) { $h = $match[1]; }
		if (preg_match("/Origin: (.*)\r\n/"           ,$req,$match)) { $o = $match[1]; }
		if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/",$req,$match)) { $key = $match[1]; }
		
		//在js创建ws时，如果指定第二个参数，
		//在header的sec-websocket-protocol里会包含对应信息
		//也可以用此方法传参数
		
		return array($r, $h, $o, $key);
	}



	function decode($buffer) {
		if($buffer=='') return '';
		
		$len = $masks = $data = $decoded = null;
		
		$firstbyte    = ord($buffer[0]);
       $secondbyte   = ord($buffer[1]);
       $is_fin_frame = $firstbyte >> 7;//标识是否是此消息的最后一个数据包
       $masked       = $secondbyte >> 7;//标识是否经过掩码处理
       $opcode       = $firstbyte & 0xf;//包类型
		
		//echo $is_fin_frame."|".$masked."|".$opcode."\r\n";
		
		switch ($opcode) {
           case 0x0://表示是延续frame
				break;
           case 0x1:// 表示文本frame
           case 0x2:// 二进制frame
				$len = ord($buffer[1]) & 127;
				if ($len === 126) {
					$reallen=unpack('S',pack('n',substr($buffer,2,4)))[1];
					$masks = substr($buffer, 4, 4);
					$data = substr($buffer, 8,$reallen);
					$lastbuffer=substr($buffer, 8+$reallen);
				} 
				else if ($len === 127) {
					//这个长度是按32bit计算的，但实际是64bit网络字节序
					$reallen=unpack('L',pack('N',substr($buffer,2,10)))[1];
					$masks = substr($buffer, 10, 4);
					$data = substr($buffer, 14,$reallen);
					$lastbuffer=substr($buffer, 14+$reallen);
				} 
				else {
					$masks = substr($buffer, 2, 4);
					$data = substr($buffer, 6,$len);
					$lastbuffer=substr($buffer, 6+$len);
				}
				for ($index = 0; $index < strlen($data); $index++) {
					$decoded .= $data[$index] ^ $masks[$index % 4];
				}
				$decoded .=$this->decode($lastbuffer);
				
				//echo '['.$decoded.']';
				return $decoded;
              break;
           case 0x8:// 关闭包.
				return $this->closeStr;
				break;
			case 0x9://ping包
				break;
			case 0xA://pong包
				break;
			default:
				break;
		   
		}
		

	}
	
	function frame($text,$inopcode='') {
		$opcode = $this->opcode=='text'?0x1:0x2;
		if($inopcode){
			$inopcodeMap=['ping'=>0x9,'pong'=>'0xA'];
			$opcode = $inopcodeMap[$inopcode];
		}
       $b1 = 0x80 | ($opcode & 0x0f);
       $length = strlen($text);

       if ($length <= 125)
            $header = pack('CC', $b1, $length);
       elseif ($length > 125 && $length < 65536)
            $header = pack('CCn', $b1, 126, $length);
       elseif ($length >= 65536)
            $header = pack('CCNN', $b1, 127, $length);

       return $header . $text;
    }

	function frameOld($s){
		$a = str_split($s, 125);
		if (count($a) == 1){
			return "\x81" . chr(strlen($a[0])) . $a[0];
		}
		$ns = "";
		foreach ($a as $o){
			$ns .= "\x81" . chr(strlen($o)) . $o;
		}
		return $ns;
	}

	
	function say($msg = ""){
		echo $msg . "\n";
	}
	function sdLog($msg = ""){
		if ($this->debug){
			echo $msg . "\n";
		} 
	}
	
	//下面两个函数，保存子孙进程，60秒后，再杀死
	function getAllSonPid($fid,$deep=false){
		if(!$deep){
			$clientO = $fid;
			$proInfo = proc_get_status($clientO['process']);
			$fid = $proInfo['pid'];
		}
		
		$backa=[$fid];
		exec("sudo ps -o pid --no-header --ppid ".$fid,$res);
		foreach($res as $ress){
			$back = $this->getAllSonPid($ress,true);
			$backa = array_merge($backa,$back);
		}
		
		if(!$deep){
			$currentTime = microtime(true);
			$this->pidToKill[] = [$currentTime,$backa,$clientO];
		}
		
		return $backa;
	}
	function delAllSonPid(){
		$killpids = $this->pidToKill;
		$this->pidToKill=[];
		foreach($killpids as $ki=>$kv){
			$oldtime = $kv[0];
			if(microtime(true)-$oldtime < 60){
				$this->pidToKill[] = $kv;//时间没到，回写回去
				continue;
			}
			
			$allson = $kv[1];
			$this->sdLog("kill pid: ".join(',',$allson));
			foreach($allson as $son){
				exec("sudo kill -9 ".$son." 2>&1");
			}
			$clientO = $kv[2];
			if(is_resource($clientO['process'])){
				$proInfo = proc_get_status($clientO['process']);
				if(!$proInfo['running']){
					proc_close($clientO['process']);
				}					
			}
		}
		
	}
}


?>
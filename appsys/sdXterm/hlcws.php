<?php
//ws监听类，在父进程里
class WS {
	var $master;
	var $debug = false;
	var $clientList=[];
	var $clientCount=0;
	var $ssReads=[];
	var $fapid=0;
	var $opCode='text';
	public $faHeartbeatTime=0;
	public $faHeartbeatInterval=60;
	public $timeout = 30;
	
	function __construct($opts,$myfunc){
		global $argv;
		foreach($argv as $argvo){
			if($argvo == 'debug'){
				$this->debug = true;
			}
		}
		
		if(isset($opts['type'])and $opts['type']){
			$this->opCode = $opts['type'];
		}
		if(isset($opts['timeout'])and intval($opts['timeout'])){
			$this->timeout = intval($opts['timeout']);
		}
		
		
		$port = '43210';
		if(isset($opts['port'])and $opts['port']){
			$port = $opts['port'];
		}
		
		
		$pro = 'wss';
		if(isset($opts['pro'])and $opts['pro']){
			$pro = $opts['pro'];
		}
		
		
		$this->faHeartbeatTime = microtime(true);
		$this->fapid = getmypid();
		
		
		//清除占用端口的进程
		exec("sudo lsof -i :".$port." | grep -v PID |awk '{print $2}'",$pids);
		foreach($pids as $pido){
			exec("sudo kill -9 ".$pido." 2>&1");
		}
		
		if($pro=='wss'){
			//for wss:客户端用h5的websocket没问题，但用web_socket_js时会报错
			$context = stream_context_create();
			stream_context_set_option($context,'ssl','local_cert','/etc/zcert/server.pem');
			stream_context_set_option($context,'ssl','local_pk','/etc/zcert/server.key');
			//stream_context_set_option($context,'ssl','passphrase','');
			stream_context_set_option($context,'ssl','allow_self_signed',true);
			stream_context_set_option($context,'ssl','verify_peer',false);
			stream_context_set_option($context,'ssl','verify_peer_name',false);
			
			$this->master=stream_socket_server("ssl://0.0.0.0:".$port, $errno, $errstr, STREAM_SERVER_BIND|STREAM_SERVER_LISTEN, $context);
			if(!$this->master){
				$this->sdSay($errstr);
				exit();
			}
			//for wss end
		}else{
			//for ws start:
			$this->master=stream_socket_server("tcp://0.0.0.0:".$port, $errno, $errstr);
			if(!$this->master){
				$this->sdSay($errstr);
				exit();
			}
			//for ws end
		}
		$this->ssReads[] = $this->master;
		$this->sdSay("Server Started : ".date('Y-m-d H:i:s'));
		$this->sdSay("Listening port : ".$port);
		$this->sdSay("Master socket  : ".$this->master."\n");
		
		
		//终端退出，会结束连接，js会立刻感应到，应该时close tcp时通知的吧
		//如果时js端关闭的话，可能发关闭包，也可能不发，所以服务端要定时发ping
		while(true){
			$socketArr = $this->ssReads;
			$ssWrite = NULL;
			$except = NULL;
			$this->sdLog("do while");
			stream_select($socketArr, $ssWrite, $except, $this->faHeartbeatInterval);
			foreach ($socketArr as $socket){
				//连接请求
				if ($socket==$this->master){  //主机
					
					$this->clientCount++;
					
					$pid = pcntl_fork();
					if ($pid == -1){
						die('could not fork');
					}else if($pid){
						$this->clientList[]=[
							'pid' => $pid,
							'sonpids' => $this->getSonPids($pid)
						];
					}else{
						//在子进程中运行
						new WSProcess($socket,$this,$myfunc);
						exit();
					}
					break;
				}
			}	
			
			$currentTime = microtime(true);
			if ($currentTime - $this->faHeartbeatTime >= $this->faHeartbeatInterval) {
				$clientList = $this->clientList;
				$this->clientList=[];
				foreach($clientList as $ci=>$clientO){
					//该操作检测子进程是否退出，若退出，消除僵尸进程
					$res = pcntl_waitpid($clientO['pid'], $status, WNOHANG);
					
					if($res == -1 || $res > 0){//已经退出
						$this->delSonPids($clientO['sonpids']);
						$this->sdSay("pid ".$clientO['pid']." killed!");
						continue;
					}
					
					$clientO['sonpids'] = $this->getSonPids($clientO['pid']);
					$this->clientList[] = $clientO;
				}
				$this->faHeartbeatTime = $currentTime;
				$this->sdSay("send ping");
			}
		}
	}
	
	//下面两个函数，获取和杀死子孙进程
	function getSonPids($fid=''){
		if(!$fid){
			$fid = getmypid();
		}
		$backa=[];
		exec("sudo ps -o pid --no-header --ppid ".$fid,$res);
		foreach($res as $ress){
			$backa[]=$ress;
			$back = $this->getSonPids($ress);
			$backa = array_merge($backa,$back);
		}
		return $backa;
	}
	function delSonPids($allson){
		$this->sdLog("kill pid: ".join(',',$allson));
		foreach($allson as $son){
			exec("sudo kill -9 ".$son." 2>&1");
		}
	}
	
	function sdSay($msg = ""){
		$pid = getmypid();
		$from=str_pad("father",10);
		echo date('Y-m-d H:i:s')."/".$pid."/".$from." >> ".$msg . "\n";
	}
	function sdLog($msg = ""){
		$pid = getmypid();
		$from=str_pad("father",10);
		if ($this->debug){
			echo date('Y-m-d H:i:s')."/".$pid."/".$from." >> ".$msg . "\n";
		} 
	}
}







//单个ws请求处理类，在子进程里
class WSProcess{
	
	public $debug=false;
	public $myCount=0;
	public $opCode='text'; //数据类型，是文本（text）还是二进制（bin）
	public $sonHeartbeatTime=0;
	public $sonHeartbeatInterval=60;
	public $sonClient=null;
	public $myDoExit=false;
	public $timeout=30;//分钟
	public $activeTime=0;
	
	function __construct($socket,$ws,$myfunc){	
		$this->debug = $ws->debug;
		$this->fapid = $ws->fapid;
		$this->myCount = $ws->clientCount;
		$this->timeout = $ws->timeout;
		$this->opCode =($ws->opCode=='text'?'text':'bin');
		$this->sonHeartbeatTime = microtime(true);
		$this->activeTime = microtime(true);
						
		$this->sdLog("do socket_accept()");
		//accept最好在子进程里，
		//若在父进程的话，$client这个变量得区分开，
		//每次accept用同一个的话，可能被覆盖，导致并发冲突
		//accpt函数会报ssl警告，且第一次失败，第二次才成功
		$client = @stream_socket_accept($socket);
		fclose($socket);
		if (!$client){
			$this->sdLog("socket_accept() failed");
			exit();
		}
						
		$buffer=fread($client,1024);
		$reqInfo = $this->doHandShake($client, $buffer);
		if(!$reqInfo){
			$this->sdLog("doHandShake failed");
			exit();
		}
		stream_set_blocking($client, 0);
		$this->sdSay($client." CONNECTED!"); 
						
		$this->sonClient = $client;
		$this->sonHeartbeatTime = microtime(true);
		$myfunc($this,$client,$reqInfo);
		$this->sdSay($client." DISCONNECTED!"); 
		exit();
	}
	
	//子进程用于监听的函数
	public function streamSelect(&$ssReads, $hbInterval=null){
		$hbtv = $this->sonHeartbeatInterval;
		if($hbInterval){
			$hbInterval = intval($hbInterval);
			if($hbInterval>0 and $hbInterval<$hbtv){
				$hbtv = $hbInterval;
			}
			
		}
		$ssWrite = NULL;
		$except = NULL;
		stream_select($ssReads, $ssWrite, $except, $hbtv);
		
		$currentTime = microtime(true);
		if ($currentTime - $this->sonHeartbeatTime >= $this->sonHeartbeatInterval) {
			$pingmsg = $this->frame('Hello','ping');
			fwrite($this->sonClient, $pingmsg);
			
			$mypid = getmypid();
			$fpid = exec("sudo ps -o ppid --no-header --pid ".$mypid);
			if($fpid=='1'){//父进程被删了，所以挂在了init进程上了
				$this->sdSay("father is killed, end myself");
				$this->myDoExit=true;
			}
			
			if ($currentTime - $this->activeTime >= ($this->timeout*60)) {
				$this->write($this->sonClient,"\r\ntimeout\r\n");
				$this->sdSay('timeout');
				$this->myDoExit=true;//超时退出
			}
			$this->sonHeartbeatTime = $currentTime;
		}
	}
	
	public function read($client){
		$buffer = fread($client,1024);
		$pkgType = '';
		$buffer = $this->decode($buffer,$pkgType);
		//pkgType有 close ping pong
		if($pkgType == 'close'){
			$this->sdLog("get close packege");
			$this->myDoExit=true;
			$buffer ='';
		}
		if($buffer){//空可能是其他包，如心跳包，关闭包等，非用户正常输入包
			$this->activeTime = microtime(true);//用于判断超时
		}
		return $buffer;
	}
	public function write($client, $buffer){
		$buffer = $this->frame($buffer);
		while(1){
			//若缓存区溢出，会报警，并返回0或false，接收返回值，好像就不会告警
			//正确的话，会返回所发送的数据长度
			$written = fwrite($client, $buffer);
			if($written==strlen($buffer)) {
				break;
			}
			$this->sdLog("fwrite to client err, try again after 10ms");
			usleep(10000);//等待10毫秒，让缓冲区数据发送出去
		}
	}

	function doHandShake($socket, $buffer){
		if (0 === strpos($buffer, 'GET')){
			$this->sdLog("Requesting handshake...");
			$this->sdLog("handshake request data: \n\n".$buffer);
			list($reqArgs, $host, $origin, $key) = $this->getHeaders($buffer);
			if(strstr($reqArgs,'?')){
				$reqArgs = explode('?',$reqArgs)[1];
			}
			$this->sdLog("request args: ".$reqArgs);
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
			$this->sdLog("handshake response data: \n\n".$upgrade);
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



	function decode($buffer,&$pkgType='') {
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
           case 0x8: //关闭包.
				$pkgType='close';
				return;
				break;
			case 0x9: //ping包
				$pkgType='ping';
				break;
			case 0xA: //pong包
				$pkgType='pong';
				break;
			default:
				break;
		   
		}
		

	}
	
	function frame($text,$inopcode='') {
		$opCodeNum = $this->opCode=='text'?0x1:0x2;
		if($inopcode){
			$inopcodeMap=['ping'=>0x9,'pong'=>'0xA'];
			$opCodeNum = $inopcodeMap[$inopcode];
		}
       $b1 = 0x80 | ($opCodeNum & 0x0f);
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
	
	//下面两个函数，获取和杀死子孙进程
	function getSonPids($fid=''){
		if(!$fid){
			$fid = getmypid();
		}
		$backa=[];
		exec("sudo ps -o pid --no-header --ppid ".$fid,$res);
		foreach($res as $ress){
			$backa[]=$ress;
			$back = $this->getSonPids($ress);
			$backa = array_merge($backa,$back);
		}
		return $backa;
	}
	function delSonPids($allson){
		$this->sdSay("kill pid: ".join(',',$allson));
		foreach($allson as $son){
			exec("sudo kill -9 ".$son." 2>&1");
		}
	}
	
	public function close(){
		if(is_resource($this->sonClient)){
			fclose($this->sonClient);
		}
	}
	
	public function procClose($process,$pipes){
		$allson = $this->getSonPids();
		
		fclose($pipes[0]);
		fclose($pipes[1]);
		fclose($pipes[2]);
		
		$wcount=0;
		while(true){
			$pinfo = proc_get_status($process);
			if(!$pinfo['running']){
				proc_close($process);//若子进程没结束,proc_close可能卡住
				break;
			}
			if($wcount>20){//大于20秒，就不管了
				break;
			}
			$wcount++;
			sleep(1);
		}
		$this->delSonPids($allson);
	}
	
	//非打印字符转为十六进制
	function NonPrintToHex($input) {
		return preg_replace_callback('/[\x00-\x1F\x7F-\xFF]/', function($matches) {
			return '\\x' . sprintf('%02X', ord($matches[0]));
		}, $input);
	}
	
	function sdSay($msg = ""){
		$pid = getmypid();
		$from=str_pad("son_".$this->myCount,10);
		echo date('Y-m-d H:i:s')."/".$pid."/".$from." >> ".$msg . "\n";
	}
	function sdLog($msg = ""){
		$pid = getmypid();
		$from=str_pad("son_".$this->myCount,10);
		if ($this->debug){
			echo date('Y-m-d H:i:s')."/".$pid."/".$from." >> ".$msg . "\n";
		} 
	}
}


?>
<?php
	
	function disArgs(){
		$thisargs=[
		'auth' => 'tac', //rad or tac
		'user' => '',
		'pass' => '',
		'nas' => '', //nas ip
		'nac' => '', //client ip
		'state' => '',
		'posttype' => '',
	];
		
		global $argv;
		if(count($argv)<=1){//分析参数
			echoOut(1,'lack of args');
		}
		
		$argss = $argv[1];
		$argsa = explode('||',$argss);
		
		foreach($argsa as $argso){
			if(strstr($argso,'=')){
				$argsoa = explode('=',$argso,2);
				if(substr($argsoa[1],0,2)=='0x'){
					$argsoa[1]=hex2bin(substr($argsoa[1],2));
				}
				$thisargs[$argsoa[0]]=$argsoa[1];
			}
		}
		
		
		
		
		
		return $thisargs;
		
	}


	function echoOut($code,$msg,$vars=[],$logtype,$args){
		//$code -1 challenge 0 success 1 fail
		//logtype 0 不记录 1 认证 2 命令授权
		if($code!=0){
			$vars['reply']['Reply-Message']=$msg;
		}
		if($code==-1){
			$vars['check']['Response-Packet-Type']='Access-Challenge';
			$vars['reply']['State']='1'; 
		}
		
		if($logtype==1){
			$reply='Access-Accept';
			if($code>0){
				$reply='Access-Reject';
			}
			\DB::table("sdaaa_log.rad_login")
			->insert([
				'username' => $args['user'],
				'user' => $args['user'],
				'pass' => $args['pass'],
				'reply' => $reply,
				'authdate' => \DB::raw('now()'),
				'date' => \DB::raw('now()'),
				'logtime' => \DB::raw('now()'),
				'nasip' => $args['nas'],
				'clientip' => $args['nac'],
				'callingip' => $args['nac'],
				'Reply_Message' => $msg
			]);
		}elseif($logtype==2){
			\DB::table("sdaaa_log.rad_oper")
			->insert([
				'logdate' => \DB::raw('now()'),
				'logtime' => \DB::raw('now()'),
				'username' => $args['user'],
				'NAS_name' => $args['nas'],
				'NAC_address' => $args['nac'],
				'cmd' => $args['cmd'],
				'result' => $code,
			]);
		}
		
		
		
		$html='code='.$code.'||';
		if($code>0){
			$html.='msg=sd-error: '.$msg.'||';
		}else{
			$html.='msg='.$msg.'||';
		}
		
		foreach($vars as $list => $ar){
			foreach($ar as $k=>$v){
				$html.= $list.":".$k."=".$v."||";
			}
		} 
		echo '<tofather>'.trim($html,'|').'<tofather>';
		
		global $timestart;
		if($timestart){
			$t2 =  microtime(true);
			echo "====php take time: ".round($t2-$timestart,3)."s";
		}
		
		exit(0);
	}

?>
<?php
ini_set("open_basedir","/");
function autoexec($info){
	//返回的数组首先继承自传入的数组，以后或改成直接返回传入的数组info
	$resarr=$info;
	
	$resarr['strs']="";
	$resarr['code']="0";
	
	if($info['hh']==""){
		$info['hh']="\n";
	}
	
	if($info['timeout']==''){
		$info['timeout']='30';
	}
	
	if($info['tpf1']==''){
		$info['tpf1']=$info['cmds'][0]['tpf'];
	}

	if($info['tpf2']==''){
		$info['tpf2']=$info['cmds'][0]['tpf'];
	}
	
	if($info['coding']==""){
		$info['coding']="UTF-8";
	}
	
	if($info['ssh']=="0"){
		if ($info['port']=='') $info['port']="23";
		$startexe="telnet ".$info['host']." -l ".$info['user']." ".$info['port'];
	}else if($info['ssh']=="1"){
		if ($info['port']=='') $info['port']="22";
		$startexe="ssh -1 ".$info['host']." -l ".$info['user']." -p ".$info['port'];
	}else{
		if ($info['port']=='') $info['port']="22";
		$startexe="ssh -2 ".$info['host']." -l ".$info['user']." -p ".$info['port'];
	}

	$ptyfp=hlc_forkptyexec($startexe);



	if (!is_resource($ptyfp)) {
		$resarr['code']='10';
		$resarr['strs'].="\nsd msg: open error\n";
	}
	
	//login
	$alreadyuser="";$alreadypass="";
	while(1){
		$waitres=fwait($ptyfp,array("(yes/no)?","ogin:","sername:","assword:",$info['tpf1']),$info['timeout'],$info['coding']);
		$resarr['strs'].=$waitres["read"];
		if($waitres["index"]=='eof'){
			$resarr['code']='20';
			$resarr['strs'].="\nsd msg: connect or login eof\n";
			return $resarr;
		}else if($waitres["index"]=='timeout'){
			$resarr['code']='21';
			$resarr['strs'].="\nsd msg: connect or login timeout\n";
			return $resarr;
		}else if($waitres["index"]=='0'){
			fwrite ($ptyfp, "yes".$info['hh']);
		}else if($waitres["index"]=='1' or $waitres["index"]=='2'){
			if($alreadyuser==""){
				fwrite ($ptyfp, $info['user'].$info['hh']);
				$alreadyuser="true";
			}else{
				$resarr['code']='31';
				$resarr['strs'].="\nsd msg: login failed\n";
				return $resarr;
			}
		}else if($waitres["index"]=='3'){
			if($alreadypass==""){
				fwrite ($ptyfp, $info['pass'].$info['hh']);
				$alreadypass="true";
			}else{
				$resarr['code']='31';
				$resarr['strs'].="\nsd msg: login failed\n";
				return $resarr;
			}
		}else{
			//logined and get first tpf
			break;
		}
	}
	
	
	if($info['sucmd']!='' and $info['supwd']!=''){
		fwrite ($ptyfp, $info['sucmd'].$info['hh']);
		$waitres=fwait($ptyfp,array($info['tpf2'],"assword:"),$info['timeout'],$info['coding']);
		$resarr['strs'].=$waitres["read"];
		if($waitres["index"]=='timeout'){
			$resarr['code']='35';
			$resarr['strs'].="\nsd msg: enable cmd timeout\n";
			return $resarr;
		}else if($waitres["index"]=='1'){
			fwrite ($ptyfp, $info['supwd'].$info['hh']);
			$waitres=fwait($ptyfp,array($info['tpf2'],"assword:"),$info['timeout'],$info['coding']);
			if($waitres["index"]=='timeout'){
				$resarr['code']='36';
				$resarr['strs'].="\nsd msg: enable pass timeout\n";
				return $resarr;
			}else if($waitres["index"]=='1'){
				$resarr['code']='37';
				$resarr['strs'].="\nsd msg: enable pass error\n";
				return $resarr;
			}
		}
	}
	

	if(stristr( $resarr['strs'],"Welcome to Microsoft Telnet Service")){
		$info['coding']="GB2312";
	}
	
	//exec cmds
	//获取登录后的最后一行作为行通配符
	$lastlinearr=explode("\n",$resarr['strs']);
	$lasttpf=trim($lastlinearr[count($lastlinearr)-1]);
	
	foreach($info['cmds'] as $cmdkey=> $cmdarr){
		fwrite ($ptyfp, $cmdarr['cmd'].$info['hh']);

		$nexttpf=$info['cmds'][$cmdkey+1]['tpf'];
		if($nexttpf=="") $nexttpf=$info['cmds'][$cmdkey]['tpf'];
		if($lasttpf!='' and $cmdarr['lasttpf']){
			$nexttpf=$lasttpf;
		}
		
		$waitres=fwait($ptyfp,array($info['errorstr'],$nexttpf),$info['timeout'],$info['coding']);
		$resarr['strs'].=$waitres["read"];
		
		if($waitres["index"]=='eof'){
			$resarr['code']='0';
			$resarr['strs'].="\nsd msg: exec cmd eof\n";//maybe exec 'exit'
			return $resarr;
		}else if($waitres["index"]=='timeout'){
			$resarr['code']='41';
			$resarr['strs'].="\nsd msg: exec cmd timeout\n";
			return $resarr;
		}else if($waitres["index"]=='0'){
			if($info['errorout']){
				$resarr['code']='42';
				$resarr['strs'].="\nsd msg: exec cmd error\n";
				return $resarr;
			}
		}
		
		//执行到这里，没有return，那就处理数据
		$readarr=explode("\n",$waitres["read"]);
		$lasttpf=trim($readarr[count($readarr)-1]);
		
		//这是该命令去头去尾的返回串
		$writestring=trim(str_replace($lasttpf,'',explode("\n",$waitres["read"],2)[1]));
		
		$resarr['cmds'][$cmdkey]['strs']=$writestring;
		
		if($cmdarr['path']){
			file_put_contents('/tmp/'.basename($cmdarr['path']),$writestring);
			exec("sudo mv ".'/tmp/'.basename($cmdarr['path'])." ".$cmdarr['path']);
		}
			
	}
	
	fclose($ptyfp);

	
	return $resarr;
}



function fwait($pipe,$waitarr,$timeout,$coding){
	stream_set_blocking($pipe, 0);
	//stream_encoding($pipe,'utf-8');
	$machindex="-1";
	$longstr="";
	$nowtime=explode(' ', microtime())[1];
	while(1){
		$str=fread($pipe,1024);
		
		//echo $str;
		//$str=str_replace("\0","",$str);//即使设置了页码，win还是需要去除\0
		//$str=preg_replace('/[\x00-\x09]/',"",$str);
		//$str=preg_replace('/[\x0e-\x1f]/',"",$str);
		
		if($coding!='' and $coding!='UTF-8' and $coding!='utf-8'){
			$str=iconv($coding, 'UTF-8', $str); 
		}
		
		$longstr.=$str;
		
		if(stristr($str,'-- More --') or stristr($str,'--More--') ){
			fwrite($pipe ,' ');
		}

		if(feof($pipe)){
			$machindex="eof";
		}
		
		foreach($waitarr as $wkey=>$wval){
			if ($wval=='') continue;
			if(stristr( $longstr,$wval)){
				$machindex=(string)$wkey;
				break;
			}
		}

		if($timeout and ((int)(explode(' ', microtime())[1])-(int)$nowtime)> (int)$timeout ){
			$machindex="timeout";
		}
		
		if($machindex!='-1'){
			break;
		}
			
	}
	$res["index"]=$machindex;
	$res["read"]=$longstr;
	return $res;
}



 


/*
$info['ssh']="2";
$info['host']="192.168.0.214";
$info['user']="root";
$info['pass']="jbgsn!2716888";
$info['hh']="\n";
$info['timeout']="5";
$info['cmds'][0]['tpf']="#";
$info['cmds'][0]['cmd']="ls -all /etc";
$info['cmds'][1]['tpf']="#";
$info['cmds'][1]['cmd']="exit";
$res=autoexec($info);

echo $res['strs'];


$info['ssh']="0";
$info['host']="192.168.0.215";
$info['user']="administrator";
$info['pass']="jbgsn";
$info['hh']="\r\n";
$info['timeout']="30";
$info['errorstr']="not recognized as an internal or external command";
$info['errorout']="true";
$info['cmds'][0]['tpf']= ">";
$info['cmds'][0]['cmd']="";
$info['cmds'][1]['tpf']= "dministrator>";
$info['cmds'][1]['cmd']="ipconfig";
$info['cmds'][2]['tpf']= "dministrator>";
$info['cmds'][2]['cmd']="exit";


$res=autoexec($info);

echo $res['strs'];

*/

?>
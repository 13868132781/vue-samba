<?php


function auto_login(&$info){
	//目前该函数只实现了忽略进程结束或停止信号，避免僵尸进程
	//该函数会导致hlc_forkwait捕获不到的.
	//所以放到这里，而不是放在外面，表示是针对该进程的子进程
	//避免影响该进程的父进程
	hlc_signal('');
	putenv("TERM=UNKNOWN"); //最好在pty里设置，不过pty也继承自父进程

	$info['lastline']="";
	$info['code']="0";
	$info['errs']="";
	$info['strs']="";
	
	if($info['enter']==""){
		$info['enter']="\n";
	}
	
	if($info['time']==''){ 
		$info['time']='10';
	}
	
	if($info['match']==''){
		$info['match']='#‖>‖$‖]';
	}else{
		$info['match']=auto_dealwithtpf($info['match']);
	}
	
	if($info['coding']==""){
		$info['coding']="UTF-8";
	}
	
	
	$users=explode(" ",$info['user']);
	$passs=explode(" ",$info['pass']);
	foreach($users as $usero){
		$useroa['name']=$usero;
		$useroa['pass']=$passs;
		$userarray[]=$useroa;
	}
	$x=10;
	while($x){$x--;//这样写是避免逻辑有误，造成死循环
		if(count($userarray)>0 and count($userarray[0]['pass'])==0){
			array_splice($userarray,0,1);
			if($info['ssh']>0){
				sleep(1);//有些设备ssh不允许连续连接？
			}
		}
		if(count($userarray)==0){//用户至少有1个，如果为0,表示尝试过登录却失败
			$info['code']='23';
			$info['errs']="login failed";
			$info['strs'].="\nsdmsg: login failed\n";
			return;
		}
		//print_r($userarray);
	
		if($info['ssh']=="0"){
			if ($info['port']=='') $info['port']="23";
			$startexe="telnet ".$info['host']." -l ".$userarray[0]['name']." ".$info['port'];
		}else if($info['ssh']=="1"){
			if ($info['port']=='') $info['port']="22";
			$startexe="ssh -1 ".$info['host']." -l ".$userarray[0]['name']." -p ".$info['port'];
		}else{
			if ($info['port']=='') $info['port']="22";
			$startexe="ssh -2 ".$info['host']." -l ".$userarray[0]['name']." -p ".$info['port'];
		}
		
		$pid=hlc_forkptyexec($ptyfp,$startexe);
		$info['pid']=$pid;
		$info['pty']=$ptyfp;

		if($info['keepty']=='3'){
			$info['code']='9';
			return;
		}
		if (!is_resource($ptyfp)) {
			$info['code']='8';
			$info['errs']="open error:".$ptyfp;
			$info['strs'].="\nsd msg:\n";
			return;
		}
		
		//login
		$cmdforfwait="";
		$y=10;
		while($y){$y--;
			$needtpf="(yes/no)?‖ogin:‖sername:‖assword:‖".$info['match'];
			$waitres=auto_fwait($ptyfp,$cmdforfwait,explode('‖',$needtpf),$info['time'],$info['coding']);
			$cmdforfwait="true";
			$info['strs'].=$waitres["read"];
			//echo "{{{{{".$waitres["index"]."}}}}}";
			if($waitres["index"]=='-2' or $waitres["index"]=='-3'){
				//$info['code']='20';
				$info['strs'].="\nsdmsg: connect or login eof\n";
				$info['pty']="";
				if($y==9){//表示这是该pty第一次fwait
					$info['code']='20';
					$info['errs']='connect or login eof';
					return;
				}
				break;
			}else if($waitres["index"]=='-1'){//超时
				//第一次write，第二次fwait时
				if($y==8 and $info['enter']!="\r\n" and trim(strstr($waitres["read"],$info['enter']))=='' ){
					//回车不是\r\n，而且上次命令之后没有任何数据返回
					$info['strs'].="\nsdmsg: timeout,trying \\r\\n and GB2312\n";
					$info['enter']="\r\n";
					$info['coding']="GB2312";
					fwrite ($ptyfp,chr(127).$info['enter']);
				}else{
					$info['code']='21';
					$info['errs']='connect or login timeout';
					$info['strs'].="\nsdmsg: connect or login timeout\n";
					return;
				}
			}else if($waitres["index"]=='0'){
				fwrite ($ptyfp, "yes".$info['enter']);
			}else if($waitres["index"]=='1' or $waitres["index"]=='2'){
				//要用户，但当前用户密码用完,那得要填新用户
				if(count($userarray)>0 and count($userarray[0]['pass'])==0){
					array_splice($userarray,0,1);
				}
				if(count($userarray)==0){
					$info['code']='22';
					$info['errs']='login failed';
					$info['strs'].="\nsdmsg: login failed\n";
					return;
				}
				fwrite ($ptyfp, $userarray[0]['name'].$info['enter']);

			}else if($waitres["index"]=='3'){
				if(count($userarray)>0 and count($userarray[0]['pass'])==0){
					if(count($userarray)==1){//用户只剩1个，且密码用完
						$info['code']='22';
						$info['errs']='login failed';
						$info['strs'].="\nsdmsg: login failed\n";
						return;
					}
					@fclose($info['pty']);
					break;//还有用户,但这里要密码,得重新连接以登录新用户
				}
				fwrite ($ptyfp, $userarray[0]['pass'][0].$info['enter']);
				array_splice($userarray[0]['pass'],0,1);
			}else{
				$info['code']='0';
				//logined and get first tpf
				$lastlinearr=explode("\n",$waitres['read']);
				$info['lastline']=trim($lastlinearr[count($lastlinearr)-1]);
				
				goto enable;
			}
		}
	
	}
	
	
	enable:
		if($info['sucmd']!='' and $info['supwd']!=''){
			$supwds=explode(" ",trim($info['supwd']));
			fwrite ($ptyfp, $info['sucmd'].$info['enter']);
			$z=10;
			while($z){$z--;
				$needtpf="assword:‖".$info['match'];
				$waitres=auto_fwait($ptyfp,$info['sucmd'],explode('‖',$needtpf),$info['time'],$info['coding']);
				$info['strs'].=$waitres["read"];
				if($waitres["index"]=='-1'){
					$info['code']='35';
					$info['errs']='enable cmd timeout';
					$info['strs'].="\nsdmsg: enable cmd timeout\n";
					return ;
				}else if($waitres["index"]=='0'){
					if(count($supwds)==0){
						$info['code']='37';
						$info['errs']='enable pass error';
						$info['strs'].="\nsdmsg: enable pass error\n";
						return ;
					}
					fwrite ($ptyfp, $supwds[0].$info['enter']);
					array_splice($supwds,0,1);
					
				}else{
					$lastlinearr=explode("\n",$waitres['read']);
					$newlastline=trim($lastlinearr[count($lastlinearr)-1]);
					//五种情况 >平级到> >升级到> >升级到# >失败到> 提权命令错误
					if($info['lastline']==$newlastline and (strstr($waitres['read'],"denied") )){
						if(count($supwds)==0){
							$info['code']='37';
							$info['errs']='enable pass error';
							$info['strs'].="\nsdmsg: enable pass error\n";
							return ;
						}
						fwrite ($ptyfp, $info['sucmd'].$info['enter']);
						continue;
					}else{
						$info['lastline']=$newlastline;
						break;
					}
					
				}
			}
		}
		

	if(stristr( $info['strs'],"Welcome to Microsoft Telnet Service")){
		$info['coding']="GB2312";
	}
	
	return;	
}



/*
function auto_login(&$info){

	$info['lastline']="";
	$info['code']="0";
	$info['strs']="";
	
	if($info['enter']==""){
		$info['enter']="\n";
	}
	
	if($info['time']==''){ 
		$info['time']='10';
	}
	
	if($info['match']==''){
		$info['match']='#‖>‖$‖]';
	}else{
		$info['match']=auto_dealwithtpf($info['match']);
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
	$info['pty']=$ptyfp;

	if($info['keepty']=='3'){
		$info['code']='9';
		return;
	}

	if (!is_resource($ptyfp)) {
		$info['code']='10';
		$info['strs'].="\nsd msg: open error\n";
	}
	
	//login
	$alreadyuser="";$alreadypass="";$cmdforfwait="";
	while(1){
		$needtpf="(yes/no)?‖ogin:‖sername:‖assword:‖".$info['match'];
		$waitres=auto_fwait($ptyfp,$cmdforfwait,explode('‖',$needtpf),$info['time'],$info['coding']);
		$cmdforfwait="true";
		//echo "{{{{".$waitres["text"]."}}}}}\n";
		$info['strs'].=$waitres["read"];
		if($waitres["index"]=='-2' or $waitres["index"]=='-3'){
			$info['code']='20';
			$info['strs'].="\nsdmsg: connect or login eof\n";
			$info['pty']="";
			return;
		}else if($waitres["index"]=='-1'){
			$info['code']='21';
			$info['strs'].="\nsdmsg: connect or login timeout\n";
			return;
		}else if($waitres["index"]=='0'){
			fwrite ($ptyfp, "yes".$info['enter']);
		}else if($waitres["index"]=='1' or $waitres["index"]=='2'){
			if($alreadyuser==""){
				fwrite ($ptyfp, $info['user'].$info['enter']);
				$alreadyuser="true";
			}else{
				$info['code']='22';
				$info['strs'].="\nsdmsg: login failed\n";
				return;
			}
		}else if($waitres["index"]=='3'){
			if($alreadypass==""){
				fwrite ($ptyfp, $info['pass'].$info['enter']);
				$alreadypass="true";
			}else{
				$info['code']='23';
				$info['strs'].="\nsdmsg: login failed\n";
				return;
			}
		}else{
			//logined and get first tpf
			$lastlinearr=explode("\n",$waitres['read']);
			$info['lastline']=trim($lastlinearr[count($lastlinearr)-1]);
			
			break;
		}
	}
	
	if($info['sucmd']!='' and $info['supwd']!=''){
		fwrite ($ptyfp, $info['sucmd'].$info['enter']);
		$needtpf="assword:‖".$info['match'];
		$waitres=auto_fwait($ptyfp,$info['sucmd'],explode('‖',$needtpf),$info['time'],$info['coding']);
		$info['strs'].=$waitres["read"];
		if($waitres["index"]=='-1'){
			$info['code']='35';
			$info['strs'].="\nsdmsg: enable cmd timeout\n";
			return ;
		}else if($waitres["index"]=='0'){
			fwrite ($ptyfp, $info['supwd'].$info['enter']);
			$waitres=auto_fwait($ptyfp,$info['supwd'],explode('‖',$needtpf),$info['time'],$info['coding']);
			if($waitres["index"]=='-1'){
				$info['code']='36';
				$info['strs'].="\nsdmsg: enable pass timeout\n";
				return ;
			}else if($waitres["index"]=='0'){
				$info['code']='37';
				$info['strs'].="\nsdmsg: enable pass error\n";
				return ;
			}
		}
	}
	
	if(stristr( $info['strs'],"Welcome to Microsoft Telnet Service")){
		$info['coding']="GB2312";
	}
	
	return;	
}
*/





function auto_check(&$info){
	$scriptarrs=explode("\n",$info['script']);
	$scriptnew="";
	foreach($scriptarrs as $kay=>$cmd){
		$cmdo=trim($cmd);
		if(substr($cmdo,0,2)==">>"){
			//去除注释
			if(strstr($cmdo,"//")){//定位注释符号位置
				$cmdoslipts=explode("//",$cmdo);
				$cmdonew="";
				foreach($cmdoslipts as $cmdoslipto){
					if($cmdonew=="")
						$cmdonew=$cmdoslipto;
					else
						$cmdonew.="//".$cmdoslipto; 
					if((substr_count($cmdonew,"'")+substr_count($cmdonew,"\""))%2==0){
						break;
					}
				}
				$cmdo=$cmdonew;
			}
			$cmdo="{\$cmd='".addslashes($cmdo)."';}";
		}
		$scriptnew.=$cmdo."\n"; 
	}
	//$str=preg_replace('/\n\s*>.*?([\r\n])/',"\n\$hong;$1","\n".$info['script']);
	//echo "<xmp>";print_r($scriptnew);echo "</xmp>";
	file_put_contents('/tmp/checkforphp.php',"<?php ".$scriptnew."; ?>");
	$res=exec("php /tmp/checkforphp.php 2>&1");
	$mret=preg_match('/^PHP \S* error:(.*) in \/tmp\/.*?( on line .*)/',$res,$matches);
	//return $res;
	if($mret){
		return $matches[1].$matches[2];
	}else{
		return "";
	}
}

function auto_display(&$info){
	$script=$info['script'];
	if(substr($info['script'],0,7)=="header:"){
		$cmdinfo=json_decode(str_replace("header:","",explode("=<==|==>=",$script,2)[0]),true);
		$script=explode("=<==|==>=",$script,2)[1];
		foreach($cmdinfo as $ckey=>$cval){
			$realcval=$cval;
			$mret=preg_match('/({\$([^\s\[\]\{\}\\\]*?)})/',$cval,$matches);
			if($mret){
				$realcval=str_replace($matches[1],$info['envir'][$matches[2]],$cval);
			}
			$info[$ckey]=$info[$ckey]?:$realcval;
		}
	}
	
	$scriptarrs=explode("\n",$script);
	$scriptnew="";
	foreach($scriptarrs as $kay=>$cmd){
		$cmdo=trim($cmd);
		
		//去除注释
		/*
		if(strstr($cmdo,"//")){//定位注释符号位置
				$cmdoslipts=explode("//",$cmdo);
				$cmdonew="";
				foreach($cmdoslipts as $cmdoslipto){
					if($cmdonew=="")
						$cmdonew=$cmdoslipto;
					else
						$cmdonew.="//".$cmdoslipto;
					if((substr_count($cmdonew,"'")+substr_count($cmdonew,"\""))%2==0){
						break;
					}
				}
				$cmdo=$cmdonew;
		}
		*/
		if(trim($cmdo)=='') continue;
		
		if(substr($cmdo,0,2)==">>"){ 
			$cmdo=trim(substr($cmdo,2));
			//result:
			$var_result='$hlc_result';
			$mret=preg_match('/^.*({result:(.*?)}).*$/',$cmdo,$matches);
			if($mret){
				$var_result="$".$matches[2];
				$cmdo=str_replace($matches[1],'',$cmdo);
			}
			//timeout deal way:空 ctrl+c return 
			$var_tdeal=$info['tdeal'];
			$mret=preg_match('/^.*({tdeal:(.*?)}).*$/',$cmdo,$matches);
			if($mret){
				$var_tdeal=$matches[2];
				$cmdo=str_replace($matches[1],'',$cmdo);
			}
			//error deal way
			$var_edeal=$info['edeal'];
			$mret=preg_match('/^.*({edeal:(.*?)}).*$/',$cmdo,$matches);
			if($mret){
				$var_edeal=$matches[2];
				$cmdo=str_replace($matches[1],'',$cmdo);
			}
			
			//获取变量列表.变量里不能有空格,{,},\,开头还不能是数字
			$cmdbefore=$cmdo;
			$argstring='$hlc_args=array();';
			$mret=preg_match_all('/({\$([^\d\s\[\]\{\}\\\][^\s\[\]\{\}\\\]*?)})/',$cmdbefore,$matches);
			if($mret){
				for($i=0;$i<count($matches[0]);$i++){
					$varstring=$matches[2][$i];
					$argstring.='$hlc_args["'.$varstring.'"]=$'.$varstring.';';
					$cmdbefore=str_replace($matches[1][$i],'', $cmdbefore );
				}
			}
			
			$cmdo="{
			".$argstring.'$hlc_args=array_merge($hlc_args,$info["envir"]);'."
			".$var_result."=auto_cmd(\$info,'".str_replace("'","\'",str_replace("\\","\\\\",$cmdo))."',\$hlc_args);
			if(".$var_result."['match']=='eof'){
				return;
			}
			if(".$var_result."['match']=='timeout'){
				if(preg_match('/^return( +\d+)?$/','".$var_tdeal."',\$m)){
					".$var_tdeal.";
				}elseif('".$var_tdeal."'=='ctrl+c'){
					auto_cmd(\$info,'".chr(3).chr(127)."');
				}
			}
			if(".$var_result."['error']!=''){
				if(preg_match('/^return( +\d+)?$/','".$var_edeal."',\$m)){
					".$var_edeal.";
				}elseif('".$var_edeal."'=='ctrl+c'){
					auto_cmd(\$info,'".chr(3).chr(127)."');
				}
			}
			}";
		}
		$scriptnew.="\n".$cmdo;
	}
	//echo $scriptnew;
	$info['scripttext']=$scriptnew;
	
}



function auto_exec(&$info){
	//keepty:空:执行并关闭。1:执行并保持 2:登录并保持 3:只连接不登录
	if($info['code']!='0' or $info['keepty']=='2' or $info['keepty']=='3'){
		if($info['keepty']==''){//只有在keepty为空时，才会关闭pty
			@fclose($info['pty']);
			$info['pty']="";
		}
		return;
	}
	//echo $info['scripttext'];
	$evalreturncode=eval($info['scripttext']);
	if($evalreturncode!=''){
		$info['code']=$evalreturncode;
	}
	$info['strs'].=$returnmsg;
	if($info['keepty']==''){
		@fclose($info['pty']);
		$info['pty']="";
	}
}




function auto_cmd(&$info,$cmd,$args){ 
	if(!$info)return;
	$realcmdo=trim($cmd) ;
	$listo=array();
	
	
	//{quote:}	
	$randmd5=array();
	$mret=preg_match('/^.*({quote:(.*?)}).*$/',$realcmdo,$matches);
	if($mret){
		$realcmdo=str_replace($matches[1],'',$realcmdo);
		//搜索单引号和双引号，替换为随机数字的md5，最后在替换回来
		$mret=preg_match_all('/((\'.*?\')|(".*?"))/',$realcmdo,$matches);
		if($mret){
			for($i=0;$i<count($matches[0]);$i++){
				
				if($matches[2][$i]!=''){//单引号
					$randone=array(md5(rand()),$matches[2][$i]);
					$realcmdo=str_replace($randone[1],$randone[0],$realcmdo);
					$randmd5[]=$randone;
				}
				if($matches[3][$i]!=''){//双引号
					$randone=array(md5(rand()),$matches[3][$i]);
					$realcmdo=str_replace($randone[1],$randone[0],$realcmdo);
					$randmd5[]=$randone;
				}
			}
		}
	}
	//替换变量
	$mret=preg_match_all('/({\$([^\d\s\[\]\{\}\\\][^\s\[\]\{\}\\\]*?)})/',$realcmdo,$matches);
	if($mret){
		for($i=0;$i<count($matches[0]);$i++){
			$varstr=$matches[2][$i];
			$varstrs=$matches[1][$i];
			$realcmdo=str_replace($varstrs,$args[$varstr], $realcmdo );
		}
	}
	//恢复引号
	foreach($randmd5 as $randone){
		$realcmdo=str_replace($randone[0],$randone[1],$realcmdo);
	}
	
	
	
	$randmd5=array();
	//搜索正则表达式，替换为随机数字的md5，最后在替换回来
	$mret=preg_match_all('/[|:](\/.*?[^\\\]\/[imsxeAZU]{0,9})[|}]/',$realcmdo,$matches);	
	if($mret){
		for($i=0;$i<count($matches[0]);$i++){
			$randone=array();
			if($matches[1][$i]!=''){
				$randone[0]=md5(rand());
				$randone[1]=$matches[1][$i];
				$realcmdo=str_replace($randone[1],$randone[0],$realcmdo);
				$randmd5[]=$randone;
			}
		}
	}
			
	//{match:}	
	$mret=preg_match('/^.*({match:(.*?[^\\\])}).*$/',$realcmdo,$matches);
	if($mret){
		$listo['match']=auto_dealwithtpf($matches[2]);
		foreach($randmd5 as $randone){
			$listo['match']=str_replace($randone[0],$randone[1],$listo['match']);
		}
		$realcmdo=str_replace($matches[1],'',$realcmdo);
	}
	//{error:}		
	$mret=preg_match('/^.*({error:(.*?[^\\\])}).*$/',$realcmdo,$matches);
	if($mret){
		$listo['var_error']=auto_dealwithtpf($matches[2]);
		foreach($randmd5 as $randone){
			$listo['var_error']=str_replace($randone[0],$randone[1],$listo['var_error']);
		}
		$realcmdo=str_replace($matches[1],'',$realcmdo);
	}
	//{save:}	
	$mret=preg_match('/^.*({save:(.*?)}).*$/',$realcmdo,$matches);
	if($mret){
		$listo['var_save']=$matches[2];
		$realcmdo=str_replace($matches[1],'',$realcmdo);
	}
	//{save:}	
	$mret=preg_match('/^.*({tag:(.*?)}).*$/',$realcmdo,$matches);
	if($mret){
		$listo['var_tag']=$matches[2];
		$realcmdo=str_replace($matches[1],'',$realcmdo);
	}
	//{time:}	
	$mret=preg_match('/^.*({time:(.*?)}).*$/',$realcmdo,$matches);
	if($mret){
		$listo['var_time']=$matches[2];
		$realcmdo=str_replace($matches[1],'',$realcmdo);
	}
	//{enter:}	
	$mret=preg_match('/^.*({enter:(.*?)}).*$/',$realcmdo,$matches);
	if($mret){
		$enterlist["\\n"]="\n";$enterlist["\\r"]="\r";$enterlist["\\r\\n"]="\r\n";
		$listo['var_enter']=$enterlist[$matches[2]];
		$realcmdo=str_replace($matches[1],'',$realcmdo);
	}

	$listo['cmd']=$realcmdo;
	
	fwrite ($info['pty'],trim($realcmdo).($listo['var_enter']?:$info['enter']));
	
	$needtpf=$listo['match']?:$info['match'];
	if($needtpf=='prompt'){//使用上次返回的最后一行
		$needtpf=$info['lastline'];
	}

	$waitres=auto_fwait($info['pty'],trim($realcmdo),explode('‖',$needtpf),$listo['var_time']?:$info['time'],$info['coding'],explode('‖',$listo['var_error']."‖".$info['error']) );
	
	$info['strs'].=$waitres["read"];
	
	$readarr=explode("\n",$waitres["read"]);
	$info['lastline']=trim($readarr[count($readarr)-1]);
	$writestring=trim(join("\n",array_slice($readarr,1,count($readarr)-2) ) );
	$var_result=array();
	$var_result['match']=$waitres["match"];
	$var_result['error']=$waitres["error"];
	$var_result['text']=$writestring;
	$var_result['prompt']=$info['lastline'];
	
	//把命令和返回值分别存放在一个返回的数组里 2018/6/27 hlcadd
	$info['cmds'][]=array("cmd"=>trim($realcmdo),"res"=>$var_result);
	//print_r($info);
	
	if($listo['var_tag']!=''){
		$info['cmdm'][$listo['var_tag']]=array("cmd"=>trim($realcmdo),"res"=>$var_result);
	}
	
	if($waitres["index"]=='-2' or $waitres["index"]=='-3'){
		$info['strs'].="\nsdmsg: exec cmd eof\n";//maybe exec 'exit'
		return $var_result;
	}else if($waitres["index"]=='-1'){//timeout
		$info['strs'].="\nsdmsg: exec cmd timeout\n";
		return $var_result;
	}else if($waitres["error"]!=''){//errorstr
		$info['strs'].="\nsdmsg: exec cmd error\n";
		return $var_result;
	}
					
	if($listo['var_save']!=''){
		file_put_contents('/tmp/'.basename($listo['var_save']),$writestring);
		exec("sudo mv ".'/tmp/'.basename($listo['var_save'])." ".$listo['var_save']);
	}
	
	return $var_result;
}



function auto_dealwithtpf($str){
	$str=str_replace('\{','{',$str);
	$str=str_replace('\}','}',$str);
	$str=str_replace('\|','卐',$str);
	$str=str_replace('|','‖',$str);
	$str=str_replace('卐','|',$str);
	return $str;
}



function auto_fwait($pipe,$cmd,$waitarr,$timeout,$coding,$errorarr=array()){
	//这个对stream_select没影响，但有时stream_select选中，fread却还是堵塞住
	//比如访问windows，执行dir命令时，具体原因不明
	stream_set_blocking($pipe, 0);
	//stream_encoding($pipe,'utf-8');
	//0:timeout -1:close 1:eof >1:match <-1:error
	$matchindex="";
	$matchtext="";
	$errorindex="";
	$errortext="";
	$longstr="";
	$nowtime=explode(' ', microtime())[1];

	while(1){
		$pipes[0]=$pipe;
		stream_select($pipes, $write=NULL, $except=NULL, 2);
		if($pipes[0]){
			
			$str=fread($pipes[0],8192);
			while(1){
				usleep(10);
				$s=fread($pipes[0],8192);
				if(strlen($s)==0){
					break;
				}else{
					$str.=$s;
				}
			}
			//echo "{{{".$str."}}}";
			$str=str_replace("\0","",$str);//即使设置了页码，win还是需要去除\0
			//$str=preg_replace('/[\x00-\x09]/',"",$str);
			//$str=preg_replace('/[\x0e-\x1f]/',"",$str);
			
			if($str===false){
				$matchindex="-3";
				$matchtext="close";
			}
			
			//exit断开连接时，会走到这里,可读，但读到空字符串
			if(feof($pipes[0])){
				$matchindex="-2";
				$matchtext="eof";
			}
			
			//   /[\x{4e00}-\x{9fa5}]+/u   utf-8的汉字匹配
			//   /[".chr(0xa1)."-".chr(0xff)."]+/
			//if($coding!='' and $coding!='UTF-8' and $coding!='utf-8'){
			//	$str=iconv($coding, 'UTF-8//IGNORE', $str);
			//}
			if(preg_match_all("/(\033\[(\d*[ABCDEFGJKSTnsuHm]|\d*;\d*[Hm]))+/",$str,$matchs)){
				foreach($matchs[0] as $matcho){
					if(strstr($matcho,"H")){
						$str=str_replace($matcho,"\r\n",$str);
					}else{
						$str=str_replace($matcho,"",$str);
					}
				}
			}
			if(preg_match_all("/(([\xa1-\xa9]|[\xb0-\xf7])[\xa1-\xfe])/",$str,$matchs)){
				foreach($matchs[0] as $matcho){
					//多个汉字时,iconv可能不会出错，str_replace却可能出错
					//所以现在用的是单个汉字逐一转换并替换
					$str=str_replace($matcho,iconv( 'GB2312','UTF-8//IGNORE', $matcho),$str); 
				}
			}

			$longstr.=$str;
			
			if($cmd!=''){//有命令的话，去除命令行
				if(!strstr($longstr,"\n")) continue;
				$searchstr=trim(explode("\n",$longstr,2)[1]);
			}else{
				$searchstr=trim($longstr);
			}
			
			
			//echo "<xmp>{{{".$searchstr."}}}</xmp>";
			$askformore=false;
			if(stristr(substr($searchstr,-20),'-- More --') or stristr(substr($searchstr,-20),'--More--') ){
				$mret=preg_match('/\n(.*?(?:-- More --|--More--).*)/',$longstr,$matches);
				//echo "{{{{{".$matches[1]."}}}}}";
				$longstr=str_ireplace($matches[1],"",$longstr);
				fwrite($pipes[0] ,' ');
				continue;
			}
			//check for match
			foreach($waitarr as $wkey=>$wval){
				if ($wval=='') continue;
				if((substr($wval,0,1)=='/' and preg_match($wval,$searchstr,$matches)) or substr($searchstr,strlen($wval)*-1)==$wval){
					$matchindex=(string)$wkey;
					$matchtext=$wval;
					break;
				}
			}
			
			//check for error search
			foreach($errorarr as $ekey=>$eval){
				if ($eval=='') continue;
				if((substr($eval,0,1)=='/' and preg_match($eval,$searchstr,$matches)) or stristr($searchstr,$eval) ){
					$errorindex=(string)$ekey;
					$errortext=$eval;
					break;
				}
			}			

		}

		if($timeout and ((int)(explode(' ', microtime())[1])-(int)$nowtime)> (int)$timeout ){
			$matchindex="-1";
			$matchtext="timeout";
		}
		
		if($matchindex!=''){
			break;
		}
	}


	$res["index"]=$matchindex;
	$res["match"]=$matchtext;
	$res["error"]=$errortext;
	$res["read"]=$longstr;
	return $res;
}




$string='
>>cd /

>>wwww {error:^|/ggfg\/gd|h{2,6}gg/s|error|/ho{ss}n|g/isxAU|command not found} 

$ind="/root";
>>cd /{$ind} {match:$|\}|#} //表示该命令返回匹配 $ 或 # 为通配符

>>ls {match:last} //表示使用前一个命令返回的最后一行作为通配符

>>ls {save:/tmp/tmp} //表示该命令结果存储在文件/tmp/tmp里

>>ls -all /srv{result:lsall}//把命令的结果放到 lsall 变量里

$lsallarr=explode("\n",$lsall["text"]);//将上面命令的返回结果分解成行数组lsallarr

for( $i=0 ; $i<count($lsallarr) ; $i++ ){//循环读上面的数组
	if($i==0){
		$lsallarro=$lsallarr[$i];
		>> echo {$i} {$lsallarro}
		continue;
	}elseif($i==1){
		>> echo {$i}
		continue;
	}elseif($i==2){
		>> echo {$i}
		continue;
	}else{
		>> echo {$i}
	}
	$linearr=explode(" ",trim($lsallarr[$i]));//将每一行按空格分解成数组
	$linename=$linearr[count($linearr)-1];//上面数组最后值放到linename
	
	if($linename==\'\' or trim($linename)==\'.\' or trim($linename)==\'..\'){
		//文件名为空或者 . 或者 .. 都不去查看大小
		continue;
	}
	>>du -sh /srv/{$linename}//查看文件或目录的大小
}

>>date
>>cat dddd

';

/*
$cmdinfo=array();
$cmdinfo['ssh']="2";
$cmdinfo['host']="192.168.0.214";
$cmdinfo['user']="root";
$cmdinfo['pass']="jbgsn!2716888";
$cmdinfo['sucmd']="su - root";
$cmdinfo['supwd']="jbgsn!2716888";

$cmdinfo['time']="5";
$cmdinfo['edeal']="";
$cmdinfo['error']="";
$cmdinfo['edeal']="";
$cmdinfo['debug']=true;

$cmdinfo['script']=$string;




$cmdinfo['ssh']="0";
$cmdinfo['host']="192.168.0.215";
$cmdinfo['user']="administrator";
$cmdinfo['pass']="jbgsn";
$cmdinfo['sucmd']="";
$cmdinfo['script']='
>>dir
>>net user
';

if($_POST['submit']!=''){
	
	$cmdinfo=$_POST;
	$cmdinfo['enter']=array("\n","\r","\r\n")[$cmdinfo['enter']];

	$res=autocheck($cmdinfo);	
	echo $res;
	if($res==''){
		autodisplay($cmdinfo);
		if($cmdinfo['code']=='0'){
			echo "login\n";
			autologin($cmdinfo);
			if($cmdinfo['code']=='0'){
				echo "exec\n";
				autoexec($cmdinfo);
			}
		}
	}

}

echo "<xmp>";
print_r($cmdinfo);
echo "</xmp>";

*/


?>
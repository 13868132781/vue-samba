<?php

if(isset($argv) and count($argv)>1 and $argv[1]=='test'){
	
	$aauto  = new AutoExec(
		[
			'host'=>'192.168.0.202',
			'ssh'=>'0',
			'coding'=>'',
			'enter'=>'',
		
			'user' => '{$backup_user}',
			'pass' => '{$backup_pass}',
			'sucmd' => 'enable',
			'supwd' => 'qqq000,,,',
			'time' => '',
			'tdeal' => '',
			'error' => '',
			'edeal' => '',
			'match' => '#|>',
			'script'=> '>>{result:check}
if($check[\'match\']==">"){
	>>enable {match::}
	>>qqq000,,,{match:>|#|:}{result:enres}
	if($enres[\'match\']!="#"){
		$returnmsg="\nask for privilage level 15 .\n";
		return 10;
	}
}
>>
>>copy running-config tftp://{$radius_ip}{match:?}
>>{match:Destination filename}
>>{$nas_ip}_running.text
>>

>>copy nvram:/startup-config tftp://{$radius_ip}{match:?}
>>{match:Destination filename}
>>{$nas_ip}_startup.text
>>

>>exit
',
		],
		[
			'backup_user'=>'hong',
			'backup_pass'=>'hong',
			'radius_ip'=>'192.168.0.110',
		]
	);
		
	$aauto->run();
		
	echo "\n".$aauto->info['code']."\n";
	//echo "\n".$aauto->info['strs']."\n";
	//echo "\n".$aauto->info['errs']."\n";
		
}

class AutoExec {
	public $envir=[];
	public $info=[
		'host' =>'',
		'port' =>'',
		'ssh' =>'',
		'enter' => "\n",//这里必须用双引号
		'coding' => 'UTF-8',
		
		'user' => '',
		'pass' => '',
		'sucmd' => '',
		'supwd' => '',
		'time' => '15',
		'tdeal' => '2',//0:什么也不做 1:结束本命令 2:结束整个脚本
		'error' => '',
		'edeal' => '',
		'match' => '#‖>‖$‖]',
		
		'script' => '',
		
		////////以上为inputs传入///////////
		
		
		'lastline' => '\n',
		'code'=> '0',
		'errs' => '',
		'strs'=> '',
		
		
		'pty' =>'',
		'keepty' => '',
	];
	
	public $logfile = '';
	public $expectlog = '';
	
	public $errinfo=[
		'-2'=>'timeout',
		'-1'=>'eof',
		'-11'=>'connect failed/closed',//index -11
		'0' => '',
		'1'=>'login failed',
		'2'=>'enable failed',
		'3'=>'cmd failed',
		'10'=>'user custom',
		//'100'=>'unknown err',
	];
	
	public function __construct($inputs=[],$envir=[]){
		$this->make($inputs,$envir);
	}
	
	public function make($inputs=[],$envir=[]){
		$this->envir = $envir;
		$this->info['script'] = $inputs['script'];
		
		$header = [
			'user','pass','sucmd','supwd',
			'time','tdeal','error','edeal','match',
		];
		foreach($header as $o){
			$p = $inputs[$o];
			if($o=='user' and $p==''){$p='{$backup_user}';}
			if($o=='pass' and $p==''){$p='{$backup_pass}';}
			if(trim($p)==''){continue;}
			
			//把类型{$backup_user}替换成环境变量里的数据
			$mret=preg_match('/({\$([^\s\[\]\{\}\\\]*?)})/',$p,$matches);
			if($mret){
				$envirval = '';
				if(array_key_exists($matches[2],$envir)){
					$envirval = $envir[$matches[2]];
				}
				$p=str_replace($matches[1],$envirval,$p);
			}
			$this->info[$o]=trim($p);
		}
		
		$nascol = ['host','ssh','port','coding','enter'];
		foreach($nascol as $o){
			$p = isset($inputs[$o])?$inputs[$o]:'';
			if(trim($p)==''){
				continue;
			}
			$this->info[$o]=trim($p);
		}
		
		$this->info['match'] = $this->dealwithtpf($this->info['match']);
		
		if ($this->info['port']==''){
			if($this->info['ssh']=="0"){
				$this->info['port']="23";
			}else{
				$this->info['port']="22";
			}
		}
		
		$this->logfile = '/tmp/phpexpectlog_'.$this->rander().".log";
	
		ini_set("expect.timeout", 10);
		ini_set("expect.loguser", "On");
		ini_set("expect.logfile", $this->logfile);
		exec("echo '' > ".$this->logfile);
		
		//$this->output=fopen('php://output','w');
		//$this->stdout=fopen('php://stdout','w');
	}
	
	public function check($script){
		$scriptarrs=explode("\n",$script);
		$scriptnew="";
		foreach($scriptarrs as $kay=>$cmd){
			$cmdo=trim($cmd);
			if(substr($cmdo,0,2)==">>"){
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

	public function run(){
		$this->start();
		$this->finish();
		@fclose($info['pty']);
		exec("rm ".$this->logfile);
	}
	
	public function start(){	
		$info = &$this->info;
		
		$this->display();
		
		$this->login();
		if($info['code']!='0'){
			return;
		}
		
		$this->enable();
		if($info['code']!='0'){
			return;
		}
		
		$this->doexec();
	}
	
	public function finish(){
		$info = &$this->info;
		$info['errs']='';
		if($info['code']!='0'){
			$info['errs']=$this->errinfo[$info['code']];
			$info['strs'].="\nsdmsg: ".$info['errs']."\n";
		}
	}
	
	public function display(){
		
		$info = &$this->info;
		$script = $info['script'];
		
		$scriptarrs=explode("\n",$script);
		$scriptnew="";
		foreach($scriptarrs as $kay=>$cmd){
			$cmdo=trim($cmd);
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
				//timeout deal val:0 1 2 
				$var_tdeal=$info['tdeal'];
				$mret=preg_match('/^.*({tdeal:(.*?)}).*$/',$cmdo,$matches);
				if($mret){
					$var_tdeal=$matches[2];
					$cmdo=str_replace($matches[1],'',$cmdo);
				}
				//error deal val:0 1 2
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
						if(isset($$varstring)){
							$argstring.='$hlc_args["'.$varstring.'"]=$'.$varstring.';';
						}
						$cmdbefore=str_replace($matches[1][$i],'', $cmdbefore );
					}
				}
				
				$cmdo="{
				".$argstring.'$hlc_args=array_merge($hlc_args,$this->envir);'."
				".$var_result."=\$this->cmd('".str_replace("'","\'",str_replace("\\","\\\\",$cmdo))."',\$hlc_args);
				if(\$info['code']=='-1'){//eof
					return;
				}
				if(\$info['code']=='-11'){//closed
					\$info['code']= '0';
					return;
				}
				if(\$info['code']=='-2'){//timeout
					if('".$var_tdeal."'=='2'){//end script
						return;
					}else if('".$var_tdeal."'=='1'){//end cmd
						\$this->cmd('".chr(3).chr(127)."');
					}
				}
				if(\$info['code']=='3'){//cmd failed
					if('".$var_edeal."'=='2'){
						return;
					}else if('".$var_edeal."'=='1'){
						\$this->cmd('".chr(3).chr(127)."');
					}
				}
				}";
			}
			$scriptnew.="\n".$cmdo;
		}
		//echo $scriptnew;
		$info['scripttext']=$scriptnew;
		
	}


	function login(){
		$info = &$this->info;
		
		if($info['ssh']=="0"){
			$startexe="telnet ".$info['host']." -l ".$info['user']." ".$info['port'];
		}else{
			$startexe="ssh -".$info['ssh']." ".$info['host']." -l ".$info['user']." -p ".$info['port'];
		}
		
		$info['strs'].= $startexe."\n";
		//下面这个很奇怪，放不同地方，表现不同
		//它会屏蔽掉expect输出到终端的信息
		//有时能echo，有时echo也会屏蔽
		//有时程序会中断
		//fclose(STDOUT);
		//$STDOUT = fopen('/dev/null', 'a');
		$ptyfp = expect_popen($startexe);
		$info['pty']=$ptyfp;

		
		if (!is_resource($ptyfp)) {
			$info['code']='8';
			$info['errs']="open error: ptyfp:".$ptyfp;
			$info['strs'].="\nsd msg:\n";
			return;
		}
			
			//login
		$userasked=false;
		$passasked=false;
		while(true){
			//ogin:会跟Linux登陆信息冲突，暂时不用
			$needtpf="(yes/no)?‖(yes/no/[fingerprint])?‖ogin:‖sername:‖assword:‖".$info['match'];
			$waitres=$this->fwait($ptyfp,'',explode('‖',$needtpf),$info['time'],$info['coding']);
			$info['strs'].=$waitres["read"];
			//echo "{{{{{".$waitres["index"]."}}}}}";
			
			if($waitres["index"]<0 ){
				$info['code']=$waitres["index"];
				return;
				
			}else if($waitres["index"]=='0' or $waitres["index"]=='1'){//yes/no
				fwrite ($ptyfp, "yes".$info['enter']);
			
			}else if($waitres["index"]=='2' or $waitres["index"]=='3'){//请求用户
				if(!$userasked){
					fwrite ($ptyfp, $info['user'].$info['enter']);
					$userasked=true;
				}else{
					$info['code']='1';
					return;
				}

			}else if($waitres["index"]=='4'){//请求密码
				if(!$passasked){
					//echo "[[[".$info['pass'].$info['enter']."]]]";
					fwrite ($ptyfp, $info['pass'].$info['enter']);
					//fwrite ($ptyfp, "qqq000,,,\n");
					$passasked=true;
				}else{
					$info['code']='1';
					return;
				}
					
			}else{//匹配到match里的 $ # ] 等字符
				$info['code']='0';
				//logined and get first tpf
				$lastlinearr=explode("\n",$waitres['read']);
				$info['lastline']=end($lastlinearr);
				
				
				
				
				if(stristr( $info['strs'],"Welcome to Microsoft Telnet Service")){
					$info['coding']="GB2312";
				}
				
				return;
				
			}
		
		}
	}	
		
	
	public function enable(){
		$info = &$this->info;
		$ptyfp = $info['pty'];
		
		if($info['sucmd']=='' or $info['supwd']==''){
			return;
		}
		$passasked=false;
		fwrite ($ptyfp, $info['sucmd'].$info['enter']);	
		while(true){
			$needtpf="assword:‖".$info['match'];
			$waitres=$this->fwait($ptyfp,$info['sucmd'],explode('‖',$needtpf),$info['time'],$info['coding']);
			
			$info['strs'].=$waitres["read"];
				
			if(array_key_exists('error',$waitres) and $waitres["error"]!=''){
				$info['code']='35';
				$info['errs']=$waitres["error"];
				$info['strs'].="\nsdmsg: ".$waitres["error"]."\n";
				return;
			}
			
			if($waitres["index"]<0 ){
				$info['code']=$waitres["index"];
				return;
				
			}else if($waitres["index"]=='0'){
				if(!$passasked){	
					fwrite ($ptyfp, $supwds[0].$info['enter']);
					$passasked=true;
				}else{
					$info['code']='2';
					return ;
				}
						
			}else{//成功
				$info['code']='0';
				//logined and get first tpf
				$lastlinearr=explode("\n",$waitres['read']);
				$info['lastline']=end($lastlinearr);
			
				return;
			}
		}
	}




	public function doexec(){
		$info = &$this->info;
		
		file_put_contents("/tmp/testphp.php","<?php\n".$info['scripttext']);
		$returnmsg='';
		$evalreturncode=eval($info['scripttext']);
		if($evalreturncode!=''){
			$info['code']='10';
			//$info['errs']=$returnmsg;
		}
		$info['strs'].=$returnmsg;
	}




	public function cmd($cmd,$args=[]){ 
		$info = &$this->info;
		
		$realcmdo=trim($cmd) ;
		$listo=array(
			'var_match'=>$info['match'],
			'var_enter'=>$info['enter'],
			'var_time'=>$info['time'],
			'var_tdeal'=>$info['tdeal'],
			'var_error'=>$info['error'],
			'var_edeal'=>$info['edeal'],
			'var_save'=>'',
			'var_tag'=>'',
			'var_cmd'=>'',
		);
		
		
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
				$varval = '';
				if(array_key_exists($varstr,$args)){
					$varval = $args[$varstr];
				}
				$realcmdo=str_replace($varstrs,$varval, $realcmdo );
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
			$listo['var_match']=$this->dealwithtpf($matches[2]);
			foreach($randmd5 as $randone){
				$listo['var_match']=str_replace($randone[0],$randone[1],$listo['var_match']);
			}
			$realcmdo=str_replace($matches[1],'',$realcmdo);
		}
		//{error:}		
		$mret=preg_match('/^.*({error:(.*?[^\\\])}).*$/',$realcmdo,$matches);
		if($mret){
			$listo['var_error']=$this->dealwithtpf($matches[2]);
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
		
		fwrite ($info['pty'],trim($realcmdo).$listo['var_enter']);
		
		$needtpf=$listo['var_match'];
		if($needtpf=='prompt'){//使用上次返回的最后一行
			$needtpf=$info['lastline'];
		}

		$waitres=$this->fwait($info['pty'],trim($realcmdo),explode('‖',$needtpf),$listo['var_time'],$info['coding'],explode('‖',$listo['var_error']) );
		//echo '[[[[[['.$waitres["read"].']]]]]]';
		$info['strs'].=$waitres["read"];
		
		$readarr=explode("\n",$waitres["read"]);
		$info['lastline']=trim($readarr[count($readarr)-1]);
		$writestring=trim(join("\n",array_slice($readarr,1,count($readarr)-2) ) );
		$var_result=array();//这个变量只给用户使用，我这里没用
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
		
		if($waitres["index"]<'0'){
			$info['code']=$waitres["index"];//eof or timeout or closed
			return $var_result;
		
		}else if($waitres["error"]!=''){//errorstr
			$info['code']='3';
			return $var_result;
		}
						
		if($listo['var_save']!=''){
			file_put_contents('/tmp/'.basename($listo['var_save']),$writestring);
			exec("sudo mv ".'/tmp/'.basename($listo['var_save'])." ".$listo['var_save']);
		}
		
		return $var_result;
	}



	public function dealwithtpf($str){
		$str=str_replace('\{','{',$str);
		$str=str_replace('\}','}',$str);
		$str=str_replace('\|','卐',$str);
		$str=str_replace('|','‖',$str);
		$str=str_replace('卐','|',$str);
		return $str;
	}



	public function fwait($pipe,$cmd,$waitarr,$timeout,$coding,$errorarr=array()){
		$cases = [];
		for($i=0;$i<count($waitarr);$i++){
			if($waitarr[$i]=='')continue;
			if(substr($waitarr[$i],0,1)=='/' and substr($waitarr[$i],-1,1)=='/'){
				$cases[]=array(substr($waitarr[$i],1,-1),$i);
			}else{
				$cases[]=array(preg_quote($waitarr[$i]),$i);
			}
		}
		for($i=0;$i<count($errorarr);$i++){
			if($errorarr[$i]=='')continue;
			if(substr($errorarr[$i],0,1)=='/' and substr($errorarr[$i],-1,1)=='/'){//前后有斜杆，表示正则
				$cases[]=array(substr($errorarr[$i],1,-1),$i+100);
			}else{
				$cases[]=array(preg_quote($errorarr[$i]),$i+100);
			}
		}
		//print_r($cases);
		//expect_expectl输出始终没法屏蔽
		//一旦fclose(STDOUT),正常echo能显示
		//但却不能传到sdthread那里的父进程了
		$matchindex='';
		$longstr='';
		$lastoutlog='';
		while(true){
			$matchindex = @expect_expectl($pipe, $cases, $match);
			$longstr = $lastoutlog.$this->outlog();
			$lastoutlog = $longstr;
			if($cmd!=''){//有命令的话，去除命令行
				$lstrs=explode("\n",$longstr,2);
				if(count($lstrs)>1){
					$searchstr=trim($lstrs[1]);
				}else{
					$searchstr=trim($longstr);
				}
			}else{
				$searchstr=trim($longstr);
			}
			//检查一些特殊情况，有待改善
			if( $matchindex>0 and $matchindex<100 ){
				$ppf = $waitarr[$matchindex];
				$reads = explode($ppf,$searchstr);
				//if(count($reads)<2){
				//	echo '==='.$ppf.'=='.$longstr.'===';
				//}
				if(strlen($ppf)==1 and count($reads)>1 and trim($reads[1])!=''){
					//单个匹配符，但后面还有字符
					continue;
				}
				if(substr(trim($reads[0].$ppf),-11)=="Last login:"){
					continue;
				}
			}
			break;
		}
		
		$res=[];
		
		$res["index"]=$matchindex;
		$res["match"]='';
		$res["error"]='';
		if($matchindex >= 100){
			$res["error"]=$errorarr[$matchindex-100];
		}else if($matchindex > 0){
			$res["match"]=$waitarr[$matchindex];
		}
		$res["read"]=$longstr;
		return $res;
		
		
	}
	
	
	public function fwait_old($pipe,$cmd,$waitarr,$timeout,$coding,$errorarr=array()){
		
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


	function outlog(){
		$expectloga = file_get_contents($this->logfile);
		$expectlogo = str_replace($this->expectlog,'',$expectloga);
		//echo "[[[[".$expectlogo."]]]]";
		$this->expectlog = $expectloga;
		return $expectlogo;
	}
	
	

	function rander(){
		$chars = array(
         "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
         "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
         "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
         "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
         "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
         "3", "4", "5", "6", "7", "8", "9"
		);
		shuffle($chars);
		return implode("",$chars);
	}
	
	
	
}
?>
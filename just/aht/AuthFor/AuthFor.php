<?php



class AuthStep{
	protected $args=[];
	
	protected $code = 0;// -1 0 1
	protected $msg = '';
	protected $msghz = '';
	protected $backlist = [];
	
	protected $rowUser = [];
	protected $rowNas = [];
	protected $rowPerm = [];
	protected $authway = '';
	
	protected $pass1 = '';
	protected $pass2 = '';
	
	protected $morechallenge = true;//是否允许多次请求一次口令 
	protected $deblx = '';//OTP\SMS，提示输入里
	
	protected $step=[];
	
	protected $nasTemp=[];
	
	public function __construct($opt){
		$this->args=$opt;
	}
	
	
	public function tacGetKey(){
		$msg = '';
		$nasRow = $this->getNasInfo();
		if($nasRow){
			$msg = $nasRow['na_secret'];
		}
		
		$this->code = 0;
		$this->msg = $msg;
		$this->outArray(0);
	}
	
	public function tacAuthen(){
		
		$this->queryForUser();//查询user信息
		$this->queryForNas();//查询nas信息
		$this->queryForPerm();//查询角色信息
		$this->queryForWay();//查询认证方式
		
		$this->checkForActive(); //检查用户状态
		$this->checkForClientid(); //检查用户对应的客户端标识是否正确
		$this->checkForIp(); //检查用户对应客户端IP是否正确
		$this->checkForShid();//检查时限
		$this->checkForLimit();//检查限制组
		
		$this->enable();//enable认证
		$this->displayPassword(); //分解密码
		$this->makestep(); //制定认证步骤
		$this->execstep(); //执行认证步骤
		
		$this->outArray(1);
	}
	
	public function tacAuthor(){
		$this->queryForUser();//查询user信息
		$this->queryForNas();//查询nas信息
		$this->queryForPerm();//查询角色信息
		
		$this->authForCmd();
		
		$this->writeForReply();//写用户自定义返回属性
		$this->writeForAttrTac();//写角色属性
		
		$this->outArray(0);	
	}
	
	public function radAuthen(){
		
		$this->queryForUser();//查询user信息
		$this->queryForNas();//查询nas信息
		$this->queryForPerm();//查询角色信息
		$this->queryForWay();//查询认证方式
		
		$this->checkForActive(); //检查用户状态
		$this->checkForClientid(); //检查用户对应的客户端标识是否正确
		$this->checkForIp(); //检查用户对应客户端IP是否正确
		$this->checkForShid();//检查时限
		$this->checkForLimit();//检查限制组
		
		$this->displayPassword(); //分解密码
		$this->makestep(); //制定认证步骤
		$this->execstep(); //执行认证步骤
		
		$this->writeForReply();//写用户自定义返回属性
		$this->writeForAttrRad();//写角色属性
		
		$this->outArray(1);
	}
	
	public function radAuthor(){
		$this->outArray(0);
	}
	
	
	
	
	
	
	
	
	
	protected function queryForUser(){
		$permrow = \DB::table("sdaaa.raduser")
		->cryptField('us_passval')
		->where('us_user',$this->args['user'])
		->first();
		
		if(!$permrow){
			$this->msg ='unknown username';
			$this->msghz = '未知的用户名：'.$this->args['user'];
			$this->code = 1;
			$this->outArray(1);	
		}
		$this->rowUser = $permrow;
	}
	
	protected function queryForNas(){
		$permrow = $this->getNasInfo();
		
		if(!$permrow){
			$this->msg ='unknown nas';
			$this->msghz = '未知的设备：'.$this->args['nas'];
			$this->code = 1;
			$this->outArray(1);	
		}
		$this->rowNas = $permrow;
	}
	
	protected function queryForPerm(){
		$gpid = $this->rowUser['us_gpid'];
		$ip = $this->rowNas['na_ip'];
		$organ = $this->rowNas['na_organ'];
		
		$perm = \DB::table("sdaaa.perm")
		->where('gpid',$gpid)
		->first();
		if(!$perm){//没选或者选中已删除，就取默认
			$perm = \DB::table("sdaaa.perm")
			->where('gp_default','1')
			->first();
			$gpid = $perm['gpid'];
		}
		$permNas = \DB::table("sdaaa.perm_nas")
		->where('gpn_gpid',$gpid)
		->where('gpn_naip',$ip)
		->first();
		
		$nasOrgan = $this->rowNas['na_organ'];
		$permOrgan = \DB::table("sdaaa.perm_organ")
		->where('gpo_gpid',$gpid)
		->where('gpo_onid',$nasOrgan)
		->first();
		
		
		$permAll = array_merge($perm?:[],$permOrgan?:[],$permNas?:[]);
		
		$result=[];
		$qzs=['gpn','gpo','gp'];
		$hzs=['check','attr','cmd','shid','limit'];
		
		foreach($hzs as $hz){
			$result[$hz] = 0;
			foreach($qzs as $qz){
				$col = $qz."_".$hz;
				if(isset($permAll[$col]) and strlen($permAll[$col])>0 and $permAll[$col]!='-1'){
					$res = $permAll[$col];
					$result[$hz] = $permAll[$col];
					break;
				}	
			}
		}
		//print_r($perm);
		//print_r($permOrgan);
		//print_r($permNas);
		//print_r($result);
		$this->rowPerm = $result;
	}
	
	
	
	protected function queryForWay(){
		$obj = \DB::table("sdaaa.authway");
		if($this->rowUser['us_tfa']){
			$obj->where("awid",$this->rowUser['us_tfa']);
		}else{
			$obj->where('aw_default','1');
		}
		$authway = $obj->value('aw_key');
		
		if(!$authway){
			$this->msg ='can not find auth way';
			$this->msghz = '未找到对应的认证方式';
			$this->code = 1;
			$this->outArray(1);
		}
		$this->authway = $authway?:'PAP';
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	protected function checkForActive(){
		if($this->rowUser['us_active']=='0'){
			$this->msg ='user active is down';
			$this->msghz ='用户已被停用';
			$this->code = 1;
			$this->outArray(1);	
		}else if($this->rowNas['na_active']=='0'){
			$this->msg ='nas active is down';
			$this->msghz ='设备已被停用';
			$this->code = 1;
			$this->outArray(1);
		}
	}
	
	protected function checkForClientid(){
		if($this->rowUser['us_rad_clientid']!='' and $this->args['nac']!=$this->rowUser['us_rad_clientid']){
			$this->msg ='Calling-Station-Id wrong';
			$this->msghz = 'Calling-Station-Id属性不合法';
			$this->code = 1;
			$this->outArray(1);
		}
	}
	
	protected function checkForIp(){
		$checkid = $this->rowPerm['check'];
		$user = $this->args['user'];
		$nas = $this->args['nas'];
		if($checkid==0){
			$this->msg =$user.' forbid to login '.$nas;
			$this->msghz = $user.'无权登录设备'.$nas;
			$this->code = 1;
			$this->outArray(1);
		}
	}
	
	protected function checkForShid(){
		$shidid = $this->rowPerm['shid'];
		if($shidid=='' or $shidid=='0'){
			return;
		}
		
		
		$row= \DB::table("sdaaa.permshid")
		->where('gsid',$shidid)
		->first();
		
		if(!$row){
			$this->msg = 'no period policy for id '.$shidid;
			$this->msghz = '无效的时限id号 '.$shidid; 
			$this->code = 1;
			$this->outArray(1);
		}
		if($row['gs_day']!=''){
			$list=$this->shiddisply($row['gs_day']);
			if(count($list)>0){
				$shij = date('G');
				if(!array_key_exists($shij,$list)){
					$this->msg = 'login in forbid hours of day';
					$this->msghz = '今天禁止登录';
					$this->code = 1;
					$this->outArray(1);
				}
			}
		}
		if($row['gs_week']!=''){
			$list=$this->shiddisply($row['gs_week']);
			if(count($list)>0){
				$shij = date("w");
				if($shij==0){$shij=7;}
				if(!array_key_exists($shij,$list)){
					$this->msg = 'login in forbid days of week';
					$this->msghz = '一周内今天禁止登录';
					$this->code = 1;
					$this->outArray(1);
				}
			}
		}
		if($row['gs_month']!=''){
			$list=$this->shiddisply($row['gs_month']);
			if(count($list)>0){
				$shij = date("j");
				if(!array_key_exists($shij,$list)){
					$this->msg = 'login in forbid days of month';
					$this->msghz = '本月禁止登录';
					$this->code = 1;
					$this->outArray(1);
				}
			}
		}
		if($row['gs_year']!=''){
			$list=$this->shiddisply($row['gs_year']);
			if(count($list)>0){
				$shij = date("Y");
				if(!array_key_exists($shij,$list)){
					$this->msg = 'login in forbid years';
					$this->msghz = '本年禁止登录';
					$this->code = 1;
					$this->outArray(1);
				}
			}
		}
	}
	
	protected function checkForLimit(){
		$limitid = $this->rowPerm['limit'];
		
		if($limitid=='' or $limitid=='0'){
			return;
		}
		$urow = $this->rowUser;
		
		$row= \DB::table("sdaaa.permlimit")
		->where('glid',$limitid)
		->first();

		if(!$row){
			$this->msg = 'no limit policy for id '.$limitid;
			$this->msghz = '无效的限制组id '.$limitid;
			$this->code = 1;
			$this->outArray(1);
		}
		if($row['gl_fail_cs'] and is_numeric($row['gl_fail_cs'])){
			$numcs = $row['gl_fail_cs'];
			$numcl = $row['gl_fail_cl'];
			$numn = $urow['us_limit_failnum'];
			$numt = $urow['us_limit_failtime'];
			if($numn>=$numcs){
				if($numcl==''){//无限锁定
					$this->msg = 'lockout forever';
					$this->msghz = '永久锁定';
					$this->code = 1;
					$this->outArray(1);
				}
				if(time()<(strtotime($numt)+$numcl*60)){//还没过锁定时间
					$still = $numcl-floor((time()-strtotime($numt))/60);
					$this->msg = 'still lockout in '.$still.' minute';
					$this->msghz = '依旧锁定'.$still.'分钟';
					$this->code = 1;
					$this->outArray(1);
				}
			}
			
			if($row['gl_login'] and is_numeric($row['gl_login'])){
				$numn = $urow['us_limit_loginnum'];
				if($numn>=$row['gl_login']){
					$this->msg = 'login Times exceeded';
					$this->msghz = '超过了登录次数'; 
					$this->code = 1;
					$this->outArray(1);
				}
			}
			if($row['gl_gq_user'] and is_numeric($row['gl_gq_user'])){
				$numn = $urow['us_limit_usertime'];
				if(time()>(strtotime($numn)+$row['gl_gq_user']*24*60*60)){
					$this->msg = 'user Time expired';
					$this->msghz = '用户过期';
					$this->code = 1;
					$this->outArray(1);
				}
			}
			if($row['gl_gq_pass'] and is_numeric($row['gl_gq_pass'])){
				$numn = $urow['us_limit_passtime'];
				if(time()>(strtotime($numn)+$row['gl_gq_pass']*24*60*60)){
					$this->msg = 'pass Time expired';
					$this->msghz = '密码过期';
					$this->code = 1;
					$this->outArray(1);
				}
			}
		}	
	}
	
	protected function shiddisply($str){
		if($str==''){return [];}
		$ret = [];
		$strs=explode(',',$str);
		foreach($strs as $st){
			$st = trim($st);
			if($st==''){
				continue;
			}
			if(!strstr($st,'-')){
				if(is_numeric($st)){
					$ret[$st]='yes';
				}
				continue;
			}
			$sts = explode('-',$st);
			if(!is_numeric($sts[0]) or !is_numeric($sts[1])){
				continue;
			}
			if($sts[0]> $sts[1]){
				continue;
			}
			for($i=$sts[0];$i<=$sts[1];$i++){
				$ret[$i]='yes';
			}
		}
		return $ret;
	}
	
	protected function enable(){ 
		if($this->args['service']=='2'){//是enable认证
			$nasenable = $this->rowNas['na_tac_enable'];
			$userenable = $this->rowUser['us_tac_enable'];
			echo $nasenable."\n";
			echo $userenable."\n";
			$this->code = 1;
			if($nasenable!='' and $nasenable==$this->args['pass']){
				$this->code = 0;
			}else{
				if($userenable!='' and $userenable==$this->args['pass']){
					$this->code = 0;
				}
			}
			if($this->code == 1){
				$this->msg = "auth failed for enable";
				$this->msghz = 'enable密码认证失败';
			}
			$this->outArray(1);//无论密码正确与否，都结束
		}
	}
	
	protected function displayPassword(){
		$userPassword = $this->args['pass'];
		$oncePassword = $this->args['pass'];
		if(strstr($this->authway,'-')){
			$userpass = substr($this->args['pass'],0 , -6);
			$otppass = substr($this->args['pass'],-6 );
			$userPassword = $userpass;
			$oncePassword = $otppass;
			
		}
		$this->pass1=$userPassword;
		$this->pass2=$oncePassword;
		
	}
	
	
	protected function makestep(){//分析出一个认证步骤来
		$authyuan = $this->authway;
		if(strstr($authyuan,'-')){
			$ats =explode('-', $authyuan);
			$this->step[] = $ats[0];
			$this->step[] = $ats[1];
			
		}else if(strstr($authyuan,'+')){
			$ats =explode('+', $authyuan);
			$state = $this->args['state'];
			if($state==''){//两步中的第一步
				$this->step[] = $ats[0];
				
				if($ats[1]=='SMS'){
					$this->step[] = 'smssend';
				}
				$this->step[] = 'challenge';
				$this->deblx =$ats[1];
			}else{//两步中的第二步
				$this->step[] = $ats[1];
				//$this->morechallenge = 'morechallenge';
				$this->deblx =$ats[1];
			}
		}else{
			$this->step=[$authyuan];
		}
		
		//print_r($this->step);
	}
	
	
	
	
	public function execstep(){
		$this->backlist['reply']['State']='';
		$steps = $this->step;
		foreach($steps as $i => $step){
			$func = "stepFor_".$step;
			if(!method_exists($this,$func)){
				$this->msg = 'no auth function for '.$step;
				$this->msghz = '未找到'.$step.'认证步骤处理函数';
				$this->code = 1;
				$this->outArray(1);
			}
			$this->$func();
		}
	}
	
	
	
	
	protected function stepFor_smssend(){
		
		$userrow= $this->rowUser;
		
		if($userrow['us_phone']=='' and $userrow['us_email']==''){
			$this->msg = "user phone and email is empty";
			$this->msghz = '用户未设置手机号或邮箱';
			$this->code = 1;
			$this->outArray(1);
		}
		$target = $userrow['us_phone'];
		$onepass = sdRandom(6); 
		$msres=sdMsgSend($target,$onepass);
		if($msres['code']!=0){
			$this->msg = 'send sms error!'.$msres['msg']."\n";
			$this->msghz = '短信发送错误';
			$this->code = 1;
			$this->outArray(1);
		}else{
			\DB::table("sdaaa.raduser")
			->where('us_name',$this->args['user'])
			->update([
				'us_onepass'=>\DB::raw("concat(now(),'_".$onepass."')")
			]);
		}
	}
	
	protected function stepFor_challenge(){
		$at = $this->deblx;
		$this->code = -1;
		$this->msg='Please enter '.$at.' Password: ';
		$this->outArray(1);
	}
	protected function askmorechallenge(){
		//这个不在step里，只在OTP、SMS密码错误时执行
		if(!$this->morechallenge){
			return;
		}
		$this->code = -1;
		$at = $this->deblx;
		$this->msg='pass wrong,Please enter '.$at.' Password: ';
		$this->outArray(1);
	}
	
	protected function stepFor_PAP(){
		if($this->rowUser['us_passval']==''){
			$this->msg = "no pass for user ".$this->args['user']." in DB";
			$this->msghz = '密码未设置';
			$this->code = 1;
			$this->outArray(1);
		}
		$pass = $this->pass1;
		if($pass != $this->rowUser['us_passval']){
			//echo "[[[".$pass."]]]===[[[".$this->rowUser['us_passval']."]]]";
			$this->msg = "auth failed for PAP";
			$this->msghz = 'PAP认证失败';
			$this->code = 1;
			$this->outArray(1);
		}
	}
	
	protected function stepFor_AD($AD='AD'){
		
		$userrow= \DB::table("sdaaa.outauth")
		->where('oa_default','1')
		->first();
		
		if(!$userrow){
			$this->msg = "no ".$AD." Server info for auth ".$AD." auth";
			$this->msghz = '未配置AD服务器';
			$this->code = 1;
			$this->outArray(1);
		}
		if(!function_exists('ldap_connect')){
			$this->msg = "no ldap extension in php";
			$this->msghz = 'php未安装ldap扩展';
			$this->code = 1;
			$this->outArray(1);
		}
		$ad=ldap_connect($userrow['oa_host']);
		if( !$ad){
			$this->msg = $AD." connnect failed";
			$this->msghz = 'ldap连接失败';
			$this->code = 1;
			$this->outArray(1);
		}
		ldap_set_option($ad,LDAP_OPT_PROTOCOL_VERSION, 3 );
		ldap_set_option($ad,LDAP_OPT_REFERRALS, 0 );
		//ldap_set_option($ad,LDAP_OPT_SIZELIMIT, 3000);
		$bd=@ldap_bind($ad,$this->args['user']."@".$userrow['oa_domain'],$this->pass1);
		if( !$bd){
			$this->msg = $AD." auth failed: ".ldap_error($ad);
			$this->msghz = 'ldap认证失败，'.ldap_error($ad);
			$this->code = 1;
			$this->outArray(1);
		}
	}
	
	protected function stepFor_LDAP(){
		return $this->stepFor_AD('LDAP');
	}
	
	protected function stepFor_RAD(){
		$userrow= \DB::table("sdaaa.outauth")
		->where('oa_default','1')
		->where('oa_prot','radius')
		->first();
		
		if(!$userrow){
			$this->msg = "no radius Server info for auth radius";
			$this->msghz = '未配置radius服务器';
			$this->code = 1;
			$this->outArray(1);
		}
		if(!function_exists('radius_auth_open')){
			$this->msg = "no radius extension in php";
			$this->msghz = 'php未安装radius扩展';
			$this->code = 1;
			$this->outArray(1);
		}
		
		$radius = radius_auth_open(); 
		$host = $userrow['oa_host'];
		$domain = $userrow['oa_domain'];
		$port = $userrow['oa_port'];
		if(strstr($port,'/')){$port = explode('/',$port)[0];}
    	if (! radius_add_server($radius,$host,$port,$domain,5,1)){ 
		//5秒后超时，1次尝试
        	$this->msg = 'connect radius server failed';
			$this->msghz = 'radius服务器连接失败';
			$this->code = 1;
			$this->outArray(1);
    	} 
    	if (! radius_create_request($radius,RADIUS_ACCESS_REQUEST)){ 
			$this->msg = 'send auth requst to radius server failed';
			$this->msghz = '发送认证请求失败';
			$this->code = 1;
			$this->outArray(1);
    	} 
		$user = $this->args['user'];
		$pass = (($this->step[0]=='RAD')?($this->pass1):($this->pass2));
    	radius_put_attr($radius,RADIUS_USER_NAME,$user); 
    	radius_put_attr($radius,RADIUS_USER_PASSWORD,$pass); 
    	switch(radius_send_request($radius)){
        	case RADIUS_ACCESS_ACCEPT: 
            	return true; 
            	break; 
        	case RADIUS_ACCESS_REJECT:
				$this->msg = 'radius server return reject';
				$this->msghz = 'radius服务器返回拒绝包';
				$this->code = 1;
				$this->outArray(1);
            	break; 
        	case RADIUS_ACCESS_CHALLENGE: 
				$this->msg = 'radius server return challenge';
				$this->msghz = 'radius服务器返回挑战包';
				$this->code = 1;
				$this->outArray(1);
            	break; 
        	default: 
				$this->msg = 'radius server return error: '.radius_strerror($radius);
				$this->msghz = 'radius服务器返回错误，'.radius_strerror($radius);
				$this->code = 1;
				$this->outArray(1);
    	} 
		
	}
	
	protected function stepFor_OTP(){
		
		$userrow= $this->rowUser;
		
		if(!$userrow['us_seep']){
			$this->msg = "user Period not set ";
			$this->msghz = '用户未设置OTP间隔时间';
			$this->code = 1;
			$this->outArray(1);
		}
		
		if(!$userrow['us_seed']){
			$this->msg = "user seed not set ";
			$this->msghz = '用户未设置OTP种子';
			$this->code = 1;
			$this->outArray(1);
		}
		
		sdOtpPeriodSet($userrow['us_seep']);
		$onpepass = sdOtpOnePassGet($userrow['us_seed']);
		//echo '{'.$this->pass2.'}{'.$onpepass.'}';
		if($this->pass2!=$onpepass){
			$this->msg = "otp pass wrong";
			$this->msghz = 'OTP口令错误';
			$this->code = 1;
			$this->askmorechallenge();
			$this->outArray(1);
		}
	}
	
	protected function stepFor_SMS(){
		
		$userrow= \DB::table("sdaaa.raduser")
		->where('us_user',$this->args['user'])
		->field('us_onepass')
		->first();
		
		if(!$userrow){
			$this->msg = "no user info for auth sms";
			$this->msghz = '未查到用户短信';
			$this->code = 1;
			$this->outArray(1);
		}
		if($userrow['us_onepass']==''){
			$this->msg = "onepass catch is empty";
			$this->msghz = '用户短信内容为空';
			$this->code = 1;
			$this->outArray(1);
		}
		
		$onepasss = explode('_',$userrow['us_onepass']);
		if(count($onepasss)!=2){
			$this->msg = "sms passs info wrong: ".$userrow['us_onepass'];
			$this->msghz = '数据库里的短信验证码格式错误';
			$this->code = 1;
			$this->outArray(1);
		}
		if(time()>(strtotime($onepasss[0])+600)){
			$this->msg = 'sms pass expired';
			$this->msghz = '短信验证码过期(10分钟)';
			$this->code = 1;
			$this->outArray(1);
		}
			//如果时间没到，就不用新生成的code,用老的
		$pass = $onepasss[1];
		if($this->pass2!=$pass){
			$this->msg = 'sms pass wrong';
			$this->msghz = '短信验证码错误';
			$this->code = 1;
			$this->askmorechallenge();
			$this->outArray(1);
		}
		
	}
	
	
	
	protected function authForCmd(){
		//返回true，表示这是条cmd授权，false表示非cmd授权
		//授权成功与否，由code设定
		$inargs = $this->args['args'];
		$inargsa = explode('-|-|-',$inargs);
		$more = [];
		foreach($inargsa as $co){
			$coo=explode('=',$co,2);
			if(count($coo)<2){
				continue;
			}
			$k = $coo[0];
			$v = $coo[1];
			if(array_key_exists($k,$more)){
				$more[$k].=" ";
			}else{
				$more[$k] = "";
			}
			$more[$k].=trim($v);
		}
		if(isset($more['cmd']) and $more['cmd']){//表示这是条cmd授权请求
			//cmd=show  cmd-arg=running-config
			//授权命令
			//$this->code=0;return true;
			if(isset($more['cmd-arg'])){
				$more['cmd'].=' '.$more['cmd-arg'];
			}
			$mycmd = trim($more['cmd']);
			if(substr($mycmd,-4)=='<cr>'){//去除末尾的<cr>
				$mycmd = trim(substr($mycmd,0,-4));
			}
			$this->args['cmd'] = $mycmd;//用于审计
			
			$cmdid = $this->rowPerm['cmd'];
			if($cmdid=='' or $cmdid=='0'){//该用户没有设置命令授权
				$this->outArray(2);
			}
			
			$row= \DB::table("sdaaa.permcmd")
			->where('gcid',$cmdid)
			->first();
			
			$dflt = $row['gc_dflt'];
			$this->code = ($dflt=='0'?'1':'0');//默认授权，数据库里0表示拒绝，得返回1
			$rows= \DB::table("sdaaa.permcmd_list")
			->where('gcl_gcid',$cmdid)
			->get();
			
			foreach($rows as $row){
				$pattern = $row['gcl_cmd'];
				if(substr($pattern,0,1)!='/'){//如果两边没有斜杆
					$pattern = '/'.$pattern.'/';//就把斜杠加上去
				}
				if(preg_match($pattern , $mycmd)){
					$this->code = ($row['gcl_perm']=='0'?'1':'0') ;
					if($row['gcl_sms']){//要求发送短信报警
						app\sms\smsLog::send([
							'call'=>'envir',
							'msg'=>$mycmd.'['.($this->code?'拒绝':'允许').']',
						]);
					}
					break;
				}
			}
			$this->outArray(2);
		}
	}
	
	
	protected function writeForReply(){
		if($this->rowUser['us_rad_reply']==''){return;}
		$replys = json_decode($this->rowUser['us_rad_reply'],true);
		foreach($replys as $reps){
			if($reps['key']=='' or $reps['val']==''){continue;}
			if(!strstr($reps['key'],'tac:')){//需以tac:开头
				continue;
			}
			$reps['key'] = str_replace('tac:','',$reps['key']);
			$this->backlist['reply'][$reps['key']]=$reps['val'];
		}
	}
	
	protected function writeForAttrTac(){
		$attrid = $this->rowPerm['attr'];
		
		if($attrid=='' or $attrid=='0'){
			return [];
		}
		$ret=[];
		$vid = $this->rowNas['na_type'];
		
		$rows= \DB::table("sdaaa.permattr_list")
			->where('gal_pro','1')
			->where('gal_gaid',$attrid)
			->where(function($mydb)use($vid){
				$mydb->where("gal_ntid","0")->orWhere("gal_ntid",$vid);
			})
			->get();
		
		foreach($rows as $row){ 
			$this->backlist['reply'][$row['gal_attr']]=$row['gal_val'];
		}
	}
	protected function writeForAttrRad(){
		$attrid = $this->rowPerm['attr'];
		
		if($attrid=='' or $attrid=='0'){
			return [];
		}
		$ret=[];
		$vid = $this->rowNas['na_type'];
		
		$rows= \DB::table("sdaaa.permattr_list")
			->where('gal_pro','0')
			->where('gal_gaid',$attrid)
			->where(function($mydb)use($vid){
				$mydb->where("gal_ntid","0")->orWhere("gal_ntid",$vid);
			})
			->get();
		
		foreach($rows as $row){ 
			$this->backlist['reply'][$row['gal_attr']]=$row['gal_val'];
		}
	}
	
	
	protected function getNasInfo(){
		$nas = $this->args['nas'];
		$permrow = \DB::table("sdaaa.nas")
		->where('na_ip',$nas)
		->first();
		if($permrow){
			return $permrow;
		}
		
		$nasTemp=null;
		$nas = $this->args['nas'];
		$nasint = ip2long($nas);
		$data = \DB::table("sdaaa.netsegment")->get();
		foreach($data as $da){
			if($nasint > $da['ns_start'] and $nasint < $da['ns_end'] ){
				$nasTemp = [
					'na_ip' => $nas,
					'na_name' => $nas,
					'na_secret' => $da['ns_secret'],
					'na_organ' => $da['ns_organ'],
					'na_type' => $da['ns_type'],
					'na_active'=> '1',
				];
				break;
			}
		}
		$this->nasTemp = $nasTemp;
		return $nasTemp;
	}
	
	
	
	protected function outArray($logtype){
		//logtype 0 不记录 1 认证 2 命令授权
		if($this->nasTemp and $logtype==1 and $this->code==0){
			\DB::table("sdaaa.nas")->insert($this->nasTemp);
			
		}
		
		echoOut($this->code,$this->msg,$this->backlist,$logtype,$this->args,$this->msghz);
	}
	
	
	
	
}




?>
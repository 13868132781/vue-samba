<?php

function tacSection_authen($args){
	$obj = new Auth_authen($args);
	$obj->run();
}


class Auth_authen{
	protected $args=[];
	
	protected $code = 0;// -1 0 1
	protected $msg = '';
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
	public function __construct($opt){
		$this->args=$opt;
	}
	
	public function run(){
		
		$this->queryForUser();//查询user信息
		$this->queryForNas();//查询nas信息
		$this->queryForPerm();//查询角色信息
		$this->queryForWay();//查询认证方式
		
		$this->checkForStatus(); //检查用户状态
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
	
	
	protected function queryForUser(){
		$permrow = \DB::table("sdaaa.raduser")
		->where('us_name',$this->args['user'])
		->first();
		
		if(!$permrow){
			$this->errmsg ='unknown username';
			$this->code = 1;
			$this->outArray(1);	
		}
		$this->rowUser = $permrow;
	}
	
	protected function queryForNas(){
		$permrow = \DB::table("sdaaa.nas")
		->where('na_ip',$this->args['nas'])
		->first();
		
		if(!$permrow){
			$this->errmsg ='unknown nas';
			$this->code = 1;
			$this->outArray(1);	
		}
		$this->rowNas = $permrow;
	}
	
	
	protected function queryForPerm(){
		global $sdmysql;
		
		$filter="gp_enable='1'";
		if($this->rowUser['us_gpid']){
			$filter="gpid='".$this->rowUser['us_gpid']."'";
		}
		
		$permrows = \DB::select("select * from (select * from sdaaa.ag_perm where ".$filter.")c left join (select * from sdaaa.ag_perm_organ where  gpo_onid='".$this->rowNas['na_organ']."')d on c.gpid=d.gpo_gpid left join (select * from sdaaa.ag_perm_nas where gpn_naip='".$this->rowNas['na_ip']."')e on c.gpid=e.gpn_gpid");
		
		if(!$permrows or count($permrows)==0){
			$this->errmsg ='unknown permgroup';
			$this->code = 1;
			$this->outArray(1);	
		}
		$this->rowPerm = $permrows[0];
		
		
	}
	
	
	protected function queryForWay(){
		$obj = \DB::table("sdaaa.ab_authway");
		if($this->rowUser['us_tfa']){
			$obj->where("awid",$this->rowUser['us_tfa']);
		}else{
			$obj->where('aw_enable','1');
		}
		$authway = $obj->value('aw_key');
		
		if(!$authway){
			$this->errmsg ='can not find auth way';
			$this->code = 1;
			$this->outArray(1);
		}
		$this->authway = $authway?:'PAP';
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	protected function checkForStatus(){
		if($this->rowUser['us_status']!='0'){
			$this->msg ='user status is lockout';
			$this->code = 1;
			$this->outArray(1);	
		}
	}
	
	protected function checkForClientid(){
		if($this->rowUser['us_rad_clientid']!='' and $this->args['nac']!=$this->rowUser['us_rad_clientid']){
			$this->msg ='Calling-Station-Id wrong';
			$this->code = 1;
			$this->outArray(1);
		}
	}
	
	protected function checkForIp(){
		$checkid = $this->realValue('check');
		if($checkid==0){
			$this->msg =$this->args['user'].' forbid to login '.$this->args['nas'];
			$this->code = 1;
			$this->outArray(1);
		}
	}
	
	protected function checkForShid(){
		$shidid = $this->realValue('shid');
		if($shidid=='' or $shidid=='0'){
			return;
		}
		
		
		$row= \DB::table("sdaaa.ag_shid")
		->where('gsid',$shidid)
		->first();
		
		if(!$row){
			$this->msg = 'no period policy for id '.$shidid;
			$this->code = 1;
			$this->outArray(1);
		}
		if($row['gs_day']!=''){
			$list=shiddisply($row['gs_day']);
			if(count($list)>0){
				$shij = date('G');
				if(!array_key_exists($shij,$list)){
					$this->msg = 'login in forbid hours of day';
					$this->code = 1;
					$this->outArray(1);
				}
			}
		}
		if($row['gs_week']!=''){
			$list=shiddisply($row['gs_week']);
			if(count($list)>0){
				$shij = date("w");
				if($shij==0){$shij=7;}
				if(!array_key_exists($shij,$list)){
					$this->msg = 'login in forbid days of week';
					$this->code = 1;
					$this->outArray(1);
				}
			}
		}
		if($row['gs_month']!=''){
			$list=shiddisply($row['gs_month']);
			if(count($list)>0){
				$shij = date("j");
				if(!array_key_exists($shij,$list)){
					$this->msg = 'login in forbid days of month';
					$this->code = 1;
					$this->outArray(1);
				}
			}
		}
		if($row['gs_year']!=''){
			$list=shiddisply($row['gs_year']);
			if(count($list)>0){
				$shij = date("Y");
				if(!array_key_exists($shij,$list)){
					$this->msg = 'login in forbid years';
					$this->code = 1;
					$this->outArray(1);
				}
			}
		}
	}
	
	protected function checkForLimit(){
		$limitid = $this->realValue('limit');
		
		if($limitid=='' or $limitid=='0'){
			return;
		}
		$urow = $this->row;
		
		$row= \DB::table("sdaaa.ag_limit")
		->where('glid',$limitid)
		->first();

		if(!$row){
			$this->msg = 'no limit policy for id '.$limitid;
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
					$this->code = 1;
					$this->outArray(1);
				}
				if(time()<(strtotime($numt)+$numcl*60)){//还没过锁定时间
					$still = $numcl-floor((time()-strtotime($numt))/60);
					$this->msg = 'still lockout in '.$still.' minute';
					$this->code = 1;
					$this->outArray(1);
				}
			}
			
			if($row['gl_login'] and is_numeric($row['gl_login'])){
				$numn = $urow['us_limit_loginnum'];
				if($numn>=$row['gl_login']){
					$this->msg = 'login Times exceeded';
					$this->code = 1;
					$this->outArray(1);
				}
			}
			if($row['gl_gq_user'] and is_numeric($row['gl_gq_user'])){
				$numn = $urow['us_limit_usertime'];
				if(time()>(strtotime($numn)+$row['gl_gq_user']*24*60*60)){
					$this->msg = 'user Time expired';
					$this->code = 1;
					$this->outArray(1);
				}
			}
			if($row['gl_gq_pass'] and is_numeric($row['gl_gq_pass'])){
				$numn = $urow['us_limit_passtime'];
				if(time()>(strtotime($numn)+$row['gl_gq_pass']*24*60*60)){
					$this->msg = 'pass Time expired';
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
	
	protected function realValue($hz,$dep=3){
		$row = $this->rowPerm;
		$qzs=['gpn','gpo','gp'];
		if($dep==2){$qzs=['gpo','gp'];}
		$res = '0';
		foreach($qzs as $qz){
			$col = $qz."_".$hz;
			if(strlen($row[$col])>0 and $row[$col]!='-1'){
				$res = $row[$col];
				break;
			}
		}
		return $res;
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
			if($state==''){//两步总的第一步
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
				$this->code = 1;
				$this->outArray(1);
			}
			$this->$func();
		}
	}
	
	
	
	
	protected function stepFor_smssend(){
		
		$userrow= \DB::table("sdaaa.raduser")
		->where('us_name',$this->args['user'])
		->first();
		
		if(!$userrow){
			$this->msg = "no user info for send sms";
			$this->code = 1;
			$this->outArray(1);
		}
		if($userrow['us_phone']=='' and $userrow['us_email']==''){
			$this->msg = "user phone and email is empty";
			$this->code = 1;
			$this->outArray(1);
		}
		$target = ['phone'=>$userrow['us_phone'],'email'=>$userrow['us_email']];
		$onepass = sdRandom(6); 
		$msres=msgSend($target,$onepass);
		if($msres['code']!=0){
			$this->msg = 'send sms error!'.$msres['msg']."\n";
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
			$this->code = 1;
			$this->outArray(1);
		}
		$pass = $this->pass1;
		if($this->rowUser['us_passkey']=='Crypt-Password'){
			$pass = crypt($pass,'_J9..mysd');
		}else if($this->rowUser['us_passkey']=='MD5-Password'){
			$pass = MD5($pass);
		}
		if($pass != $this->rowUser['us_passval']){
			//echo "[[[".$pass."]]]===[[[".$this->rowUser['us_passval']."]]]";
			$this->msg = "auth failed for PAP";
			$this->code = 1;
			$this->outArray(1);
		}
	}
	
	protected function stepFor_AD($AD='AD'){
		
		$userrow= \DB::table("sdaaa.ab_outauth")
		->where('oa_enable','1')
		->first();
		
		if(!$userrow){
			$this->msg = "no ".$AD." Server info for auth ".$AD." auth";
			$this->code = 1;
			$this->outArray(1);
		}
		if(!function_exists('ldap_connect')){
			$this->msg = "no ldap extension in php";
			$this->code = 1;
			$this->outArray(1);
		}
		$ad=ldap_connect($userrow['oa_host']);
		if( !$ad){
			$this->msg = $AD." connnect failed";
			$this->code = 1;
			$this->outArray(1);
		}
		ldap_set_option($ad,LDAP_OPT_PROTOCOL_VERSION, 3 );
		ldap_set_option($ad,LDAP_OPT_REFERRALS, 0 );
		//ldap_set_option($ad,LDAP_OPT_SIZELIMIT, 3000);
		$bd=@ldap_bind($ad,$this->args['user']."@".$userrow['oa_domain'],$this->pass1);
		if( !$bd){
			$this->msg = $AD." auth failed: ".ldap_error($ad);
			$this->code = 1;
			$this->outArray(1);
		}
	}
	
	protected function stepFor_LDAP(){
		return $this->stepFor_AD('LDAP');
	}
	
	protected function stepFor_RAD(){
		$userrow= \DB::table("sdaaa.ab_outauth")
		->where('oa_enable','1')
		->where('oa_prot','radius')
		->first();
		
		if(!$userrow){
			$this->msg = "no radius Server info for auth radius";
			$this->code = 1;
			$this->outArray(1);
		}
		if(!function_exists('radius_auth_open')){
			$this->msg = "no radius extension in php";
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
			$this->code = 1;
			$this->outArray(1);
    	} 
    	if (! radius_create_request($radius,RADIUS_ACCESS_REQUEST)){ 
			$this->msg = 'send auth requst to radius server failed';
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
				$this->code = 1;
				$this->outArray(1);
            	break; 
        	case RADIUS_ACCESS_CHALLENGE: 
				$this->msg = 'radius server return challenge';
				$this->code = 1;
				$this->outArray(1);
            	break; 
        	default: 
				$this->msg = 'radius server return error: '.radius_strerror($radius);
				$this->code = 1;
				$this->outArray(1);
    	} 
		
	}
	
	protected function stepFor_OTP(){
		
		$userrow= \DB::table("sdaaa.raduser")
		->where('us_name',$this->args['user'])
		->field('us_seed','us_seep')
		->first();
		
		if(!$userrow){
			$this->msg = "no user info for auth otp";
			$this->code = 1;
			$this->outArray(1);
		}
		\App\SdAaa\Inc\func\HlcOtp::period_set($userrow['us_seep']);
		$onpepass = \App\SdAaa\Inc\func\HlcOtp::onepass_get($userrow['us_seed']);
		//echo '['.$this->args['sd_once_password'].']['.$onpepass."]\n";
		if($this->pass2!=$onpepass){
			$this->msg = "otp pass wrong";
			$this->code = 1;
			$this->askmorechallenge();
			$this->outArray(1);
		}
	}
	
	protected function stepFor_SMS(){
		
		$userrow= \DB::table("sdaaa.raduser")
		->where('us_name',$this->args['user'])
		->where('us_onepass','!=','')
		->field('us_onepass')
		->first();
		
		if(!$userrow){
			$this->msg = "no user info for auth sms";
			$this->code = 1;
			$this->outArray(1);
		}
		$onepasss = explode('_',$userrow['us_onepass']);
		if(count($onepasss)!=2){
			$this->msg = "sms passs info wrong: ".$userrow['us_onepass'];
			$this->code = 1;
			$this->outArray(1);
		}
		if(time()>(strtotime($onepasss[0])+600)){
			$this->msg = 'sms pass expired';
			$this->code = 1;
			$this->outArray(1);
		}
			//如果时间没到，就不用新生成的code,用老的
		$pass = $onepasss[1];
		if($this->pass2!=$pass){
			$this->msg = 'sms pass wrong';
			$this->code = 1;
			$this->askmorechallenge();
			$this->outArray(1);
		}
		
	}
	
	
	protected function outArray($logtype){
		echoOut($this->code,$this->msg,$this->backlist,$logtype,$this->args);
	}
	
	
	
	
}



?>
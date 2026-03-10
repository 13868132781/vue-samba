<?php
namespace App\SdAaa\Inc\func\AuthRad;

class Auth_authenticate{
	protected $args=[];
	
	protected $code = 0;
	protected $errmsg = '';
	
	protected $morechallenge = false;
	protected $deblx = '';//OTP\SMS，提示输入里
	
	protected $backlist = [];
	
	protected $step=[];
	public function __construct($opt){
		$this->args=$opt;	
		$this->authstep();
	}
	
	protected function authstep(){//分析出一个认证步骤来
		$authyuan = $this->args['sd_auth_yuan'];
		$authtype = $this->args['sd_auth_type'];
		if(strstr($authyuan,'-')){
			$ats =explode('-', $authyuan);
			//if($ats[0]=='AD' or $ats[0]=='LDAP' or $ats[0]=='RAD'){
				$this->step[] = $ats[0];
			//}
			$this->step[] = $ats[1];
			
		}else if(strstr($authyuan,'+')){
			$ats =explode('+', $authyuan);
			if(strstr($authtype,'+')){//两步总的第一步
				//if($ats[0]=='AD' or $ats[0]=='LDAP' or $ats[0]=='RAD'){
					$this->step[] = $ats[0];
				//}
				if($ats[1]=='SMS'){
					$this->step[] = 'smssend';
				}
				$this->step[] = 'challenge';
				$this->deblx =$ats[1];
			}else{//两步中的第二步
				$this->step[] = $ats[1];
				$this->morechallenge = 'morechallenge';
				$this->deblx =$ats[1];
			}
		}else{
			$this->step=[$authyuan];
		}
		
		//print_r($this->step);
	}
	
	
	
	
	public function run(){
		$this->backlist['reply']['State']='';
		$steps = $this->step;
		foreach($steps as $i => $step){
			$func = "checkFor_".$step;
			if(!method_exists($this,$func)){
				$this->errmsg = 'no auth function for '.$step;
				$this->code = 1;
				return $this->outArray();
			}
			
			if(!$this->$func()){
				break;
			}
		}
		return $this->outArray();
	}
	
	
	
	
	protected function checkFor_smssend(){
		global $sdmysql;
		$userobj = $sdmysql->query("select us_phone,us_email from sdaaa.raduser where us_name='".$this->args['user']."'");
		$userrow = $userobj->fetch_assoc();
		if(!$userrow){
			$this->errmsg = "no user info for send sms";
			$this->code = 1;
			return false;
		}
		if($userrow['us_phone']=='' and $userrow['us_email']==''){
			$this->errmsg = "user phone and email is empty";
			$this->code = 1;
			return false;
		}
		$target = ['phone'=>$userrow['us_phone'],'email'=>$userrow['us_email']];
		$onepass = sdRandom(6); 
		$msres=msgSend($target,$onepass);
		if($msres['code']!=0){
			$this->errmsg = 'send sms error!'.$msres['msg']."\n";
			$this->code = 1;
			return false;
		}else{
			$sdmysql->query("update sdaaa.raduser set us_onepass=concat(now(),'_".$onepass."') where us_name='".$this->args['user']."'");
		}
		return true;
	}
	
	protected function checkFor_challenge(){
		$at = $this->deblx;
		$this->backlist['check']['Response-Packet-Type']='Access-Challenge';
		$this->backlist['reply']['State']='1'; 
		$this->backlist['reply']['Reply-Message']='Please enter '.$at.' Password: ';
		return true;
	}
	protected function askmorechallenge(){
		//这个不在step里，只在OTP、SMS密码错误时执行
		if(!$this->morechallenge){
			return;
		}
		$at = $this->deblx;
		$this->backlist['check']['Response-Packet-Type']='Access-Challenge';
		$this->backlist['reply']['State']='1';
		$this->backlist['reply']['Reply-Message']='pass wrong,Please enter '.$at.' Password: ';
		return;
	}
	
	protected function checkFor_PAP(){
		if($this->args['us_passval']==''){
			$this->errmsg = "no pass for user ".$this->args['user']." in DB";
			$this->code = 1;
			return false;
		}
		$pass = $this->args['pass'];
		if($this->args['us_passkey']=='Crypt-Password'){
			$pass = crypt($pass,'_J9..mysd');
		}else if($this->args['us_passkey']=='MD5-Password'){
			$pass = MD5($pass);
		}
		if($pass != $this->args['us_passval']){
			$this->errmsg = "auth failed for PAP";
			$this->code = 1;
			return false;
		}
		return true;
	}
	
	protected function checkFor_AD($AD='AD'){
		
		global $sdmysql;
		$userobj = $sdmysql->query("SELECT * FROM sdaaa.ab_outauth where oa_enable='1'");
		$userrow = $userobj->fetch_assoc();
		if(!$userrow){
			$this->errmsg = "no ".$AD." Server info for auth ".$AD." auth";
			$this->code = 1;
			return false;
		}
		if(!function_exists('ldap_connect')){
			$this->errmsg = "no ldap extension in php";
			$this->code = 1;
			return false;
		}
		$ad=ldap_connect($userrow['oa_host']);
		if( !$ad){
			$this->errmsg = $AD." connnect failed";
			$this->code = 1;
			return false;
		}
		ldap_set_option($ad,LDAP_OPT_PROTOCOL_VERSION, 3 );
		ldap_set_option($ad,LDAP_OPT_REFERRALS, 0 );
		//ldap_set_option($ad,LDAP_OPT_SIZELIMIT, 3000);
		$bd=@ldap_bind($ad,$this->args['user']."@".$userrow['oa_domain'],$this->args['pass']);
		if( !$bd){
			$this->errmsg = $AD." auth failed: ".ldap_error($ad);
			$this->code = 1;
			return false;
		}
		return true;
	}
	
	protected function checkFor_LDAP(){
		return $this->checkFor_AD('LDAP');
	}
	
	protected function checkFor_RAD(){
		global $sdmysql;
		$userobj = $sdmysql->query("SELECT * FROM sdaaa.ab_outauth where oa_enable='1' and oa_prot='radius'");
		$userrow = $userobj->fetch_assoc();
		if(!$userrow){
			$this->errmsg = "no radius Server info for auth radius";
			$this->code = 1;
			return false;
		}
		if(!function_exists('radius_auth_open')){
			$this->errmsg = "no radius extension in php";
			$this->code = 1;
			return false;
		}
		
		$radius = radius_auth_open(); 
		$host = $userrow['oa_host'];
		$domain = $userrow['oa_domain'];
		$port = $userrow['oa_port'];
		if(strstr($port,'/')){$port = explode('/',$port)[0];}
    	if (! radius_add_server($radius,$host,$port,$domain,5,1)){ 
		//5秒后超时，1次尝试
        	$this->errmsg = 'connect radius server failed';
			$this->code = 1;
			return false; 
    	} 
    	if (! radius_create_request($radius,RADIUS_ACCESS_REQUEST)){ 
			$this->errmsg = 'send auth requst to radius server failed';
			$this->code = 1;
			return false;
    	} 
		$user = $this->args['user'];
		$pass = (($this->step[0]=='RAD')?($this->args['pass']):($this->args['sd_once_password']));
    	radius_put_attr($radius,RADIUS_USER_NAME,$user); 
    	radius_put_attr($radius,RADIUS_USER_PASSWORD,$pass); 
    	switch(radius_send_request($radius)){
        	case RADIUS_ACCESS_ACCEPT: 
            	return true; 
            	break; 
        	case RADIUS_ACCESS_REJECT:
				$this->errmsg = 'radius server return reject';
				$this->code = 1;
				return false;
            	break; 
        	case RADIUS_ACCESS_CHALLENGE: 
				$this->errmsg = 'radius server return challenge';
				$this->code = 1;
				return false;
            	break; 
        	default: 
				$this->errmsg = 'radius server return error: '.radius_strerror($radius);
				$this->code = 1;
				return false;
    	} 
		
		return true;
	}
	
	protected function checkFor_OTP(){
		global $sdmysql;
		$userobj = $sdmysql->query("select us_seed,us_seep from sdaaa.raduser where us_name='".$this->args['user']."'");
		$userrow = $userobj->fetch_assoc();
		if(!$userrow){
			$this->errmsg = "no user info for auth otp";
			$this->code = 1;
			return false;
		}
		\App\SdAaa\Inc\func\HlcOtp::period_set($userrow['us_seep']); 
		$onpepass = \App\SdAaa\Inc\func\HlcOtp::onepass_get($userrow['us_seed']);
		//echo '['.$this->args['sd_once_password'].']['.$onpepass."]\n";
		if($this->args['sd_once_password']!=$onpepass){
			$this->errmsg = "otp pass wrong";
			$this->code = 1;
			$this->askmorechallenge();
			return false;
		}
		return true;
	}
	
	protected function checkFor_SMS(){
		global $sdmysql;
		$userobj = $sdmysql->query("select us_onepass from sdaaa.raduser where us_name='".$this->args['user']."' and us_onepass!=''");
		$userrow = $userobj->fetch_assoc();
		if(!$userrow){
			$this->errmsg = "no user info for auth sms";
			$this->code = 1;
			return false;
		}
		$onepasss = explode('_',$userrow['us_onepass']);
		if(count($onepasss)!=2){
			$this->errmsg = "sms passs info wrong: ".$userrow['us_onepass'];
			$this->code = 1;
			return false;
		}
		if(time()>(strtotime($onepasss[0])+600)){
			$this->errmsg = 'sms pass expired';
			$this->code = 1;
			return false;
		}
			//如果时间没到，就不用新生成的code,用老的
		$pass = $onepasss[1];
		if($this->args['sd_once_password']!=$pass){
			$this->errmsg = 'sms pass wrong';
			$this->code = 1;
			$this->askmorechallenge();
			return false;
		}
		
		return true;
	}
	
	
	protected function outArray(){
		
		return [
			'code'=>$this->code,
			'msg'=>$this->errmsg,
			'vars'=>$this->backlist
		];
	}
	
	
	
	
}



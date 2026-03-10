<?php

function radSection_authenticate($args){
	$obj = new Auth_authenticate($args);
	$obj->run();
}


class Auth_authenticate{
	protected $args = [];
	
	protected $rowUser = [];
	protected $rowNas = [];
	protected $rowPerm = [];

	protected $code = '0';
	protected $msg ='';
	protected $backlist = [];
	
	
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
		
		
		$this->writeForReply();//写用户自定义返回属性
		$this->writeForAttr();//写角色属性
		
		return $this->outArray();
		
		
		//一步还是两步 
		//当前第几步
		//第一认证
		//第二认证
		//user-password
		//once-password
		
		
	}
	
	protected function queryForUser(){
		global $sdmysql;
		$permsql ="select * from sdaaa.raduser where us_name='".$this->args['user']."'";
		$permobj = $sdmysql->query($permsql);
		$permrow = $permobj->fetch_assoc();
		if(!$permrow){
			$this->errmsg ='unknown username';
			$this->code = 1;
			return false;	
		}
		$this->row = $permrow;
		return true;
	}
	
	protected function queryForNas(){
		global $sdmysql;
		$permsql ="select * from sdaaa.nas where na_ip='".$this->args['nas']."'";
		$permobj = $sdmysql->query($permsql);
		$permrow = $permobj->fetch_assoc();
		if(!$permrow){
			$this->errmsg ='unknown nas';
			$this->code = 1;
			return false;	
		}
		$this->rownas = $permrow;
		return true;
	}
	
	
	protected function queryForPerm(){
		global $sdmysql;
		
		$filter="gp_enable='1'";
		if($this->row['us_gpid']){
			$filter="gpid='".$this->row['us_gpid']."'";
		}
		
		$permsql ="select * from (select * from sdaaa.ag_perm where ".$filter.")c left join (select * from sdaaa.ag_perm_organ where  gpo_onid='".$this->rownas['na_organ']."')d on c.gpid=d.gpo_gpid left join (select * from sdaaa.ag_perm_nas where gpn_naip='".$this->rownas['na_ip']."')e on c.gpid=e.gpn_gpid";
		$permobj = $sdmysql->query($permsql);
		$permrow = $permobj->fetch_assoc();
		if(!$permrow){
			$this->errmsg ='unknown permgroup';
			$this->code = 1;
			return false;	
		}
		$this->rowperm = $permrow;
		return true;
		
		
	}
	
	
	protected function queryForWay(){
		global $sdmysql;
		$filter="aw_enable='1'";
		if($this->row['us_tfa']){
			$filter="awid='".$this->row['us_tfa']."'";
		}
		$sql = "select aw_key from sdaaa.ab_authway where ".$filter;
		$authwayobj = $sdmysql->query($sql);
		$authwayrow = $authwayobj->fetch_assoc();
		
		if(!$authwayrow){
			$this->errmsg ='can not find auth way';
			$this->code = 1;
			return false;
		}
		$this->sd_auth_yuan = $authwayrow['aw_key']?:'PAP';
		
		return true;
	}
	
	protected function checkForStatus(){
		if($this->row['us_status']!='0'){
			$this->errmsg ='user status is lockout';
			$this->code = 1;
			return false;	
		}
		return true;
	}
	
	protected function checkForClientid(){
		if($this->row['us_rad_clientid']!='' and $this->args['nac']!=$this->row['us_rad_clientid']){
			$this->errmsg ='Calling-Station-Id wrong';
			$this->code = 1;
			return false;
		}
		return true;
	}
	
	protected function checkForIp(){
		$checkid = $this->realValue('check');
		if($checkid==0){
			$this->errmsg =$this->args['user'].' forbid to login '.$this->args['nas'];
			$this->code = 1;
			return false;
		}
		return true;
	}
	
	protected function checkForShid(){
		$shidid = $this->realValue('shid');
		if($shidid=='' or $shidid=='0'){
			return true;
		}
		
		global $sdmysql;
		$row=$sdmysql->query("select * from sdaaa.ag_shid where gsid='".$shidid."'")->fetch_assoc();
		if(!$row){
			$this->errmsg = 'no period policy for id '.$shidid;
			$this->code = 1;
			return false;
		}
		if($row['gs_day']!=''){
			$list=shiddisply($row['gs_day']);
			if(count($list)>0){
				$shij = date('G');
				if(!array_key_exists($shij,$list)){
					$this->errmsg = 'login in forbid hours of day';
					$this->code = 1;
					return false;
				}
			}
		}
		if($row['gs_week']!=''){
			$list=shiddisply($row['gs_week']);
			if(count($list)>0){
				$shij = date("w");
				if($shij==0){$shij=7;}
				if(!array_key_exists($shij,$list)){
					$this->errmsg = 'login in forbid days of week';
					$this->code = 1;
					return false;
				}
			}
		}
		if($row['gs_month']!=''){
			$list=shiddisply($row['gs_month']);
			if(count($list)>0){
				$shij = date("j");
				if(!array_key_exists($shij,$list)){
					$this->errmsg = 'login in forbid days of month';
					$this->code = 1;
					return false;
				}
			}
		}
		if($row['gs_year']!=''){
			$list=shiddisply($row['gs_year']);
			if(count($list)>0){
				$shij = date("Y");
				if(!array_key_exists($shij,$list)){
					$this->errmsg = 'login in forbid years';
					$this->code = 1;
					return false;
				}
			}
		}
		return true;
	}
	
	protected function checkForLimit(){
		$limitid = $this->realValue('limit');
		
		if($limitid=='' or $limitid=='0'){
			return [0];
		}
		$urow = $this->row;
		
		global $sdmysql;
		$row=$sdmysql->query("select * from sdaaa.ag_limit where glid='".$limitid."'")->fetch_assoc();
		if(!$row){
			$this->errmsg = 'no limit policy for id '.$limitid;
			$this->code = 1;
			return false;
		}
		if($row['gl_fail_cs'] and is_numeric($row['gl_fail_cs'])){
			$numcs = $row['gl_fail_cs'];
			$numcl = $row['gl_fail_cl'];
			$numn = $urow['us_limit_failnum'];
			$numt = $urow['us_limit_failtime'];
			if($numn>=$numcs){
				if($numcl==''){//无限锁定
					$this->errmsg = 'lockout forever';
					$this->code = 1;
					return false;
				}
				if(time()<(strtotime($numt)+$numcl*60)){//还没过锁定时间
					$this->backlist['perl']['sd_keepfailtime']='true';
					$still = $numcl-floor((time()-strtotime($numt))/60);
					$this->errmsg = 'still lockout in '.$still.' minute';
					$this->code = 1;
					return false;
				}
			}
			
			if($row['gl_login'] and is_numeric($row['gl_login'])){
				$numn = $urow['us_limit_loginnum'];
				if($numn>=$row['gl_login']){
					$this->errmsg = 'login Times exceeded';
					$this->code = 1;
					return false;
				}
			}
			if($row['gl_gq_user'] and is_numeric($row['gl_gq_user'])){
				$numn = $urow['us_limit_usertime'];
				if(time()>(strtotime($numn)+$row['gl_gq_user']*24*60*60)){
					$this->errmsg = 'user Time expired';
					$this->code = 1;
					return false;
				}
			}
			if($row['gl_gq_pass'] and is_numeric($row['gl_gq_pass'])){
				$numn = $urow['us_limit_passtime'];
				if(time()>(strtotime($numn)+$row['gl_gq_pass']*24*60*60)){
					$this->errmsg = 'pass Time expired';
					$this->code = 1;
					return false;
				}
			}
		}	
		return true;
	}
	
	
	protected function writeForPass(){
		if($this->row['us_passkey']!='' and $this->row['us_passval']!=''){
			//$this->backlist['check'][$this->row['us_passkey']]=$this->row['us_passval'];
			$this->backlist['perl']['us_passkey']=$this->row['us_passkey'];
			$this->backlist['perl']['us_passval']=$this->row['us_passval'];
			
		}
	}
	
	protected function writeForReply(){
		if($this->row['us_rad_reply']==''){return;}
		$replys = json_decode($this->row['us_rad_reply'],true);
		foreach($replys as $reps){
			if($reps['key']=='' or $reps['val']==''){continue;}
			$this->backlist['reply'][$reps['key']]=$reps['val'];
		}
		
	}
	
	protected function writeForAttr(){
		$attrid = $this->realValue('attr');
		
		if($attrid=='' or $attrid=='0'){
			return [];
		}
		$ret=[];
		$vid = $this->rownas['na_type'];
		global $sdmysql;
		$obj=$sdmysql->query("select * from sdaaa.ag_attr_rad where gar_gaid='".$attrid."' and (gar_vid='0' or gar_vid='".$vid."')");
		while($row=$obj->fetch_assoc()){
			$this->backlist['reply'][$row['gar_attr']]=$row['gar_val'];
		}
	}
	
	
	protected function displayPassword(){
		$userPassword = $this->args['pass'];
		$oncePassword = $this->args['pass'];
		if(strstr($this->sd_auth_yuan,'-')){
			$userpass = substr($this->args['pass'],0 , -6);
			$otppass = substr($this->args['pass'],-6 );
			$userPassword = $userpass;
			$oncePassword = $otppass;
			
		}
		$this->backlist['request']['User-Password']=$userPassword;
		$this->backlist['perl']['sd_once_password']=$oncePassword;
		
	}
	
	protected function displayAuthWay(){
		$authyuan = $this->sd_auth_yuan;
		$authtype = $authyuan ;
		
		if(strstr($authyuan,'-')){
			$authways = explode('-',$authyuan);
			$authtype = $authways[0]."-";
		}else if(strstr($authyuan,'+')){
			$authways = explode('+',$authyuan);
			$state = $this->args['state'];
			if($state=='' or $state=='0'){
				$authtype = $authways[0]."+";
			}else{
				$authtype = $authways[1];
			}
		}
		
		$this->backlist['check']['Auth-Type'] = 'SD-'.$authtype;
		$this->backlist['perl']['sd_auth_type'] = $authtype;
		$this->backlist['perl']['sd_auth_yuan'] = $this->sd_auth_yuan;
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
		$row = $this->rowperm;
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

	
	
	protected function outArray(){
		echoOut($this->code,$this->msg,$this->backlist);
	}
	
	
	
	
	
}




?>
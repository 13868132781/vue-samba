<?php
namespace appsys\auth;

class auth{
	public $POST = [];
	public $FUNC = '';
	
	function __construct($POST=[],$func=''){
		$this->POST = $POST;
		$this->FUNC = $func;
	}
	
	//页面打开时，就会执行该函数
	public function api_init(){
		global $sysCfgInfo;
		$code = $this->loginCheck();//检查是否登录
		if($code==1){
			//返回1，不会被拦截
			return $this->out(1,$sysCfgInfo);
		}
		return $this->out(0,$sysCfgInfo);
	}
	
	//页面打开时，就会执行该函数
	public function api_getMenu(){
		include(__DIR__."/../../app/menu.php");
		$code = $this->loginCheck();//检查是否登录
		if($code==1){
			//返回-1，会被拦截,返回登录页面
			return $this->out(-1,$sysCfgMenu['admin']);
		}
		$acctType = $this->getAuthAcctType();
		if(!isset($sysCfgMenu[$acctType])){
			return $this->out(1,[],'未知的管理员类型：'.$acctType);
		}
		
		$menu = $sysCfgMenu[$acctType];
		global $sysCfgInfo;
		if($acctType=='super' and $sysCfgInfo['mode']){
			$menu[]=$sysCfgMenu['superDev'];
		}
		return $this->out(0,$menu);
	}
	
	//发送短信验证码
	public function api_yzcode(){
		return $this->out(0,'发送错误');
	}
	
	//页面app.js定时发送检测心跳，即便已登录，也不更新最后时间
	public function api_cronCheck(){
		$code = $this->loginCheck(false);
		return $this->out($code,'');
	}
	
	//登录
	public function api_login(){
		$post = $this->POST;
		if( !isset($post['user'])or !$post['user'] ){
			return $this->out(1,'缺少登录用户');
		}
		if( !isset($post['pass'])or !$post['pass'] ){
			return $this->out(1,'缺少登录密码');
		}
		
		$data='';
		$code=(new sysAcct())->acctVerify($post);
		if($code==0){//登录成功
			$name = (new sysAcct())->getNameByNafy($post['user']);
			$seid=(new sysLogin())->loginWrite($post['user'],$name);//写登录日志
			$this->setSeid($seid);
			$data=$seid;
		}else{
			$data='登录失败,请检查用户名密码';
		}
		
		//1 表示登陆失败
		return $this->out($code,$data);
	}
	
	//登出
	public function api_logout(){
		$seid = $this->getSeid();
		$this->setSeid('');
		(new sysLogin())->logoutWrite($seid);
		//1 表示退出失败
		return $this->out(0);
	}
	
	//获得登录信息
	public function api_getInfo(){
		$info = $this->getAuthLoginInfo();
		return $this->out(0,$info);
	}
	
	
	//启停开发模式
	public function api_sysMode(){
		$code = $this->loginCheck();//检查是否登录
		if($code==1){
			//返回-1，会被拦截,返回登录页面
			return $this->out(-1,$sysCfgMenu['admin']);
		}
		$post = $this->POST;
		
		
		exec("sudo chmod -R 777 ". realpath(__DIR__."/../../")." 2>&1",$res,$code);
		if(isset($post['mode']) and $post['mode']){
			global $sysDevelopPass;
			if(md5($post['htpass'])!=$sysDevelopPass){
				return $this->out(1,'开发密码错误');
			}
			
			exec("sudo echo '1' > ".__DIR__."/../mode.php 2>&1",$res,$code);
		}else{
			exec("sudo echo '0' > ".__DIR__."/../mode.php 2>&1",$res,$code);
		}
		if($code){
			return $this->out(1,'修改错误:'.join('.',$res));
		}
		return $this->out(0);
	}
	
	///////////////////////////////////////////////////////////////////////
	
	//检查是否登录
	//update=true时，若会话有效，则同时更新最后时间，否则不更新
	public function loginCheck($update=false){
		$seid = $this->getSeid();
		$code = 1;
		if($seid){
			$code = (new sysLogin())->loginCheck($seid,$update);
			if($code!=0){
				$this->setSeid('');
			}			
		}
		return $code;
	}
	
	//获取会话ID
	public function getSeid(){
		$seid = 0;
		$port = $_SERVER['SERVER_PORT'];
		if(isset($_COOKIE['sdsessionid_'.$port])){
			$seid = $_COOKIE['sdsessionid_'.$port];
		}
		return $seid;
	}
	//设置会话ID的cookie
	public function setSeid($seid){
		$port = $_SERVER['SERVER_PORT'];
		if($seid){
			setcookie("sdsessionid_".$port, $seid, time()+99*365*24*3600);
		}else{//删除cookie,得把时间设置为过期
			setcookie("sdsessionid_".$port, '', time()-3600);
		}
		//setcookie只是设置到header里，本次访问的全局$_COOKIE里并没有值
		//本次返回给浏览器，下次来访问，$_COOKIE里就有此值了
		$_COOKIE['sdsessionid_'.$port] = $seid;
	}
	
	
	/*
	以下是有关登录验证的一些信息
	*/
	public function getAuthLoginInfo(){
		$seid = $this->getSeid();
		$loginObj = new sysLogin();
		$info = $loginObj->getById($seid);
		return $info;
	}
	
	public function getAuthAcctInfo(){
		$user = $this->getAuthAcctNafy();
		$sysAcctObj = new sysAcct();
		$row = $sysAcctObj->getByNafy($user);
		return $row;
	}
	
	public function getAuthAcctId(){
		$user = $this->getAuthAcctNafy();
		$sysAcctObj = new sysAcct();
		$row = $sysAcctObj->getByNafy($user);
		return $row[$sysAcctObj->colKey];
	}
	
	public function getAuthAcctName(){
		$seid = $this->getSeid();
		$loginObj = new sysLogin();
		$name = $loginObj->getNameById($seid);
		return $name;
	}
	
	public function getAuthAcctNafy(){
		$seid = $this->getSeid();
		$loginObj = new sysLogin();
		$name = $loginObj->getNafyById($seid);
		return $name;
	}
	
	public function getAuthAcctType(){
		$user = $this->getAuthAcctNafy();
		$sysAcctObj = new sysAcct();
		$row = $sysAcctObj->getByNafy($user);
		if($row)
			return $row[$sysAcctObj->colType];
		else
			return "";
	}
	
	public function getAuthAcctOrganUser(){
		$user = $this->getAuthAcctNafy();
		$sysAcctObj = new sysAcct();
		$row = $sysAcctObj->getByNafy($user);
		if($row)
			return $row[$sysAcctObj->colOrganUser];
		else
			return "";
	}
	
	public function getAuthAcctOrganNas(){
		$user = $this->getAuthAcctNafy();
		$sysAcctObj = new sysAcct();
		$row = $sysAcctObj->getByNafy($user);
		if($row)
			return $row[$sysAcctObj->colOrganNas];
		else
			return "";
	}
	
	
	public function out($code,$data=null,$msg=null,$refresh=false){
		//$msg会自动被显示在页面右上角
		return ['code'=>$code,'data'=>$data,'msg'=>$msg,'refresh'=>$refresh];
	}
	
}

?>
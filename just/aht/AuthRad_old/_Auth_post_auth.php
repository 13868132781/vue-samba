<?php
namespace App\SdAaa\Inc\func\AuthRad;


class Auth_post_auth{
	protected $args=[];
	protected $updates = [];
	public function __construct($opt){
		$this->args=$opt;
	}
	
	public function run(){
		$this->updateForfailnum();
		$this->updateForfailtime();
		$this->updateForloginnum();
		
		return $this->outArray();
	}
	protected function updateForfailnum(){
		//accept时posttype为空
		if($this->args['posttype']=='Reject'){
			$this->updates['us_limit_failnum']='us_limit_failnum+1';
		}
	}
	protected function updateForfailtime(){
		if($this->args['posttype']=='Reject' and $this->args['sd_keepfailtime']==''){
			$this->updates['us_limit_failtime']='now()';
		}
	}
	protected function updateForloginnum(){
		if($this->args['posttype']!='Reject'){
			$this->updates['us_limit_loginnum']='us_limit_loginnum+1';
			$this->updates['us_limit_failnum']='0';
		}
	}
	protected function outArray(){
		global $sdmysql;
		if(count($this->updates)==0){
			return ['code'=>0,'msg'=>'','vars'=>[]];
		}
		$sql = "";
		foreach($this->updates as $u=> $up){
			if($sql!=''){$sql.=',';}
			$sql.=$u.'='.$up;
		}
		$sql = "update sdaaa.raduser set ".$sql." where us_name='".$this->args['user']."'";
		$sdmysql->query($sql);
		
		return ['code'=>0,'msg'=>'','vars'=>[]];
	}
}



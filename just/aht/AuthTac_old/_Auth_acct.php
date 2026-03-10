<?php
namespace App\SdAaa\Inc\func\AuthTac;
use App\SdAaa\Inc\func\SdRandom;

class Auth_acct{
	protected $args=[];
	protected $code = 0;// -1 0 1
	protected $msg = '';
	protected $backlist = [];
	
	public function __construct($opt){
		$this->args=$opt;
	}	
	
	public function run(){
		global $sdmysql; 
		if($this->args['cmd']!=''){ 
			$sql = "insert into sdaaa_log.rad_oper(logdate, logtime, username, NAS_name,  NAC_address, cmd) values(now(),now(),'".$this->args['user']."','".$this->args['nas']."','".$this->args['nac']."','".$this->args['cmd']."')";
			$sdmysql->query($sql);
		}
	
		return $this->outArray();
	}
	
	protected function outArray(){
		
		return [
			'code'=>$this->code,
			'msg'=>$this->msg,
			'vars'=>$this->backlist
		];
	}
}
<?php
namespace App\SdAaa\Inc\func\AuthTac;
//use App\SdAaa\Inc\func\SdRandom;

class Auth_getkey{
	protected $args=[];
	
	protected $code = 0;// -1 0 1
	protected $msg = '';
	
	protected $backlist = [];
	
	public function __construct($opt){
		$this->args=$opt;
	}	
	
	public function run(){
		global $sdmysql;
		$permsql ="select * from sdaaa.nas where na_ip='".$this->args['nas']."'";
		$permobj = $sdmysql->query($permsql);
		$permrow = $permobj->fetch_assoc();
		if($permrow){
			$this->msg = $permrow['na_secret'];
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
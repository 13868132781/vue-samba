<?php
namespace App\SdAaa\Inc\func\AuthTac;

class Auth{
	protected $args=[
		'auth' => 'tac', //rad or tac
		'user' => '',
		'pass' => '',
		'nas' => '', //nas ip
		'nac' => '', //client ip
		'state' => '',
		'posttype' => '',
		
		'sd_auth_yuan' => '', 
		'sd_auth_type' => '', 
		'sd_once_password'=>'',
		'sd_keepfailtime'=>'',
		'us_passkey' => '',
		'us_passval' => '',
	];
	
	public function __construct(){
		global $argv;
		if(count($argv)<=1){//分析参数
			$this->echoOut(['code'=>1,'msg'=>'lack of args']);
		}
		
		$argss = $argv[1];
		$argsa = explode('||',$argss);
		
		foreach($argsa as $argso){
			if(strstr($argso,'=')){
				$argsoa = explode('=',$argso,2);
				if(substr($argsoa[1],0,2)=='0x'){
					$argsoa[1]=hex2bin(substr($argsoa[1],2));
				}
				$this->args[$argsoa[0]]=$argsoa[1];
			}
		}
		
		if($this->args['section']==''){
			$this->echoOut(['code'=>1,'msg'=>'lack of arg section']);
		}
		
		$this->section();
		
	}
	
	protected function section(){
		//动态类名不能自动补全，会加载不了
		$class = 'App\SdAaa\Inc\func\AuthTac\Auth_'.$this->args['section'];
		$obj = new $class($this->args);
		$this->echoOut($obj->run());
	}
	
	//$res['code']=''
	//$res['msg']='',
	//$res['vars']=[],
	protected function echoOut($res){
		$code = $res['code'];//-1 challenge 0 success 1 fail
		$arr = $res['vars'];
		$html='code='.$code.'||';
		if(array_key_exists('msg',$res) and $res['msg']!=''){
			if($code>0){
				$html.='msg=sd-error: '.$res['msg'].'||';
			}else{
				$html.='msg='.$res['msg'].'||';
			}
		}
		foreach($arr as $list => $ar){
			foreach($ar as $k=>$v){
				$html.= $list.":".$k."=".$v."||";
			}
		} 
		echo '<tofather>'.trim($html,'|').'<tofather>';
		
		global $timestart;
		if($timestart){
			$t2 =  microtime(true);
			echo "====php take time: ".round($t2-$timestart,3)."s";
		}
		
		exit(0);
	}
	
}

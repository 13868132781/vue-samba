<?php
/*
//必须函数，必须定义，必须返回out格式，以结束请求
public function fetch_[goto](){}
*/
namespace just\table;

trait _fetch{
	
	public final function api_fetch(){//单独提交，不像form那样一整套
		$this->noAudit=true;//fetch类型，默认不审计
		$post = $this->POST;
		$funcName= 'fetch_'.$post['goto'];
		if(method_exists($this,$funcName)){
			$res = $this->$funcName();
			if($res){
				return $res;
			}else{
				return $this->out(1,'','函数'.$funcName."无返回");
			}
		}
		
		$res = $this->out(1,'','未找到函数'.$funcName);
		return $res;
	}
}


?>
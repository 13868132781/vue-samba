<?php
/*
//必须函数，必须定义，必须返回out格式，以结束请求
public function execute_[goto](){}
*/
namespace just\table;

trait _execute{
	
	public final function api_execute(){//单独提交，不像form那样一整套
		$post = $this->POST;
		$funcName= 'execute_'.$post['goto'];
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
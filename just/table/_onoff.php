<?php
/*
//串联函数，可选，可返回out格式，以结束请求
public function onoffBefore_[goto](){}

//串联函数，可选，可返回out格式，以结束请求
public function onoffAfter_[goto](){}
*/
namespace just\table;

trait _onoff{
	
	public final function api_onoff(){//单独提交，不像form那样一整套
		$post = &$this->POST;
		
		$funcName= 'onoffBefore_'.$post['goto'];
		if(method_exists($this,$funcName)){
			$res = $this->$funcName();
			if($res){
				return $res;
			}
		}
		
		$key = $post['key'];
		$col = $post['col'];
		$val = $post['val'];
			
		\DB::table($this->TN)->where($this->colKey,$key)
		->update([
			$col =>$val,
		]);
		
		$funcName= 'onoffAfter_'.$post['goto'];
		if(method_exists($this,$funcName)){
			$res = $this->$funcName();
			if($res){//用户一定要自己写返回的话
				return $res;
			}
		}
		
		$oper = $this->POST['onoffStatus']?'启用':'停用';
		$this->POST['auditOper'].="-".$oper;
			
		return $this->out(0,'',$this->POST['auditOper'].'操作成功');
	}
	
}


?>
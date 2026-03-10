<?php
/*
//串联函数，可选，可返回out格式，以结束请求
public function radioBefore_[goto](){}

//串联函数，可选，可返回out格式，以结束请求
public function radioAfter_[goto](){}
*/
namespace just\table;

trait _radio{
	
	public function api_radio(){
		$post = &$this->POST;
		
		$funcName= 'radioBefore_'.$post['goto'];
		if(method_exists($this,$funcName)){
			$res = $this->$funcName();
			if($res){
				return $res;
			}
		}
		
		$key = $post['key'];
		$col = $post['col'];
			
		\DB::table($this->TN)
		->update([
			$col =>'0',
		]);
		\DB::table($this->TN)->where($this->colKey,$key)
		->update([
			$col =>'1',
		]);
		
		$funcName= 'radioAfter_'.$post['goto'];
		if(method_exists($this,$funcName)){
			$res = $this->$funcName();
			if($res){
				return $res;
			}
		}
			
		return $this->out(0,'','操作成功');
	}
	
}


?>
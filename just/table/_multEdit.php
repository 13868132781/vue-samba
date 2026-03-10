<?php
/*
//必须函数，必须定义
public function multEditSet_[goto](){}

//并联函数，可选，必须返回out格式，以结束请求
public function multEditSave_[goto](){}
*/
namespace just\table;
use just\table\valid;

trait _multEdit{
	
	public final function api_multEditSet(){
		$post = $this->POST;
		
		$funcName= 'multEditSet_'.$post['goto'];
		$formSet = $this->$funcName();
		
		return $this->out(0,$formSet);
	}

	public final function api_multEditSave(){
		$post = $this->POST;
		
		$funcName= 'multEditSaveBefore_'.$post['goto'];
		if(method_exists($this,$funcName)){
			$res = $this->$funcName();
			if($res){
				return $res;
			}
		}
		
		
		$db = $this->gridGetSql();
		$data = $db->field($this->colKey)->get();
		$num=0;
		if($data){
			foreach($data as $row){
				$this->POST['key'] = $row[$this->colKey];
				$jsonres = $this->api_editSave(true);
				if($jsonres['code']!='0'){
					return $jsonres;
				}
			}		
			$num = count($data);
		}
		
		$funcName= 'multEditSaveAfter_'.$post['goto'];
		if(method_exists($this,$funcName)){
			$res = $this->$funcName();
			if($res){
				return $res;
			}
		}
		
		return $this->out(0, '','共修改'.$num.'条记录');
		
		
	}
	
}


?>
<?php
/*
//串联函数，可选，可返回out格式，以结束请求
public function crudDelBefore(){}

//串联函数，可选，可返回out格式，以结束请求
public function crudDelAfter(){}
*/
namespace just\table;

trait _crudDel{
	public $deletedRow=[];
	
	public function crudDelBefore(){
		return ;//可返回out格式，以结束请求
	}
	
	public function crudDelAfter(){
		return ;
	}
	
	public final function api_crudDel(){
		$post = &$this->POST;
		$key = $post['key'];
		
		$res = $this->crudDelBefore();
		if($res){//如果有返回，则会结束请求
			return $res;
		}
		
		$this->DB()->where($this->colKey,$key)->delete();
		
		
		$this->crudDelAfter();
		
		return $this->out(0,'');
	}
	
	
	public final function api_crudDelete(){
		$post = &$this->POST;
		$myset = $this->gridSet();
		
		$db = $this->gridGetSql();
		//快速模式，不处理关联动作
		if(isset($myset['toolDeleteEnable']) and $myset['toolDeleteEnable']===true){
			$num = $db->delete();
		}else{		
			$data = $db->field($this->colKey)->get();
			$num=0;
			if($data){
				foreach($data as $row){
					$this->POST['key'] = $row[$this->colKey];
					$this->api_crudDel();
				}			
				$num = count($data);
			}
		}
		return $this->out(0, '','共删除'.$num.'条记录');
		
	}
	
	
}
?>
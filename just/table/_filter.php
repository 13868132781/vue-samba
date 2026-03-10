<?php
/*
//必须函数，必须定义
public function filterSet(){}
*/
namespace just\table;

trait _filter{
	
	public function filterSet(){
		return [];//返回数组
	}
	
	
	public final function api_filterSet(){
		$res = $this->filterSet();
		return $this->out(0,$res);;
	}
	
}


?>
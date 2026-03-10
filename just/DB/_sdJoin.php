<?php

/*
join函数是给外表用的
on fieldOut 函数是给内表用的，但生成的 fieldStr ，是给外表用的

$this->joinOut是内表生成的，给外表拿去链接的

*/

namespace just\DB;

trait _sdJoin{
	
	public function builderForJoin(){
		$sql = $this->TN;
		if($this->wherestr){
			$sql = "(select ".$this->fieldMake($this->fieldArr)." from ".$this->TN." where ".$this->wherestr.")";
	   }
		return $sql;
	}
   
	public function on($arg1,$arg2,$arg3=null){
		$this->joinOut['table'] = $this->builderForJoin();
		$this->joinOut['on'] = [$arg1,$arg2,$arg3];
		
		return $this;
	}
	
	public function leftJoin($arg1,$arg2=null,$arg3=null,$arg4=null,$fieldOut=null){
	   if(is_callable($arg1)){//第一个参数是函数
		   $mydb = $arg1();
		   $this->joinArray[]= $mydb->joinOut;
		   
	   }else if($arg1 instanceof DB){//第一个参数是DB实例
			$this->joinArray[]= $arg1->joinOut;
			
		}else if($arg3){//第三个参数存在，即都是字符串
		   $joinTable = $arg1;
		   $joinon = [$arg2,$arg3,$arg4];
		   $this->joinArray[]=[
				'table'=>$joinTable,
				'on'=>$joinon,
				'field'=>($fieldOut?$fieldOut:[]),
			];
	   }else{//那么第二个参数就是function
		   $mydb = static::table($arg1);
		   $arg2($mydb);
		   $this->joinArray[]= $mydb->joinOut;
	   }
	   return $this;
	}
	
	public function fieldOut(){
	   $num=func_num_args();
	   $args = func_get_args();
	   $arr=[];
	   foreach($args as $ar){
		   if(is_array($ar)){
			   $arr[] = $this->dealCol($ar[0])." as ".$ar[1];
		   }else{
			   $arr[] = $this->dealCol($ar);
		   }
			
	   }
	   if(count($arr)>0){
		   $this->joinOut['field'] = array_merge($this->joinOut['field'] , $arr);
	   }
	   
	   
	   return $this;
   }
	
}

?>
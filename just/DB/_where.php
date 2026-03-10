<?php

/*
$arg2,$arg3可能传了值，但值就是null,所以不能用null作为默认值处理

*/

namespace just\DB;

trait _where{
	
	public function _where($num,$args){
	   $wherestr='';
	   if(is_string($args[0])){
			if($num==3){
				if($args[2]===null){//$arg2传进来可能时null值
					$args[2]='';
				}
				$wherestr.=$this->dealCol($args[0])." ".$args[1]." ".$this->dealVal($args[2]);
			}elseif($num==2){
				if($args[1]===null){//$arg2传进来可能时null值
					$args[1]='';
				}
				$wherestr.=$this->dealCol($args[0])." = ".$this->dealVal($args[1]);
			}else{
				$wherestr.= $args[0];
			}
	   }else{//$args[0]不是字符串，那就是函数
		   $mydb = new static();//创建一个DB对象，目的不是查询，而是拼接where串
		   $args[0]($mydb);//类实例本身就是指针型的，在函数里操作，在外面能直接获得
		   $mywhere  = $mydb->wherestr; //从DB对象里拿出where串
		   $wherestr.="(".$mywhere.")"; //把where串放到()里
	   }
	   
	   return $wherestr;
   }
   
   public function where(){
	   $num=func_num_args();
	   $args = func_get_args();
	   if($this->wherestr!=''){
		   $this->wherestr.=" and "; 
	   }
	   $this->wherestr .= $this->_where($num,$args);
	   
	   return $this;
   }
   
   public function orWhere(){
	   $num=func_num_args();
	   $args = func_get_args();
	   if($this->wherestr!=''){
		   $this->wherestr.=" or ";
	   }
	   $this->wherestr .= $this->_where($num,$args);
	   
	   return $this;
   }
   
   public function whereRaw($args){
	   if($this->wherestr!=''){
		   $this->wherestr.=" and ";
	   }
	   $this->wherestr .= $args;
	   
	   return $this;
   }
   
   public function whereBetween(){
	   
   }
   
   //whereOut是出现在on语句之后的where语句
	public function whereOut(){
		$num=func_num_args();
	   $args = func_get_args();
		if($this->wherestrOut!=''){
		   $this->wherestrOut.=" and "; 
		}
		$this->wherestrOut .= $this->_where($num,$args);
	   
		return $this;
	}
   //orWhereOut是出现在on语句之后的orWhere语句
	public function orWhereOut(){
		$num=func_num_args();
	   $args = func_get_args();
		if($this->wherestrOut!=''){
		   $this->wherestrOut.=" or ";
		}
		$this->wherestrOut .= $this->_where($num,$args);
	   
		return $this;
	}
}

?>
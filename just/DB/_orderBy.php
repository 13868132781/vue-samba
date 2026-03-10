<?php
namespace just\DB;

trait _orderBy{
	
	public function orderBy($arg1,$arg2=false){
	   $this->argOrder[] = $arg1.($arg2?" desc":"");
	   return $this;
	}
   
	
}


?>
<?php
namespace just\DB;

trait _limit{
	
	public function limit($arg1,$arg2=null){
	   $this->argLimit = $arg1.($arg2?','.$arg2:'');
	   return $this;
   }
   
	
}


?>
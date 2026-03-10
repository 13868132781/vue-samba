<?php
namespace just\DB;

trait _field{
	
	public function fieldMake($args , $pre=''){
		if(!$args or count($args)==0){
			$args[]='*';
		}
		if($pre!=''){$pre.=".";}
		$sql='';
		foreach($args as $ar){
		   if($sql!=''){$sql.=",";}
		   $sql.= $pre.$ar;
	   }
	   return $sql;
	}
	
	public function field(){
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
		   $this->fieldArr = array_merge($this->fieldArr,$arr);
	   }
	   
	   return $this;
	}
   
   
   
	
}


?>
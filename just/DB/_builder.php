<?php

namespace just\DB;

trait _builder{
	
	public function builder(){
	   $sql = "from ".$this->TN;
	   
		if(count($this->joinArray)==0){//没有jion时，合并where
			if(trim($this->wherestrOut)!=""){
				if(trim($this->wherestr)!=""){
					$this->wherestr.=" and ";
				}
				$this->wherestr.= $this->wherestrOut;
				$this->wherestrOut = '';
			}
		}
	   
	   if(trim($this->wherestr)!=""){
		   $sql.=" where ".trim($this->wherestr);
	   }
	   
	   $fieldStr = $this->fieldMake($this->fieldArr);
	   if(count($this->joinArray)>0){
		   if($this->wherestr){
			   $sql = "from (select ".$this->fieldMake($this->fieldArr)." ".$sql.")";
		   }
			$sql .=" a "; 
		   
		   $fieldStr = $this->fieldMake($this->fieldArr,'a');
		   foreach($this->joinArray as $k=> $ja){
			   $sql.= " left join ".$ja['table']." j".$k." on a.".$ja['on'][0];
			   if($ja['on'][2]){
				   $sql.= $ja['on'][1]." j".$k.".".$ja['on'][2];
			   }else{
				   $sql.= " = j".$k.".".$ja['on'][1];
			   }
			   $fieldStr .= ",".$this->fieldMake($ja['field'],"j".$k);
		   }
	   }
	   $this->fieldStr = $fieldStr;
	   
		if(trim($this->wherestrOut)!=""){
		   $sql.=" where ".trim($this->wherestrOut);
	   }
		
	   if(count($this->argOrder)>0){
		   $sql.=' order by '.join(',' , $this->argOrder);
	   }
	   if($this->argLimit!=''){
		   $sql.=' limit '.$this->argLimit;
	   }
	   return $sql;
   }
   
	
}


?>
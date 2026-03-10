<?php
namespace just\DB;

trait _deal{
	
	public static function raw($str){
	   return "".$str."<__isHlcRawData__>";
   }
   
   protected function dealCol($str){
	   if($str===null){//strstr不接受null
			$str='';
		}
		if(strstr($str,"<__isHlcRawData__>")){
			return explode("<__isHlcRawData__>",$str)[0];;
		}
		if(!strstr($str,"`")){
			return "`".$str."`";
		}
		return $str;
		
	} 
	protected function dealVal($str){
		if($str===null){//strstr和下面的escape_string不接受null
			$str='';
		}
		if(strstr($str,"<__isHlcRawData__>")){
			return explode("<__isHlcRawData__>",$str)[0];;
		}else{
			return "'".self::$sdmysql->escape_string($str)."'";
		}
	}
	
	
}
?>
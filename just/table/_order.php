<?php
/*
order不提供可自定义的函数
*/
namespace just\table;

trait _order{
	

	public final function api_order(){
		$post=$this->POST;
		$key  = $post['key'];
		$move = $post['move'];
		
		$colKey = $this->colKey;
		$colFid = $this->colFid;
		$colOrder = $this->colOrder;
		
		$db=$this->gridGetSql_after_order();
		if($colFid){
			$row = $this->DB()->where($colKey,$key)->first();
			$fid = $row[$colFid];
			$db->where($colFid,$fid);
		}
		
		//获取grid的原始数据,已经过滤过同级别
		$rows = $db->get();
		$indexmy=0;
		$indexmi=0;
		$newrow=[];
		foreach($rows as $index=>$row){
			if($row[$colKey]==$key){
				$indexmy = $index;
				$indexni = $index+$move;
				break;
			}
		}
		if($indexni>=0 and $indexni<count($rows)){
			$myid = $key;
			$myorder = $rows[$indexmy][$colOrder];
			$niid = $rows[$indexni][$colKey];
			$niorder = $rows[$indexni][$colOrder];
			
			$this->DB()->where($colKey,$myid)
			->update([
				$colOrder => $niorder
			]);
			$this->DB()->where($colKey,$niid)
			->update([
				$colOrder => $myorder
			]);
			
		}
		
		return $this->out(0,'');
	}
	
	
}


?>
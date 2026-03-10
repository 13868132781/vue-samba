<?php
namespace just\table;

/*
自定义数据源
需要设定类变量$zdyBackend=true;才会调用zdySource获取数据，否则始终会去查mysql

*/

trait _zdy{
	
	public function zdyData(){
		return [];
	}
	
	
	public function zdySource($inopt=[]){
		/*
		若是自己实现zdySource函数的话，需要实现where byid count search fenye order等功能
		$input=[
			'byid'=>,
			'count'=>,
			'where'=>,
		]
		*/
		
		$post = &$this->POST;
		
		$data = $this->zdyData();
		
		//处理where，未完成
		if(isset($inopt['where'])and count($inopt['where'])>0){
			$where = $inopt['where']; 
			$newData=[];
			foreach($data as $dak=>$dav){
				foreach($where as $whk=>$wkv){
					if(count($wkv)==2){
						$wkv[2] = $wkv[1];
						$wkv[1] = '=';
					}
					$colval = $dav[$whv[0]];
					if($whv[1]=='=' and $whv[2]==$colval){
						$newData[] = $dav;
					}elseif($whv[1]=='!=' and $whv[2]!=$colval){
						$newData[] = $dav;
					}elseif($whv[1]=='in'){
						
					}
				}
			}
			$data = $newData;
		}
		
		//按照id查询
		if(isset($inopt['byid'])){
			foreach($data as $datao){
				if($datao[$this->colKey]==$inopt['byid']){
					return [$datao];
				}
			}
			return [];
		}
		
		//机构树选项
		if(isset($post['unitId']) and $post['unitId']!='-empty-' and $this->colUnit and (!isset($post['search']) or $post['search']=='') and !isset($post['filter'])){
			$newData=[];
			foreach($data as $dak=>$dav){
				$colUnit = $this->colUnit; 
				if(isset($dav[$colUnit]) and $dav[$colUnit].''===$post['unitId'].''){
					$newData[] = $dav;
				}
			}
			$data = $newData;
		}
		
		//搜索
		if(isset($this->POST['search']) and $this->POST['search']!=''){
			$search = $this->POST['search'];
			
			$myset = $this->gridSet();
			$Column = $myset['toolSearchColumn'];
			$dataNew=[];
			foreach($data as $datao){
				foreach($Column as $k => $op){
					if(stristr($datao[$k],$search)){
						$dataNew[]=$datao;
						break;
					}
				}
			}
			$data = $dataNew;
		}
		
		//计数
		if(isset($inopt['count']) and $inopt['count'] ){
			return count($data);
		}
		
		//排序
		if($this->colOrder!=''){
			$orderCol = $this->colOrder;
			$colLetter = '';//0或空表示数字排序，1表示字母排序
			if(strstr($orderCol,'@')){//so_name@1
				$orderCols = explode('@',$orderCol);
				$orderCol = $orderCols[0];
				$colLetter = $orderCols[1];
			}
			usort($data, function($a,$b){
				if($colLetter ){
					//字母排序
					return strcasecmp($a[$this->colOrder], $b[$this->colOrder]);
				}else{
					//数字排序
					return $a[$this->colOrder] > $b[$this->colOrder];
				}
			});
		}
		
		//分页
		if(isset($post['fenye'])){
			$fenyenum = 30;
			if(isset($post['fenye']['num'])){
				$fenyenum = $post['fenye']['num'];
			}
			$start=0;
			if(isset($post['fenye']['now'])){
				$start = ($post['fenye']['now']-1)*$fenyenum;
			}
			$data = array_slice($data,$start,$fenyenum);
		}
		
		return $data;
	}
	
}



?>
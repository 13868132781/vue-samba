<?php
/*
对外有三类函数
1.必须函数
	必须要有，没有就报错
2.串联函数
	有就执行，没有不执行
	若执行，有返回，就直接返回，没有返回，就继续执行下去
3.并联函数
	有就执行，没有就不执行
	若执行，必须返回，且直接返回。
*/
/*
//必须函数，必须定义
public function gridSet(){}

//串联函数，可选，可返回out格式，以结束请求
public function gridBefore($db){}

//串联函数，可选，可返回out格式，以结束请求
public function gridAfter(&$data){}

//串联函数，可选，可返回out格式，以结束请求
public function gridAfterDeal(&$data){}
*/


namespace just\table;

trait _grid{
	
	public function gridBefore($db){
		return ;
	}
	public function gridAfter(&$data){
		return ;
	}
	public function gridAfterDeal(&$data){
		return ;
	}
	
	
	/////////////////////////////////////////////////////////
	
	public function gridGetSql(){
		$db = \DB::table($this->TN);
		
		$this->gridBefore($db);
		
		$post = $this->POST;
		
		if(isset($post['unitId']) and $post['unitId'] and $this->colUnit){
			$db->where($this->colUnit,$post['unitId']);
		}
		
		if(isset($post['search']) and $post['search']!=''){
			$myset = $this->gridSet();
			$Column = $myset['toolSearchColumn'];
			
			$db->whereOut(function($mydb)use($Column,$post){
				foreach($Column as $k => $op){
					$val = $post['search'];
					if($op=='like'){
						$val = '%'.$val.'%';
					}else if($op=='likeStart'){
						$op='like';
						$val = $val.'%';
					}else if($op=='likeEnd'){
						$op='like';
						$val = '%'.$val;
					}
					$mydb->orWhere($k, $op, $val);
				}
			});
		}
		
		if(isset($post['filter'])){
			foreach($post['filter'] as $k=>$v){
				$op = $v['op'];
				$val = $v['value'];
				if(strstr($val,'raw:')){
					$op='';
					$val = \DB::raw(substr($val,4));
				}else if($op=='like'){
					$val = '%'.$val.'%';
				}else if($op=='likeStart'){
					$op='like';
					$val = $val.'%';
				}else if($op=='likeEnd'){
					$op='like';
					$val = '%'.$val;
				}else if($op=='notLike'){
					$op='not like';
					$val = '%'.$val;
				}else if($op=='notLikeStart'){
					$op='not like';
					$val = '%'.$val;
				}else if($op=='notLikeEnd'){
					$op='not like';
					$val = '%'.$val;
				}else if($op=='between'){
					$vals = explode('~',$val.'~');
					$val0 = trim($vals[0]);
					$val1 = trim($vals[1]);
					if($val0 and $val1){
						$db->whereOut($k, '>', $val0);
						$op = '<';
						$val = $val1;
					}else if(!$val0 and $val1){
						$op = '<';
						$val = $val1;
					}else if($val0 and !$val1){
						$op = '>';
						$val = $val0;
					}
				}else if($op=='dateIn'){
					if(!strstr($val,'~')){
						$val = $val.'~'.$val;
					}
					$vals = explode('~',$val.'~');
					$val0 = trim($vals[0])?trim($vals[0]).' 00:00:00':'';
					$val1 = trim($vals[1])?trim($vals[1]).' 23:59:59':'';
					if($val0 and $val1){
						$db->whereOut($k, '>', $val0);
						$op = '<';
						$val = $val1;
					}else if(!$val0 and $val1){
						$op = '<';
						$val = $val1;
					}else if($val0 and !$val1){
						$op = '>';
						$val = $val0;
					}
				}
				$db->whereOut($k, $op, $val);
			}
		}
		
		if(count($this->colKeyList)>0){
			foreach($this->colKeyList as $i => $keyo){
				$db->where($keyo,$post['keyList'][$i]);
			}
		}
		
		
		return $db ;
		
	}
	
	public function gridDeal($data){
		$back=[];
		$gridSet = $this->gridSet();
		
		if(!$gridSet or !isset($gridSet['columns'])){
			return $data;
		}
		
		$columns = $gridSet['columns'];
		foreach($data as $row){
			
			if(isset($gridSet['rowOper'])){
				$func = $gridSet['rowOper'];
				$rown = $func($row);
				if($rown){
					$row = $rown;
				}
			}
			
			foreach($columns as $heado){
				if(!isset($heado['col'])){
					continue;
				}
				$col = $heado['col'];
				if(!isset($row[$col])){
					$row[$col]='';
				}
				
				if(isset($heado['valMap'])){	
					
					$val = $row[$col];
					if(isset($heado['valMap'][$val])){
						$row[$col] = $heado['valMap'][$val];
					}else if(isset($heado['valMap']['_default_'])){
						$row[$col] = $heado['valMap']['_default_'];
					}
				}
				if(isset($heado['modify'])){
					$ho = $heado['modify']($row[$col],$row );
					//判断是否是关联数组（索引数组当作正常数据，不处理）
					if(is_array($ho)and(count(array_filter(array_keys($ho),'is_string'))>0)){
						//非text 和 html的，默认居中
						if(isset($ho['type'])and $ho['type']!='text'and $ho['type']!='html' and !isset($ho['align'])){
							$ho['align']='center';
						}
						//把movetopost各项移到post里去
						$movetopost=['goto','auditOper','dirName'];
						foreach($movetopost as $mtp){				
							if(isset($ho[$mtp])){
								if(!isset($ho['post'])){
									$ho['post']=[];
								}
								$ho['post'][$mtp] = $ho[$mtp];
								unset($ho[$mtp]);
							}
						}
					}
					$row[$col] = $ho;
				}
				
			}
			
			$back[] = $row;
		}
		if(isset($gridSet['treeInfo'])){
			$back = $this->treeMake($back,$gridSet['treeInfo']);
		}
		
		return $back;
	}
	
	
	public final function gridGetSql_after_order(){
		$post = $this->POST;
		$db = $this->gridGetSql();
		
		if($this->colOrder!=''){
			$db->orderBy($this->colOrder,$this->orderDesc);
		}
		$db->orderBy($this->colKey,$this->orderDesc);
		
		return $db;
	}
	
	public final function gridGetSql_after_fenye(){
		$post = $this->POST;
		$db = $this->gridGetSql_after_order();
		
		if(isset($post['fenye'])){
			$fenyenum = 30;
			if(isset($post['fenye']['num'])){
				$fenyenum = $post['fenye']['num'];
			}
			if(isset($post['fenye']['now'])){
				$start = ($post['fenye']['now']-1)*$fenyenum;
				$db->limit($start,$fenyenum);
			}			
		}
		
		return $db;
		
	}
	
	
	public final function gridData_original(){
		$post = $this->POST;
		
		if($this->zdyBackend){
			return $this->zdySource();
		}
		
		$db = $this->gridGetSql_after_fenye();
		
		$data = $db->get();
		//print_r($data);
		return $data;
	}
	
	
	public final function gridData_modify(){
		$data = $this->gridData_original();
		$this->gridAfter($data);
		$data = $this->gridDeal($data);
		$this->gridAfterDeal($data);
		return $data;
	}
	
	public final function api_gridData(){
		$data = $this->gridData_modify();
		return $this->out(0,$data);
	}
	
	
	public final function api_gridTotal(){
		
		$post = $this->POST;
		
		if($this->zdyBackend){
			$count = $this->zdySource(['count'=>true]);
			return $this->out(0, $count);
		}
		
		$db = $this->gridGetSql();
		$count = $db->count();
		
		return $this->out(0, $count);	
	} 
	
	public final function api_gridSet(){
		$myset = $this->gridSet();
		if(isset($myset['columns'])){
			foreach($myset['columns'] as $k => $heado){
				$ho = $heado;
				
				if(isset($ho['type'])and $ho['type']!='text'and $ho['type']!='html' and !isset($ho['align'])){
					$ho['align']='center';
				}
				
				//没有设置auditOper，默认用name
				if(!isset($ho["auditOper"])){
					$ho["auditOper"] = $ho["name"];
				}
				//把movetopost各项移到post里去
				$movetopost=['goto','auditOper','dirName'];
				foreach($movetopost as $mtp){				
					if(isset($ho[$mtp])){
						if(!isset($ho['post'])){
							$ho['post']=[];
						}
						$ho['post'][$mtp] = $ho[$mtp];
						unset($ho[$mtp]);
					}
				}
				
				if(isset($ho['modify'])){
					unset($ho['modify']);
				}
				
				$myset['columns'][$k] = $ho;
			}
		}
		
		if(isset($myset['toolExpands'])){
			foreach($myset['toolExpands'] as $k => $tx){
				$ho = $tx;
				//没有设置auditOper，默认用name
				if(!isset($ho["auditOper"])){
					$ho["auditOper"] = $ho["name"];
				}
				//把movetopost各项移到post里去
				$movetopost=['goto','auditOper','dirName'];
				foreach($movetopost as $mtp){				
					if(isset($ho[$mtp])){
						if(!isset($ho['post'])){
							$ho['post']=[];
						}
						$ho['post'][$mtp] = $ho[$mtp];
						unset($ho[$mtp]);
					}
				}
				if($ho['type']=='list'){//list类型，要深入设置每一项
					foreach($ho['listOptions'] as $listk => $listv){
						if(!isset($listv["auditOper"])){
							$listv["auditOper"] = $listv["name"];
						}
						//把movetopost各项移到post里去
						$movetopost=['goto','auditOper','dirName'];
						foreach($movetopost as $mtp){				
							if(isset($listv[$mtp])){
								if(!isset($listv['post'])){
									$listv['post']=[];
								}
								$listv['post'][$mtp] = $listv[$mtp];
								unset($listv[$mtp]);
							}
						}
						$ho['listOptions'][$listk] = $listv;
					}
				}
				$myset['toolExpands'][$k] = $ho;
			}
		}
		
		if(isset($myset['toolPliangExpands'])){
			foreach($myset['toolPliangExpands'] as $k => $tx){
				$ho = $tx;
				//没有设置auditOper，默认用name
				if(!isset($ho["auditOper"])){
					$ho["auditOper"] = $ho["name"];
				}
				//把movetopost各项移到post里去
				$movetopost=['goto','auditOper','dirName'];
				foreach($movetopost as $mtp){				
					if(isset($ho[$mtp])){
						if(!isset($ho['post'])){
							$ho['post']=[];
						}
						$ho['post'][$mtp] = $ho[$mtp];
						unset($ho[$mtp]);
					}
				}
				$myset['toolPliangExpands'][$k] = $ho;
			}
		}
		
		if(isset($myset['operExpands'])){
			foreach($myset['operExpands'] as $k => $tx){
				$ho = $tx;
				//没有设置auditOper，默认用name
				if(!isset($ho["auditOper"])){
					$ho["auditOper"] = $ho["name"];
				}
				//把movetopost各项移到post里去
				$movetopost=['goto','auditOper','dirName'];
				foreach($movetopost as $mtp){				
					if(isset($ho[$mtp])){
						if(!isset($ho['post'])){
							$ho['post']=[];
						}
						$ho['post'][$mtp] = $ho[$mtp];
						unset($ho[$mtp]);
					}
				}
				$myset['operExpands'][$k] = $ho;
			}
		}
		
		if(isset($myset['rowOper'])){
			unset($myset['rowOper']);
		}
		
		$myset['colKey'] = $this->colKey;
		$myset['colName'] = $this->colName;
		$myset['colNafy'] = $this->colNafy; 
		
		return $this->out(0,$myset);
	}
	
}
?>
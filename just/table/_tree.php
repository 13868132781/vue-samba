<?php
namespace just\table;

trait _tree{
	public final function api_treeData(){
		$post=$this->POST;
		//gridData_original是api_gridData调用获取数据的函数
		$data = $this->gridData_original();
		
		$treeInfo=[
			'col'=> $this->colName,
			'depth'=> -1,//默认打开到第几级，-1所有都打开 
		];
		
		$data = $this->treeMake($data,$treeInfo);
		
		$dingji = [];
		foreach($data as $row){
			$row['_tree_']['col'] = 'name';
			$myrow=[
				'id'=>$row[$this->colKey],
				'name'=>$row[$this->colName],
				'_tree_'=>$row['_tree_'],
				
			];
			$dingji[] = $myrow;
		}
		
		return $this->out(0,$dingji);
	}
	
	//这是给_grid.php里的gridDeal函数用的
	public function treeMake($data,$treeInfo){
		$post=$this->POST;
		
		$firstFid = '0';//顶级id。本框架中，虚拟的顶级机构的id是 0
		
		if(isset($post['treeSearch']) and $post['treeSearch']!=''){
			$newData=[];
			foreach($data as $dak=>$dav){
				$treeVal = $dav[$this->colName];
				if(stristr($treeVal,$post['treeSearch'])){
					$davv = $dav;
					$davv[$this->colFid]=$firstFid;//把改行提到根目录下
					$davv['_tree_']=['isSearch'=>true];
					//isSearch在treeCycle里改为父节点name，以便标识不同节点下同名节点
					$newData[] = $davv;
				}
				if($dav[$this->colFid].''!==$firstFid.''){//非根目录下保留下来
					$newData[] = $dav;
				}
			}	
			$data=$newData;
			$treeInfo['depth'] = 0;
		}
		
		$data = $this->treeCycle($data,0,$firstFid,'顶级',$treeInfo,$this->treeBeginId);
		
		return $data;
	}
	
	//层级从0开始计数
	public function treeCycle($data,$depth,$fid,$fullFname,$treeInfo,$beginid=''){
		if($beginid){//设定了起始id，就先找到该起始id的fid
			foreach($data as $row){
				if($row[$this->colKey].''===$beginid.''){
					$fid = $row[$this->colFid];
					break;
				}
			}
		}
		
		$dftdepth = -1;
		if(isset($treeInfo['depth'])){
			$dftdepth = $treeInfo['depth'];
		}
		
		$show = true;
		$open = true;
		if($dftdepth!=-1){
			if($depth == $dftdepth){
				$open=false;
			}else if($depth > $dftdepth){
				$show = false;
				$open = false;
			}
		}
		
		$backtree = [];
		$fidcol = $this->colFid;
		$keycol = $this->colKey;
		$namecol = $this->colName;
		if(isset($treeInfo['col'])){
			$namecol = $treeInfo['col'];
		}
		$lastIndex = -1;
		foreach($data as $row){
			//如果$fid是数字0,和字符串对比，php会尝试将字符串转为数字，也称了0
			//所以 'aaaa'==0 对比，会是true
			if($row[$fidcol].''!==$fid.''){
				continue;
			}
			if($beginid and $beginid!=$row[$keycol]){
				continue;
			}
			$fullname =  $fullFname."->".$row[$namecol];
			$treeSet=[
				'col'=>$namecol, //在哪个字段生成树
				'depth'=>$depth, //当前行所在深度
				'fullname' => $fullname, //当前行全名
				'islast'=>false, //是否是父节点里最后一个
				'isleaf'=>true, //是否是叶子节点
				'show'=> $show,//自己是否显示
				'open'=>$open,//子项是否打开
				'fislast'=>[], //之前深度节点是否是上级节点的最后节点
			];
			if(isset($row['_tree_']['isSearch']) and $row['_tree_']['isSearch']){
				//treeSearch之后，用于标识不同节点下的同名节点
				//$treeSet['isSearch'] = $fullFname;
			}
			
			$backtreeson = $this->treeCycle($data, $depth+1, $row[$keycol], $fullname,$treeInfo);
			if(count($backtreeson)>0){
				$treeSet['isleaf']=false;
			}
			$row['_tree_'] = $treeSet;
			$backtree[] = $row;
			$lastIndex = count($backtree)-1;
			
			foreach($backtreeson as $son){
				$backtree[] = $son;
			}
			
		}
		if($lastIndex>-1){
			$backtree[$lastIndex]['_tree_']['islast']=true;
			//$lastIndex是last，那所有>$lastIndex都是他的子孙
			for($i = $lastIndex+1;$i<count($backtree);$i++){
				$backtree[$i]['_tree_']['fislast'][$depth] = true;
			}
		}
		
		return $backtree;
	}
	
	
	
	
}

?>
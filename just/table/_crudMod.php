<?php
/*
//必须函数，必须定义
public function crudModSet(){}

//串联函数，可返回out格式，以结束请求
public function crudModBefore(){}

//串联函数，可返回out格式，以结束请求
public function crudModAfter(){}
*/
namespace just\table;
use just\table\valid;

trait _crudMod{
	public $sdPassHolder = "1a,2B.4c;4d!5E@6fz7#G8hijklMn9";
	
	public function crudModSet(){
		return [];//返回数组
	}
	
	public function crudModBefore(){
		return ;//可返回out格式，以结束请求
	}
	
	public function crudModAfter(){
		return ;
	}
	
	public final function api_crudModSet(){
		$post = &$this->POST;
		
		$formSet = $this->crudModSet();
			
		$row = $this->getById($post['key']);
		if($row){
			$keyval=[];
			foreach($formSet as $k => $seto){
				if($seto['type']=='hidden'){
					unset($formSet[$k]);
					continue;
				}
				
				$formSet[$k]['key'] = $row[$this->colKey];
				
				if(isset($row[$seto['col']])){
					$val = $row[$seto['col']];
					$formSet[$k]['value'] = $val;
					$keyval[$seto['col']] = $val;
				}
				
				//修改时，不把密码传到前端，用sdPassHolde代替
				if($seto['type']=='password'){
					if(isset($formSet[$k]['value'])and $formSet[$k]['value']!=''){
						$formSet[$k]['value'] = $this->sdPassHolder ;
						$keyval[$seto['col']] = $this->sdPassHolder ;
					}
				}
				
				if(isset($seto['valid']['type']) and $seto['valid']['type']=='same'){
					$realcol = $seto['valid']['as'];
					if(isset($keyval[$realcol])){
						$formSet[$k]['value']=$keyval[$realcol];
					}
				}
				
				if($seto['type']=='treePick'){
					$id=$row[$seto['col']];
					$className=str_replace("/","\\",('app'.$seto['router']));
					$xsname=(new $className())->getTreeName($id);
					$formSet[$k]['xsname'] = $xsname;
				}
				//根据查询出来的$row，修改该项设置
				//目前好像不太用到
				//可以在set函数里，自己查询出$row，来做修改
				if(isset($seto['modify'])){
					$seto['modify']($formSet[$k],$row);
					unset($formSet[$k]['modify']);
				}
			}
		}
		$formSet = array_values($formSet);//重新建立索引
		return $this->out(0,$formSet);
	}
	
	
	public final function api_crudModSave(){
		$post = &$this->POST;
		$key = $post['key'];
		
		$formSet = $this->crudModSet();
		
		//如果密码框还是占位串，说明没有输入新密码
		foreach($formSet as $k => $seto){
			if($seto['type']=='password'){
				$col = $seto['col'];
				$val = $post['formVal'][$col];
				if($val==$this->sdPassHolder){
					$post['formVal'][$col]='';
					$formSet[$k]['ask']=false;
				}
			}
		}
		
		//检查的过程中，$post['formVal']数据会被规整补全
		$res = valid::check($formSet,$post['formVal']);
		if($res){
			return $this->out(1,$res);
		}
		
		$res = $this->crudModBefore();
		if($res){//如果有返回，则会结束请求
			return $res;
		}
		
		$res = $this->crudModUnique($formSet,$post['formVal']);
		if($res){//如果有返回，则会结束请求
			return $this->out(1,$res);
		}
		
		
		$updateVal=[];
		foreach($formSet as $k => $seto){
			
			if(isset($seto['ignore']) and $seto['ignore']){
				continue;
			}
			if($seto['type']=='hidden'){
				continue;
			}
			
			$col = $seto['col'];
			$val = $post['formVal'][$col];
			
			if($val=='' and isset($seto['sqlValue'])){
				$val = $seto['sqlValue'];
			}
			
			if($seto['type']=='password'){
				if($val==''){//密码为空，表示不修改
					continue;
				}
				if($val!='' and isset($seto['crypt'])){
					if($seto['crypt']=='md5'){
						$val = md5($val);
					}
				}
			}
			
			$updateVal[$col] = $val;
		}
		
		$this->DB()->where($this->colKey,$key)->update($updateVal);
		
		$this->crudModAfter();
		
		
		return $this->out(0,'','修改成功');
	}
	
	
	public function crudModUnique($sets,$formVal){
		$post=&$this->POST;
		$key = $post['key'];
		
		$back=[];
		foreach($sets as $seto){
			
			if(!isset($seto['unique']) or !$seto['unique']){
				continue;
			}
			
			$col = $seto['col'];
			$row = $this->DB()
			->where($this->colKey,'!=',$key)
			->where($col , $formVal[$col])
			->first();
			if($row){
				$back[$col]= $seto['name']."已经存在";
			}
			
		}
		
		if(count($back)>0){
			return $back;
		}
		return ;
	}
	
	
}

?>
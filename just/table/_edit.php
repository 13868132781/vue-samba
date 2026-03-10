<?php
/*
//必须函数，必须定义
public function editSet_[goto](){}

//并联函数，可选，必须返回out格式，以结束请求
public function editSave_[goto](){}

//串联函数，可选，可返回out格式，以结束请求
public function editSaveBefore_[goto](){}

//串联函数，可选，可返回out格式，以结束请求
public function editSaveAfter_[goto](){}
*/
/*
认清crudMod，edit的区别
默认用crudMod修改数据库，
但要想再弄个按钮，修改数据库里某几个字段，或者完全不修改数据库
可创建个edit按钮，
*/
namespace just\table;
use just\table\valid;

trait _edit{
	
	public final function api_editSet(){
		$post = $this->POST;
		
		$funcName= 'editSet_'.$post['goto'];
		$formSet = $this->$funcName();
			
		$row = $this->getById($post['key']);
		if($row){
			foreach($formSet as $k => $seto){
				$formSet[$k]['key'] = $row[$this->colKey];
				if($seto['type']=='password'){
					if(!isset($seto['ask']) or !$seto['ask']){
						$formSet[$k]['holder']='不修改时可留空';
					}
					continue;
				}
				if(isset($seto['ignore']) and $seto['ignore']){
					continue;
				}
				if(isset($row[$seto['col']])){
					$formSet[$k]['value'] = $row[$seto['col']];
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
		
		return $this->out(0,$formSet);
	}
	
	
	public final function api_editSave($mult=false){
		$post = $this->POST;
		$key = $post['key'];
		
		/*
		//定义了editSave_goto函数，就只执行该函数
		//这个废弃，都用before after
		$funcName= 'editSave_'.$post['goto'];
		if(method_exists($this,$funcName)){
			$res = $this->$funcName();
			if($res){
				return $res;
			}else{
				return $this->out(1,'','函数'.$funcName.'缺少返回');
			}
		}
		*/
		
		$funcName= 'editSet_'.$post['goto'];
		if($mult){//api_multEditSave也是逐项调用这边的函数执行
			$funcName= 'multEditSet_'.$post['goto'];
		}
		$formSet = $this->$funcName();
		
		$res = valid::check($formSet,$post['formVal']);
		if($res){
			return $this->out(1,$res);
		}
		
		$funcName= 'editSaveBefore_'.$post['goto'];
		if(method_exists($this,$funcName)){
			$res = $this->$funcName();
			if($res){
				return $res;
			}
		}
		
		$res = $this->editUnique($formSet,$post['formVal']);
		if($res){//如果有返回，则会结束请求
			return $this->out(1,$res);
		}
		
		
		$editVal=[];
		foreach($formSet as $k => $seto){
			if($seto['type']=='show'){
				continue;
			}
			
			if(isset($seto['ignore']) and $seto['ignore']){
				continue;
			}
			$col = $seto['col'];
			$val = '';
			if(isset($post['formVal']) and isset($post['formVal'][$col]) ){
				$val = $post['formVal'][$col];
			}
			if($seto['type']=='password'){
				if($val==''){
					continue;
				}
				if(isset($seto['crypt'])){
					if($seto['crypt']=='md5'){
						$val = md5($val);
					}
				}
			}
			
			$editVal[$col] = $val;
		}
		
		$this->DB()->where($this->colKey,$key)->update($editVal);
		
		$funcName= 'editSaveAfter_'.$post['goto'];
		if(method_exists($this,$funcName)){
			$res = $this->$funcName();
			if($res){
				return $res;
			}
		}
		
		return $this->out(0,'','操作成功');
	}
	
	
	public function editUnique($sets,$formVal){
		$post = $this->POST;
		$key = $post['key'];
		
		$back=[];
		foreach($sets as $seto){
			if($seto['type']=='show'){
				continue;
			}
			
			if(isset($seto['ignore']) and $seto['ignore']){
				continue;
			}
			
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
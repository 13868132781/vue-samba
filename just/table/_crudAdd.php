<?php
/*
//必须函数，必须定义
public function crudAddSet(){}

//串联函数，可返回out格式，以结束请求
public function crudAddBefore(){}

//串联函数，可返回out格式，以结束请求
public function crudAddAfter(){}
*/
namespace just\table;
use just\table\valid;

trait _crudAdd{
	
	
	public function crudAddSet(){
		return [];//返回数组
	}
	
	public function crudAddBefore(){
		return;//可返回out格式，以结束请求
	}
	
	public function crudAddAfter(){
		return;
	}
	
	public final function api_crudAddSet(){
		$post = &$this->POST;
		$formSet = $this->crudAddSet();
		//holder是占位，不允许用户自己定义的
		//hidden是不传到浏览器
		//在添加时，formVal里设置了对应值，用formVal，没有就根据value填
		//在修改时，formVal里设置了对应值，用formVal，没有就忽略
		foreach($formSet as $k => $seto){
			if($seto['type']=='hidden'){
				unset($formSet[$k]);//删除，但不修改索引
				continue;
			}
			if(isset($seto['valueIndex']) and isset($seto['options']) ){
				$valoptionsI=0;
				foreach($seto['options'] as $valoptionsK => $valoptionsV){
					if($valoptionsI == intval($seto['valueIndex'])){
						$formSet[$k]['value'] = $valoptionsK;
						break;
					}
					$valoptionsI++;
				}
			}
		}
		$formSet = array_values($formSet);//重新建立索引
		return $this->out(0,$formSet);
	}
	
	public final function api_crudAddSave(){
		$post = &$this->POST;
		//validation
		$formSet = $this->crudAddSet();
		//检查的过程中，$post['formVal']数据会被规整补全
		$res = valid::check($formSet,$post['formVal']);
		if($res){
			return $this->out(1,$res);
		}
		
		$res = $this->crudAddBefore();
		if($res){//如果有返回，则会结束请求
			return $res;
		}
		
		$res = $this->crudAddUnique($formSet,$post['formVal']);
		if($res){//如果有返回，则会结束请求
			return $this->out(1,$res);
		}
		
		
		$insertVal=[];
		foreach($formSet as $k => $seto){
			if(isset($seto['ignore']) and $seto['ignore']){
				continue;
			}
			
			$col = $seto['col'];
			$val = $post['formVal'][$col];
			
			if($seto['type']=='hidden'){//hidden不接受前台传来的值
				if(isset($seto['value'])){
					$val = $seto['value'];
				}else{
					$val='';
				}	
			}
			
			if($val=='' and isset($seto['sqlValue'])){
				$val = $seto['sqlValue'];
			}
			
			if($seto['type']=='password'){
				if(isset($seto['crypt']) and $val!=''){
					if($seto['crypt']=='md5'){
						$val = md5($val);
					}
				}
			}
			
			$insertVal[$col] = $val;
		}
		
		if(count($this->colKeyList)>0){
			foreach($this->colKeyList as $i => $keyo){
				$insertVal[$keyo] = $post['keyList'][$i];
			}
		}
		
		
		$insetid = $this->DB()->insert($insertVal);
		if($this->colOrder){
			$this->DB()->where($this->colKey,$insetid)
			->update([
				$this->colOrder=>$insetid
			]);
		}
		
		$post['key'] = $insetid;
		
		$res = $this->crudAddAfter();
		if($res){//如果有返回，则会结束请求
			return $res;
		}
		
		return $this->out(0,'','新增成功');
	}
	
	
	
	public function crudAddUnique($sets,$formVal){
		$post = &$this->POST;
		
		$back=[];
		foreach($sets as $seto){
			
			if(!isset($seto['unique']) or !$seto['unique']){
				continue;
			}
			
			$col = $seto['col'];
			$row = $this->DB()
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
	
	//批量导入，php.ini里的max_input_vars设置得大写，才能接受大的POST数据
	public final function api_crudAddImport(){
		$post = &$this->POST;
		//validation
		$formSet = $this->crudAddSet();
		$back=[];
		$excelVals = $post['excelVals'];
		foreach($excelVals as $excelVal){
			$formVal=[];
			$t=0;
			foreach($formSet as $k => $seto){
				if(isset($seto['import']) and $seto['import']){
					$realval =  $excelVal[$t];
					if(isset($seto['options'])){
						//options类型添加的是选项名，要替换成选项编号
						//如果options选项名有重复的话，就只能选择找到的第一个项的编号了
						foreach($seto['options'] as $k=>$v){
							if($v = $realval){
								$realval = $k;
							}
						}
					}
					$formVal[$seto['col']] = $realval;
					$t++;
				}else if(isset($seto['valid']['same'])){//密码确认字段自动填上密码
					$formVal[$seto['col']] = $formVal[$seto['valid']['as']];
				}else if(isset($seto['importValue'])){//不在csv的字段自动填上importValue
					$formVal[$seto['col']] = $seto['importValue'];
				}else if(isset($seto['value'])){//没有importValue就自动填上value
					$formVal[$seto['col']] = $seto['value'];
				}
			}
			
			
			
			$this->POST['formVal'] = $formVal;
			
			$res = $this->api_crudAddSave();
			
			$back[]=$res;
			
		}
		
		return $this->out(0,$back);
	}
	
	
	
	
}



?>
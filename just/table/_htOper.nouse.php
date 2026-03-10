<?php
namespace just\table;
use just\table\valid;

trait _htOper{
	//增删改查
	public final function api_htOperAdd(){
		$post = $_POST;//这是取php标准的post信息，必须是&连接的参数串
		
		$formSet = $this->crudAddSet();
		$formVal=[];
		foreach($formSet as $k => $seto){
			$col = $seto['col'];
			$colx = $col;
			if(strstr($colx,'_')){
				$colx=explode('_',$colx,2)[1];
			}
			
			//把密码复制到密码确认字段
			if(isset($seto['valid']) and isset($seto['valid']['as'])){
				$colas = $seto['valid']['as'];
				if(strstr($colas,'_')){
					$colasx = explode('_',$colas,2)[1];
				}
				if(isset($post[$colasx])){
					$post[$colx] = $post[$colasx];
				}
			}
			
			//$formVal[$col] = '';
			if(isset($post[$colx])){
				$formVal[$col] = $post[$colx];
			}else if(isset($seto['importValue'])){
				$formVal[$col] = $seto['importValue'];
			}else if(isset($seto['value'])){
				$formVal[$col] = $seto['value'];
			
			}
		}
		//print_r($formVal);
		$this->POST['formVal'] = $formVal;
			
		$res = $this->api_crudAddSave();
			
		return $res;
		
	}
	
	public final function api_htOperMod(){
		$post = $_POST;//这是取php标准的post信息，必须是&连接的参数串
		$colname = 'colNafy';
		if($this->colNafy){
			$colname = 'colNafy';
		}else if($this->colName){
			$colname = 'colName';
		}else{
			return $this->out(1,'','未找找_key_所对应的字段');
		}
		
		$bscol = $this->$colname;
		$bsval = $post["_key_"];
		
		$row = $this->DB()->where($bscol,$bsval)->first();
		if(!$row){
			return $this->out(1,'','未找到老数据');
		}
		
		$key = $row[$this->colKey];
		
		
		$formSet = $this->crudAddSet();
		$formVal=[];
		foreach($formSet as $k => $seto){
			$col = $seto['col'];
			$colx = $col;
			if(strstr($colx,'_')){
				$colx=explode('_',$colx,2)[1];
			}
			
			//把密码复制到密码确认字段
			if(isset($seto['valid']) and isset($seto['valid']['as'])){
				$colas = $seto['valid']['as'];
				if(strstr($colas,'_')){
					$colasx = explode('_',$colas,2)[1];
				}
				if(isset($post[$colasx])){
					$post[$colx] = $post[$colasx];
				}
			}
			
			if(isset($post[$colx])){
				$formVal[$col] = $post[$colx];
			}else if(isset($row[$col])){
				$formVal[$col] = $row[$col];
			}
		}
		//print_r($formVal);
		$this->POST['key'] = $key;
		$this->POST['formVal'] = $formVal;
			
		$res = $this->api_crudModSave();
		
		return $res;
		
		
	}
	
	public final function api_htOperDel(){
		$post = $_POST;//这是取php标准的post信息，必须是&连接的参数串
		$colname = 'colNafy';
		if($this->colNafy){
			$colname = 'colNafy';
		}else if($this->colName){
			$colname = 'colName';
		}else{
			return $this->out(1,'','未找找_key_所对应的字段');
		}
		
		$bscol = $this->$colname;
		$bsval = $post["_key_"];
		
		$row = $this->DB()->where($bscol,$bsval)->first();
		if(!$row){
			return $this->out(1,'','未找到老数据');
		}
		
		$key = $row[$this->colKey];
		
		
		$this->POST['key'] = $key;
		$res = $this->api_crudDel();
		
		return $res;
	}
	
	public final function api_htOperGet(){
		$post = $_POST;//这是取php标准的post信息，必须是&连接的参数串
		$colname = 'colNafy';
		if($this->colNafy){
			$colname = 'colNafy';
		}else if($this->colName){
			$colname = 'colName';
		}else{
			return $this->out(1,'','未找找_key_所对应的字段');
		}
		
		$bscol = $this->$colname;
		$bsval = $post["_key_"];
		
		$row = $this->DB()->where($bscol,$bsval)->first();
		if(!$row){
			return $this->out(1,'','未找到老数据');
		}
		
		$key = $row[$this->colKey];
		
		$back=[];
		$gridSet = $this->gridSet();
		$columns = $gridSet['columns'];
		
		foreach($columns as $heado){
			$col = $heado['col'];
			$colx = $col;
			if(strstr($colx,'_')){
				$colx=explode('_',$colx,2)[1];
			}
			
			if(isset($row[$col])){
				$back[$colx]=$row[$col];
			}
			
		}
		
		return $this->out(0,$back);
	}
	
	
	
}


?>
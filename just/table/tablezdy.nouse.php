<?php
use just\table as T;

class tablezdy{
	use T\_tree;
	
	public $pageName='';//页名，审计用
	public $TN = "";
	public $colKey = "";
	public $colOrder = "";
	public $colFid = "";
	public $colUnit = "";//用于右侧菜单的查询
	public $colName = "";
	public $colNafy = "";
	public $orderDesc = false;
	public $treeBeginId='';
	public $POST = [];
	public $FUNC = '';

	public $executeReg=[];//注册的执行，审计用
	
	function __construct($POST=[],$func=''){
		$this->POST = $POST;
		$this->FUNC = $func;
	}
	
	//给一些别的组件用的，比如_tree组件就通过getData来获得数据
	//这是原始数据，griddata是修改过的数据
	public function getData($exclude=''){
		$data = $this->gridData();
		return $data;
	}
	
	
	
	public final function api_gridSet(){
		$myset = $this->gridSet();
		$myset['colKey'] = $this->colKey;
		$myset['colName'] = $this->colName;
		$myset['colNafy'] = $this->colNafy;
		return $this->out(0,$myset);
	}
	
	public final function api_gridData(){
		$data = $this->gridData();
		
		return $this->out(0,$data);
	}
	
	public final function api_gridTotal(){
		if(method_exists($this,'gridTotal')){
			$num = $this->gridTotal();
		}else{
			$data = $this->gridData();
			$num = count($data);
		}
		return $this->out(0,$num);
	}
	
	public final function api_crudAddSet(){
		$set = $this->crudAddSet();
		
		return $this->out(0,$set);
	}

	public final function api_crudAddSave(){
		return $this->crudAddSave();
	}
	
	public final function api_crudModSet(){
		$set = $this->crudModSet();
		
		return $this->out(0,$set);
	}

	public final function api_crudModSave(){
		return $this->crudModSave();
	}
	
	public final function api_execute(){
		$post=$this->POST;
		$goto = $post['goto'];
		
		$funcName = 'execute_'.$goto;
		$res = $this->$funcName();
		return $res;
	}
	
	public function out($code,$data=null,$msg=null,$refresh=false){
		//$msg会自动被显示在页面右上角
		return ['code'=>$code,'data'=>$data,'msg'=>$msg,'refresh'=>$refresh];
	}
	
}


?>
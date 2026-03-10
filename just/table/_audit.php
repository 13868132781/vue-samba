<?php
namespace just\table;
use appsys\auth\sysOper;

trait _audit{
	/*
	noAudit：不做审计
	execAudit：做审计，审计动作名就是前面的信息
	gotoAudit：做审计，审计动作名得自定义
	*/
	public $allowFuncList = [
		'gridData'			=>['获取表数据', 'noAudit'],
		'gridSet'			=>['获取表设定', 'noAudit'],
		'gridTotal'			=>['获取表总行数', 'noAudit'],
		'treeData'			=>['获取树形表数据', 'noAudit'],
		'export'			=>['导出', 'noAudit'],
		'exportDownload'	=>['导出下载', 'noAudit'],
		'crudAddSet'		=>['修改设定', 'noAudit'],
		'crudAddSave' 		=>['添加', 'execAudit'],
		'crudAddImport'		=>['导入', 'execAudit'],
		'crudModSet' 		=>['修改设定', 'noAudit'],
		'crudModSave'		=>['修改', 'execAudit'],
		'crudDel' 			=>['删除','execAudit'],
		'crudDelete'		=>['批量删除', 'execAudit'],
		'editSet' 			=>['编辑设定', 'noAudit'],
		'editSave' 			=>['编辑', 'gotoAudit'],
		'multEditSet' 		=>['批量编辑设定', 'noAudit'],
		'multEditSave' 		=>['批量编辑', 'gotoAudit'],
		'execute' 			=>['执行', 'gotoAudit'],
		'fetch' 			=>['获取', 'noAudit'],
		'state' 			=>['状态', 'gotoAudit'],
		'uploadAdd' 			=>['上传文件', 'gotoAudit'],
		'uploadDel' 			=>['上传删除', 'gotoAudit'],
		'download' 			=>['下载', 'noAudit'],
		'onoff' 			=>['启停', 'gotoAudit'],
		'radio' 			=>['单选', 'gotoAudit'],
		'order' 			=>['排序', 'execAudit'],
		'filterSet' 		=>['筛选设定', 'noAudit'],
	];
	/*
	检查本类的定义是否合法
	检查请求的函数是否合法
	*/
	public final function auditCheck(){
		$post = $this->POST;
		$func = $this->FUNC;
		$class = get_class($this);
		
		//允许的接口函数
		if(!isset($this->allowFuncList[$func])){
			return $this->out(1,'','非法函数：'.$func);
		}
		//必须定义pageName，否则审计不知道记录哪个页面
		if(!property_exists($this,'pageName') or !$this->pageName){
			return $this->out(1,'','未定义pageName成员变量：'.$class);
		}
		
		$funcinfo = $this->allowFuncList[$func];
		if($funcinfo[1]=='gotoAudit' and !isset($post['auditOper'])){
			return $this->out(1,'','未定义auditOper for '.$func.' :'.$class);
		}
		
	} 
	
	public final function auditExec($back){
		//pageName  oper  name nafy code msg
		$post = $this->POST;
		$func = $this->FUNC;
		
		$code = $back['code'];
		$msg = $back['msg']?:$back['data'];
		if(is_array($msg)){
			$msg=json_encode($msg);
		}
		$page = $this->pageName;
		$oper='';
		$name = '';
		$nafy = '';
		
		$funcinfo = $this->allowFuncList[$func];
		
		if($this->noAudit){
			return;
		}
		//获取oper
		if($funcinfo[1]=='noAudit'){
			return;
		}else if($funcinfo[1]=='execAudit'){
			$oper= $funcinfo[0];
		}else if(isset($post['auditOper'])){
			$oper = $post['auditOper'];
		}else{
			$oper = $func;
			if(isset($post['goto'])){
				$oper = $func."_".$post['goto'];
			}
		}
		
		//获取name和nafy
		if($this->currentRow){//只要post里有key，就会生成currentRow
			$row = $this->currentRow;
			$name = $row[$this->colName]; 
			if($this->colNafy){
				$nafy = $row[$this->colNafy];
			}
		}else{//对于add情况，就到['formVal']里去取
			if(isset($post['formVal'][$this->colName])){
				$name = $post['formVal'][$this->colName];
			}
			if($this->colNafy and isset($post['formVal'][$this->colNafy])){
				$nafy = $post['formVal'][$this->colNafy];
			}
		}
		
		sysOper::auditWrite($page,$oper,$name,$nafy,$code,$msg);
		
	} 
	
	
}


?>
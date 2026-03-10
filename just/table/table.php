<?php
use just\table as T;

class table {
	use T\_audit,T\_export,T\_filter,
		T\_crudAdd,T\_crudMod,T\_crudDel,
		T\_edit,T\_multEdit,
		T\_execute,T\_fetch,T\_state,T\_upload,T\_download,
		T\_onoff,T\_radio,
		T\_order,T\_grid,T\_tree,
		T\_zdy;
	
	public $pageName='';//页名，审计用
	public $TN = ""; //表名
	public $colKey = ""; //主键字段名
	public $colKeyList = [];
	public $colOrder = ""; //排序字段名
	public $colFid = ""; //父级id字段名
	public $colUnit = "";//用于右侧菜单查询的机构字段名
	public $colName = "";//名称字段名
	public $colNafy = "";//次名称字段名
	public $colCrypt = [];//加密字段列表
	public $orderDesc = false; //是否反向排序
	public $POST = [];
	public $POSTY = [];
	public $FUNC = ''; //要执行的api_
	public $treeBeginId=''; //树结构的起始id
	public $noAudit=false;//是否做审计
	public $zdyBackend=false; //后端不是数据库，而是自定义数据
	public $currentRow=null;
	
	function __construct($post=[],$func=''){
		global $sysCfgInfo;
		$this->TN = str_ireplace('{sysDB}',$sysCfgInfo['sysDB'],$this->TN); 
		
		$this->POST = $post;  //在使用过程中，可能被改变
		$this->POSTY = $post; //原始的post
		$this->FUNC = $func;
		
		//如果参数里有key的话，就把对应数据取出来放到currentRow
		//因为多数情况下都要用到，干脆放到类成员变量里
		$post = $this->POST;
		if( isset($post['key']) and $post['key']!='' ){
			$this->currentRow = $this->getById($post['key']);
		}
		
		$this->__myconstruct();
		
	}
	public function __myconstruct(){//子类可重写，以实现自己的需求
	}
	
	
	/*
	//获取全部数据,目前没地方用到
	public function getData(){
		if($this->zdyBackend){
			return $this->zdySource();
		}
		$db = $this->DB();
		
		if($this->colOrder!=''){
			$db->orderBy($this->colOrder);
		}
		
		$db->orderBy($this->colKey,$this->orderDesc);
		
		$data = $db->get();
		
		return $data;
	}
	*/
	
	public static function options($prex=[],$wheres=[],$colkey='',$colname=''){
		$inst = new static();
		
		$colkey = $colkey?$colkey:$inst->colKey;
		$colname = $colname?$colname:$inst->colName;
		//自定义数据源
		if($inst->zdyBackend){
			$data = $inst->zdySource(['where'=>$wheres]);
			$back=$prex;
			foreach($data as $row){
				$back[$row[$colkey].''] = $row[$colname];
			}
			return $back;
		}
		
		$db = $inst->DB();
		
		foreach($wheres as $k=>$v){
			$db->where($k,$v);
		}
		
		if($inst->colOrder!=''){
			$db->orderBy($inst->colOrder);
		}
		
		$db->orderBy($inst->colKey,$inst->orderDesc);
		
		$data = $db->get();
		
		$back=$prex;
		foreach($data as $row){
			$back[$row[$colkey].''] = $row[$colname];
		}
		return $back;
	}
	
	
	public function getById($id,$col=null){
		
		//自定义数据源
		if($this->zdyBackend){
			$inopt=[];
			$inopt['byid']=$id;
			$data = $this->zdySource($inopt);
			if(!$data or count($data)==0){
				return null;
			}
			if($col){
				return $data[0][$col];
			}
			return $data[0];
		}
		
		$db=$this->DB();
		$row = $db->where($this->colKey,$id)->first();
		if($row and $col){
			return $row[$col];
		}
		
		return $row;
	}
	
	public function getByName($name,$col=null){
		$db=$this->DB();
		$row = $db->where($this->colName,$name)->first();
		if($row and $col){
			return $row[$col];
		}
		return $row;
	}
	public function getByNafy($nafy,$col=null){
		$db=$this->DB();
		$row = $db->where($this->colNafy,$nafy)->first();
		if($row and $col){
			return $row[$col];
		}
		return $row;
	}
	
	public function getNameByNafy($nafy){
		$db=$this->DB();
		$row = $db->where($this->colNafy,$nafy)->first();
		if($row ){
			return $row[$this->colName];
		}
		return "";
	}
	public function getNameById($id){
		$db=$this->DB();
		$row = $db->where($this->colKey,$id)->first();
		if($row ){
			return $row[$this->colName];
		}
		return "";
	}
	public function getNafyById($id){
		$db=$this->DB();
		$row = $db->where($this->colKey,$id)->first();
		if($row ){
			return $row[$this->colNafy];
		}
		return "";
	}
	

	public function getTreeName($id){
		if(strlen($id)==0){//空id时，返回空
			return '';
		}
		
		$db=$this->DB();
		$name='';
		while(1){
			$row = $db->clear()->where($this->colKey,$id)->first();
			if(!$row){
				break;
			}
			if($name!=''){
				$name='→'.$name;
			}
			$name = $row[$this->colName].$name;
			$id = $row[$this->colFid];
			if($id=='0'){
				break;
			}
		}
		
		return $name;
	}
	
	
	public static function DB(){
		$th = null;
		if(isset($this)){
			$th = $this;
		}else{
			$th=new static();
		}
		$ndb = \DB::table($th->TN)->cryptField(...$th->colCrypt);
		
		return $ndb;
	}
	
	public function out($code,$data=null,$msg=null,$refresh=false,$js=''){
		//$msg会自动被显示在页面右上角
		return ['code'=>$code,'data'=>$data,'msg'=>$msg,'refresh'=>$refresh,'js'=>$js];
	}
	
}

/*
函数调用大致流程：

api_gridData-->gridData_modify-->!gridBefore(!gridAfter)

api_gridTotal

api_gridSet-->gridInfo


api_formSet        -->!formAddInfo
  [formWay]        -->!formModInfo
  [goto]           -->!other_formAddInfo
  [formWay]/[goto] -->!other_formModInfo

api_formSubmit       -->formAddSubmit-->!formAddBefore(!formAddAfter)
  [goto]                             -->!other_forAddBefore(!other_formAddAfter)
  [formWay]          -->formModSubmit-->!forModBefore(!formModAfter)
  [formWay]/[goto]                   -->!other_forModBefore(!other_formModAfter)

api_execute  --->  execute
             --->  other_execute
  
注:POST里有formWay==mod时，走mod流程，有goto='other'时，走other流程
	!开头的，是要继承类自己定义的
  
  
*/



?>
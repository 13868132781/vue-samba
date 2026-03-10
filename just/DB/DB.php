<?php
use just\DB as D;

class DB{
	use D\_base,D\_deal,D\_sdJoin,D\_orderBy,D\_limit,D\_where,D\_builder;
	use D\_field,D\_crypt;
	
	protected static $sdmysql=null;
	protected static $lastSql="";
	
	protected $TN="";
	protected $argOrder=[];
	protected $argLimit='';
	public $wherestr = '';
	public $wherestrOut = '';//jion时，在jion之外查询
	public $fieldStr = '';
	public $fieldArr=[];
	public $cryptArr=[];//加密字段
	//public $fieldOutArr=['*'];
	public $joinArray=[];//主表要jion的表的joinOut列表,
	public $joinOut=['field'=>['*']];//jion副表要给主表的信息，table/on/field 
	
	
	//public static function __callStatic($method, $parameters){
	//	return static::connection()->$method(...$parameters);
   //}
   //public function __call($method, $parameters){
	//	return $this->connection()->$method(...$parameters);
	//}
	public static function getLastSql(){
		return self::$lastSql;
	}
   
   
   public static function table($TN){
	   $inst = new static();
	   $TNS = explode(".",$TN);
	   $TN = '`'.join('`.`',$TNS).'`';
	   $inst->TN = $TN;
	   return $inst;
   }
   
   //这里不是清理表，而是清理类属性数据
   public function clear(){
	   $inst = new static();
	   $inst->TN = $this->TN;
	   return $inst;
   }
   
   //直接执行sql语句，是个静态函数
   public static function doExec($sql){
	   $sql = trim($sql);
	   $obj = self::query($sql);
	   //$sqlOper = substr($sql,0,6);取前6个字符也行
	   $sqlOper = explode(' ',$sql,2)[0];
		$data='';
	   if(gettype($obj)=='object'){
		   $data=[];
		   while($row=$obj->fetch_assoc()){
			   $data[] = $row;
		   }
	   }elseif(stristr($sqlOper,'insert')){
		   $data = self::$sdmysql->insert_id;
	   }else{
		   $data = self::$sdmysql->affected_rows;
	   }
		return $data;
   }
   
   public function get(){
	   $builderStr = $this->builder();
	   $sql = "select ".$this->fieldStr." ".$builderStr;
	   //echo $sql;
	   $data=$this->queryData($sql);
		return $data;
   }
   
	public function first(){
		$rows = $this->get();
		if(count($rows)>0){
			return $rows[0];
		}
		return null;
   }
   
   public function value($col){
		$rows = $this->get();
		if(count($rows)>0 and isset($rows[0][$col])){
			return $rows[0][$col];
		}
		return null;
   }
   
	public function count(){
		$sql = "select count(*) as num ".$this->builder();
		$data = $this->queryData($sql);
		$num=0;
		if($data){
			$num = $data[0]['num'];
		}
		return $num;
		
	}
	
	
	public function insert($data){
		$cols='';
		$vals='';
		foreach($data as $k=>$da){
			//加密数据
			if(isset($this->cryptArr[$k])){
				$da = $this->_encrypt_php($da);
			}
			
			if($cols!=''){$cols.=',';}
			$cols.=$this->dealCol($k);
			if($vals!=''){$vals.=',';}
			$vals.=$this->dealVal($da);
		}
		$sql="insert into ".$this->TN."(".$cols.") values(".$vals.")";
		
		$obj=self::query($sql);
		
		$insetid = self::$sdmysql->insert_id;
		
		return $insetid;
	}
	
	public function update($data){
		$sql="update ".$this->TN." set ";
		$set = "";
		foreach($data as $k=>$da){
			//加密数据
			if(isset($this->cryptArr[$k])){
				$da = $this->_encrypt_php($da);
			}
			if($set!=''){$set.=",";}
			$set.= $this->dealCol($k)."=".$this->dealVal($da);
		}
		$sql.=$set;
		if(trim($this->wherestr)!=""){
		   $sql.=" where ".trim($this->wherestr);
	   }
	   $obj=self::query($sql);
	   
	   $affectedRows = self::$sdmysql->affected_rows;
	   
	   return $affectedRows;
	}
	
	public function delete(){
		$sql="delete from ".$this->TN." ";
		if(trim($this->wherestr)!=""){
		   $sql.=" where ".trim($this->wherestr);
	   }
	   $obj=self::query($sql);
	   
	   return self::$sdmysql->affected_rows;
	}
	
	
}

/*
DB::table('sdtest.raduser')->where('usid','123')
->join("sdtest.radcheck","rrr","uuuu")
->join("radcheck",function($mydb){
	$mydb->where("xxx","aaa")
	->on('ccc','zzz');
})


where=[];
order=[];
limit=[];
join=[];




*/
?>
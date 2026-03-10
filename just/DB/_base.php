<?php

namespace just\DB;

trait _base{
	
	function __construct(){
		self::connection();
	}
	
	protected static function sqlDebug($msg,$sql=""){
		$backtrace = debug_backtrace();
		echo "sd error：".$msg."</br>\n";
		echo "sql: ".$sql."<br/>\n";
		echo "trace:<br/>\n";
		foreach($backtrace as $line){
			echo "in ".$line['file']." on line ".$line['line']."<br/>\n";
		}
		exit(1);
	}
	
	protected static function connection($force=false){
        if($force or !self::$sdmysql){
			self::$sdmysql = new \mysqli("127.0.0.1","root","jbgsn!2716888");
			if (self::$sdmysql->connect_errno) {	
				self::sqlDebug(self::$sdmysql->connect_error);
			}
			self::$sdmysql->set_charset('utf8');
			mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
		}
	}
	
	public static function close(){
		if(self::$sdmysql){
			self::$sdmysql->close();
		}
	}
	
	//最终，所有操作，都会汇集到这个函数，由这个函数执行
	public static function query($sql){
		self::$lastSql = $sql;
		self::connection();	
		$obj = null;
		try{
			$startTime = microtime(true);
			$obj = self::$sdmysql->query($sql);
			$endTime = microtime(true);
			/* 利用mysql自带的 profiling 进行记录
			\sdLog::sql([
				'sql'=>$sql,
				'take'=>$endTime-$startTime,
			]);
			*/
		} catch (\mysqli_sql_exception $e) {
			self::sqlDebug($e->getMessage(),$sql);
		}
		if(!$obj){
			self::sqlDebug(self::$sdmysql->error,$sql);
		}
		return $obj;
	}
	
	public function queryData($sql){
		$obj = self::query($sql);
		$data=[];
		while($row=$obj->fetch_assoc()){
			//解密字段
			foreach($this->cryptArr as $jmk=>$jmv){
				if(isset($row[$jmk])){
					$row[$jmk] = $this->_decrypt_php($row[$jmk]);
				}
			}
			$data[] = $row;
		}
		return $data;
	}
	
	//这个函数不需要，所以值都经过了escape_string处理的
	//public static function escapeString($str){
	//	return self::$sdmysql->real_escape_string($str);
	//}
	
	
}

?>

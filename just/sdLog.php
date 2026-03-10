<?php
//namespace app\php;
//给ajax请求用的，所以后台脚本里，不需要包含进去

class sdLog{
	protected static $lastTime=0;
	protected static $sayList=[];
	protected static $sqlList=[];
	
	public static function say($key){
		global $sysCfgInfo;
		if(!$sysCfgInfo['mode']){//非开发模式
			return;
		}
		$nowTime = microtime(true);
		global $sdLogGlobalStartTime;
		if(self::$lastTime==0){//第一次记录
			self::$lastTime = $sdLogGlobalStartTime;
			self::$sayList[]=[0,'start'];
		}
		$useTime = number_format($nowTime-self::$lastTime, 8);
		self::$sayList[]=[$useTime,$key];
		self::$lastTime = $nowTime;
	}
	/*
	public static function sql($data){
		global $sysCfgInfo;
		if(!$sysCfgInfo['mode']){
			return;
		}
		self::$sqlList[]=[
			'take'=>number_format($data['take'],8),
			'sql' =>$data['sql'],
		];
	}
	*/
	
	public static function DBLogEnable(){//开启mysql记录
		global $sysCfgInfo;
		if(!$sysCfgInfo['mode']){
			return;
		}
		\DB::doExec("SET profiling = 1");
	}
	
	public static function entry($data){
		global $sysCfgInfo;
		if(!$sysCfgInfo['mode']){
			return;
		}
		
		$sayStr = json_encode(self::$sayList);
		//$sqlStr = json_encode(self::$sqlList);
		
		$PROFILES=\DB::doExec("SHOW PROFILES");
		foreach($PROFILES as $k=>$v){
			$detail = \DB::doExec("SHOW PROFILE CPU,BLOCK IO,CONTEXT SWITCHES,SWAPS,MEMORY  FOR QUERY ".$v['Query_ID']);
			$PROFILES[$k]['detail'] =$detail;
		}
		$sqlStr = json_encode($PROFILES);
		$data['say'] = $sayStr;
		$data['sql'] = $sqlStr;
		$data['time'] = date('Y-m-d H:i:s');
		$dataStr = json_encode($data);
		
		$dataStr = str_replace("\n",".",$dataStr);
		$fp=fopen(__DIR__."/../appsys/entry/logEntryFile.log",'a');
		fwrite($fp,$dataStr."\n");
		fclose($fp);
	}
}
?>
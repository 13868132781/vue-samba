<?php
ini_set("display_errors", "On");

require_once(__DIR__."/../app/config.php");
require_once(__DIR__."/../appsys/config.php");

require_once(__DIR__."/autoload.php");

require_once(__DIR__."/DB/DB.php");
require_once(__DIR__."/table/table.php");
require_once(__DIR__."/aht/sdMsgSend.php");
require_once(__DIR__."/aht/sdRandom.php");
require_once(__DIR__."/aht/sdOtp.php");
require_once(__DIR__."/aht/qrcode/sdQrcode.php");


//$TN：表名，$type：id/time $col：字段名  $count：保留的条数或天数
function clearDBTable($TN,$type,$col,$count){
	if($type=='id'){
		$row = \DB::table($TN)
			->field($col)
			->orderBy($col,'desc')
			->limit($count,1)
			->first();
			
		if($row){
			$minid = $row[$col];
			\DB::table($TN)->where($col,'<',$minid)->delete();
		}
	}else{
		\DB::table($TN)
			->where($col,'<',DB::raw("DATE_SUB(now(),INTERVAL ".$count." DAY)"))
			->delete();
	}
}



?>
<?php




//目前没用到
function sdException($e){
	$backtrace = debug_backtrace();
	echo "sd error：".$e->getMessage()."</br>\n";
	echo "trace:<br/>\n";
	foreach($backtrace as $line){
		echo "in ".$line['file']." on line ".$line['line']."<br/>\n";
	}
	exit(1);
}

function sdError($data){
	$backtrace = debug_backtrace();
	echo "sd error：<br/>\n";
	print_r($data);
	echo "</br>\n";
	echo "trace:<br/>\n";
	foreach($backtrace as $line){
		echo "in ".$line['file']." on line ".$line['line']."<br/>\n";
	}
	exit(1);
}

function sdAlert($data){
	$backtrace = debug_backtrace();
	echo '<fengexiancongzhelikais>';
	echo "来自于文件：".$backtrace[0]['file']."，第".$backtrace[0]['line']."行。";
	echo '<fengexiancongtitle>';
	print_r($data);
	echo '</fengexiancongzhelikais>';
}


function baseTrace(){
	$backtrace = debug_backtrace();
	$lines = '';
	foreach($backtrace as $line){
		$lines.="in ".$line['file']." on line ".$line['line']."<br/>\n";
	}
	return $lines;
}

/* 目前还是用table类下的out函数，要不要换成这个函数，以后再考虑
public function sdResult($code,$data=null,$msg=null,$refresh=false,$js=''){
		//$msg会自动被显示在页面右上角
		return ['code'=>$code,'data'=>$data,'msg'=>$msg,'refresh'=>$refresh,'js'=>$js];
	}
*/


?>
<?php

function checkOne($id){
	$result=checkDIsPsLine($id);
	if(count($result)>0){
		return '已有调度正在运行，请稍后再试！';
	}
}

function checkDIsPsLine($crid){
	global $_SERVER;
	//$this_file_name=basename($_SERVER["PHP_SELF"]);
	$this_file_name="doCron.php";
	$result=array();
	exec("ps -ef | grep '".$this_file_name."' | grep -v 'grep'",$result);
	$backs=[];
	foreach($result as $val){
       $vals= preg_split('/\s+/', $val);
		$back=[];
		$back['myid']=$vals[1];
		$back['faid']=$vals[2];
		$back['start']=$vals[4];
		foreach($vals as $ok=>$one){
			if(stristr($one,$this_file_name)){
				$back['crid'] = $vals[$ok+1];
			}
		}
		$backs[$back['myid']] = $back;
	}
	
	//消除此进程及其父进程
	$thispid=getmypid();
	while(1){
		if(!isset($backs[$thispid])){
			break;
		}
		$faid = $backs[$thispid]['faid'];
		unset($backs[$thispid]);
		$thispid = $faid;
	}
	
	$backEnable=array();
	foreach($backs as $vak=>$val){
		if($val['crid']==$crid){
			$backEnable[] = $val;
		}
	}
	
	return $backEnable;
}
?>
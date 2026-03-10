<?php require_once(__DIR__.'/../../aht/a.php');?>
<?php require_once(__DIR__.'/../../aht/SdThread.php');?>
<?php

SdThread::init();



$filter = '';
$naid = '';

$nasobj = \DB::table('sdaaa.nas');
if(count($argv)>1){
	$naid = $argv[1];
	$nasobj->where("naid",$argv[1]);
}
$nasRows = $nasobj->get();





$maxcount=10;
$nowcount=0;
foreach($nasRows as $src_row){
	if($naid){//单个设备的话，直接执行，不走线程
		$back=auatoexectest(array($src_row));
		print_r("<returnstr>".json_encode($back)."<returnstr>\n");
		exit();
	}
	
	
	if($nowcount==$maxcount){
		$key=SdThread::waitpid();
		if($key=="-1"){
			break;
		}
		//print_r($val);
		//print_r("<returnstr>".json_encode($val['return'])."<returnstr>");
		$nowcount--;
	}
	
	//start函数两个参数，第一个是函数名，第二个是参数，只能一个参数
	SdThread::start('auatoexectest',array($src_row));
	$nowcount++;
}


SdThread::waitpids();
foreach(SdThread::$pids as $key => $val){
	//print_r($val);
	print_r("<returnstr>".json_encode($val['return'])."<returnstr>\n");
	$nowcount--;
}


function auatoexectest($myargs){
	$src_row=$myargs[0];
	$pingargs=[
		'count' => "-c 5",
		'timeout' =>"-w 30",
		'packet' => "-s 5"
	];
	
	exec("sudo ping ".$pingargs['count']." ".$pingargs['timeout']." ".$pingargs['packet']." ".$src_row['na_ip']." 2>&1", $res,$code);
		$rc=0;//成功率
		$timeu='unknown';//用时多少
		$restext="";
		foreach($res as $val){
			if(strstr($val,'icmp_')){
				if(strstr($val,'time=')){
					$timeu=str_replace(' ','',explode('time=',$val)[1]);
				}else{
					$timeu=explode(' ',explode('icmp_seq',$val)[1],2)[1];
				}
			}
			if(strstr($val,'packet loss')){
				$timparr=explode(' ',explode('% packet loss',$val)[0]);
				$rc=100-$timparr[count($timparr)-1];
			}
			
		}
		$restext=join('',$res);
		//echo $restext."=====";
		/*
		$res=preg_match( ' (\S*?)% packet loss.*?rtt min/avg/max/mdev = 0.499/0.578/0.689/0.061 ms/is' , $restext , $matchs );
		if($res){
			
		}
		*/
		
		\DB::table('sdaaa.nas')->where('na_ip',$src_row['na_ip'])
			->update([
				'na_status'=> ($rc.'')
			]);
		
		return ["code"=>$rc,"ip"=>$src_row['na_ip']];

}
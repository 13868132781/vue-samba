<?php
/*
*该类用子进程模拟子线程
*
*$pids,一个数组，用于管理所有的子进程pid
*$pids[]['pid']
*$pids[]['write']
*$pids[]['read']
*$pids[]['status']
*$pids[]['back']
*$pids[]['return']
*
*
*sdthread::init()
*
*sdthread::start()
*
*sdthread::waitpids()
*
*
*sdthread::waitpid()
*
*
*/
class sdthread{
	public static $pids=array();
	
	//这个函数应该在所有的include之后，以及所有其他逻辑代码之前
	//因为在这个函数之前的代码，父子进程都会执行，而之后的代码，都不会执行
	//子进程就是在这个位置执行thread函数，执行完成后退出脚本
	public static function init(){
		global $argv;
		if($argv[1] !='' and $argv[1]=="sdthreadfun"){
			$fun=trim(fgets(STDIN,2014));
			$some=trim(fgets(STDIN,2014));
			$fun_args=json_decode($some,true);
			$returnstr=$fun($fun_args);
			echo "|(|[|{|:|".json_encode($returnstr)."|(|[|{|:|";
			exit();
		}
	}

	//开启线程，参数1是函数名，参数2可选，为函数参数
	public static function start($fun,$args="",$script=""){

		$descriptorspec = array(
		   0 => array("pipe", "r"),  // 标准输入，子进程从此管道中读取数据
		   1 => array("pipe", "w"),  // 标准输出，子进程向此管道中写入数据
		   2 => array("pipe", "w"), // 标准错误，写入到一个文件
		);
		if($script=="") $script=$_SERVER["SCRIPT_FILENAME"];
		$gdo=proc_open("php  ".$script." 'sdthreadfun' 2>&1", $descriptorspec, $pipes);
		fwrite($pipes[0],$fun."\n");
		fwrite($pipes[0],json_encode($args)."\n");
		$gdos['pid']=$gdo;
		$gdos['write']=$pipes[0];
		$gdos['read']=$pipes[1];
		$gdos['status']='0';
		$gdos['back']="";
		$gdos['return']="";
		self::$pids[]=$gdos;
		
	}
	
	//等到所有子进程结束，再返回
	public static function waitpids(){
		while(1){
			$key=self::waitpid();
			if($key=="-1"){
				break;
			}	
		}
	}
	
	//等待某一个子线程结束，返回该子线程在pids里的key值
	//i如果所有子线程都已经结束，那就返回-1
	public static function waitpid(){
		$eof_count=0;
		while(1){
			if($eof_count >= count(self::$pids) ){
				return "-1";
			}
			$eof_count=0;
			foreach(self::$pids as $key => $val){
				if(self::$pids[$key]['status']=="eof"){
					$eof_count++;
					continue;
				}
				stream_set_blocking(self::$pids[$key]['read'], 0);
				while(1){
					$str=fread(self::$pids[$key]['read'],1024);
					self::$pids[$key]['back'].=$str;
					if(feof(self::$pids[$key]['read'])){
						self::$pids[$key]['status']="eof";
						self::$pids[$key]['return']=json_decode(explode("|(|[|{|:|",self::$pids[$key]['back'])[1]);
						
						fclose(self::$pids[$key]['read']);
						fclose(self::$pids[$key]['write']);
						proc_close(self::$pids[$key]['pid']);
						
						return $key;
					}
					if($str==""){
						break;
					}
				}
			}
		}
	}
	
}
//引入类时，直接就执行这个，检测该文件是否是子进程
//这个函数应该在所有include之后，以及所有其他代码之前
sdthread::init();




/*

//这个函数应该在所有include之后，以及所有其他代码之前
sdthread::init();

echo "this is father<br>\n";

for($i;$i<5;$i++){
	//start函数两个参数，第一个是函数名，第二个是参数，只能一个参数
	sdthread::start('thread_fun',$i);
}

//子进程只能有一个参数，可以是任意类型
//函数返回数据，可以在父进程里通过getreturn获得，也可以是任何类型
function thread_fun($args){
	echo "this is son<br>\n";
	//var_dump($args);
	sleep(10-$args);
	
	$ping=exec("ping -c 4 192.168.0.214");
	
	$str[]="hong";
	$str[]=$ping;
	return $str;
}

sdthread::waitpids();
foreach(sdthread::$pids as $key => $val){
	echo " father get from son : ".$val['return'][1]."<br>\n";
	//sleep(5);
}

for($q;$q<7;$q++){
	$key=sdthread::waitpid();
	if($key=="-1"){
		echo "all to end<br>\n";
	}else{
		echo " father get from son : ".sdthread::$pids[$key]['return'][1]."<br>\n";
	}
}



*/



?>

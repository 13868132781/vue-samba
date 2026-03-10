<?php
set_time_limit(0);
//该脚本是唯一的软域超级服务，会被注册到系统服务列表里，开机会自动执行
//在这里个脚本里做的工作有：
//1.执行开机启动项
//2.打开该打开的服务
//3.如果配置了双机的话，运行双机
//。。。。。


function start(){
	exec("php ".__DIR__."/../Pages/SdStart/Script/myrun.php ");
	exec("php ".__DIR__."/../Pages/Service/Script/myrun.php ");
}




//下面部分不要修改
/////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////
$selfPath = __FILE__;
$selfName = rtrim(basename($selfPath),'.php');

function install(){
	global $selfPath,$selfName;
	$service = <<<SERVICE
[Unit]
Description={$selfName}
ConditionPathExists={$selfPath}

[Service]
Type=forking
ExecStart=php {$selfPath} start
TimeoutSec=0
StandardOutput=tty
RemainAfterExit=yes
SysVStartPriority=99

[Install]
WantedBy=multi-user.target

SERVICE;
	$tempfile='/tmp/'.$selfName.'.service';
	$systemfile='/etc/systemd/system/'.$selfName.'.service';
		
	file_put_contents($tempfile,$service);
		
	if(file_exists($systemfile)){
		$diff=exec('sudo diff '.$tempfile.' '.$systemfile);
		if($diff!=''){
			exec('sudo rm '.$systemfile);
		}else{
			exec('sudo rm '.$tempfile);
			return;
		}
	}
		
		exec('sudo mv '.$tempfile.' '.$systemfile);
		exec('sudo systemctl enable '.$selfName);
	exit(0);
}


function uninstall(){
	global $selfPath,$selfName;
	$systemfile='/etc/systemd/system/'.$selfName.'.service';
	exec('sudo rm '.$systemfile);
	exec('sudo systemctl disable '.$selfName);
}




function help(){
	global $selfPath,$selfName;
	echo <<<help
install：
uninstall：
start：
help;
}



/*************************************************************/
/*************************************************************/
if(count($argv)>1){
	switch($argv[1]){
		case '-h':
		case '--help':
			help();
			break;
		case 'install':
			install();
			break;
		case 'uninstall':
			uninstall();
			break;
		case 'start':
			start();
			break;
		default:
			echo $selfName.": Error in argument 1, no argument for option '".$argv[1]."'\n".$selfName.": Try php '".$selfName." -h' for more information.\n";
	}
}



//启动服务：systemctl start xxx.service
//关闭服务：systemctl stop xxx.service
//重启服务：systemctl restart xxx.service
//显示服务的状态：systemctl status xxx.service
//在开机时启用服务：systemctl enable xxx.service
//在开机时禁用服务：systemctl disable xxx.service
//查看服务是否开机启动：systemctl is-enabled xxx.service
//查看已启动的服务列表：systemctl list-unit-files|grep enabled
//查看启动失败的服务列表：systemctl --failed

?>
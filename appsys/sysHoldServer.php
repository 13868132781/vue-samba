<?php require_once(__DIR__.'/../just/a.php');?>
<?php
/*
框架维护服务
清理各种日志，避免日志越来越大
*/

echo json_encode($argv);
echo "\n";

while(1){
	echo date('Y-m-d H:i:s').": exec\n";
	
	//清理服务的日志文件，并检测其状态
	$service = new appsys\service\service();
	$service->logClear();
	$service->statusCheck();//检测服务状态错误
	
	//检测调度错误
	$sysCron = new appsys\sysCron\sysCron();
	$sysCron->statusCheck();
	
	//系统登录会话表和系统操作审计表，只保留近1000条数据
	clearDBTable($sysCfgInfo['sysDB'].'.zlog_syslogin','id','slid','1000');
	clearDBTable($sysCfgInfo['sysDB'].'.zlog_sysoper','id','soid','1000');
	
	sleep(120);
	//sleep(60*60*24);//一天一运行
}

?>
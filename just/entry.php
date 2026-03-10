<?php
$sdLogGlobalStartTime = microtime(true);
require_once(__DIR__."/../app/config.php");
require_once(__DIR__."/../appsys/config.php");
require_once(__DIR__."/sdLog.php");//log类，也是全局的
\sdLog::say("end: get config and sdLog");
\sdLog::say("end: sdLog say onece");

//修改当前工作目录,不会改变__DIR__
//避免一些php里，写日志到当前工作目录
chdir(dirname(__DIR__));// getcwd()
//设置站点目录权限，避免一些修改创建文件的操作失败,但有时很耗时，所以暂时注释掉
//exec("sudo chmod 777 -R ".dirname(__DIR__));//耗时0.03s
\sdLog::say("end: chmod 777 web root");

ini_set("display_errors", "On");



require_once(__DIR__."/autoload.php");

//加载公共的全局类和函数
require_once(__DIR__."/global.php");//全局函数
require_once(__DIR__."/DB/DB.php");//DB类，也是全局的
\sdLog::DBLogEnable();//开始DB的日志记录功能
require_once(__DIR__."/table/table.php");//table类，也是全局的
require_once(__DIR__."/aht/sdMsgSend.php");
require_once(__DIR__."/aht/sdRandom.php");
require_once(__DIR__."/aht/sdOtp.php");
require_once(__DIR__."/aht/qrcode/sdQrcode.php");

\sdLog::say("end: require_once");

//分解参数，获得router和func
$router = $_GET["router"];
$routers = explode("@",$router);
if(count($routers)!= 2){
	echo '路由不合规：'.$router;
	exit(0);
}
$routerClass = $routers[0];
$routerFunc = $routers[1];

//类名和函数名，类空间必须反斜杠
$routerClass = 'app'.$routerClass;

$className = str_replace("/","\\",$routerClass);
$funcName = 'api_'.$routerFunc;

\sdLog::say("end: display router");


//fetch 默认发送的是 application/json 格式的数据，而 PHP 的 $_POST 数组只能解析 application/x-www-form-urlencoded 或 multipart/form-data 格式的数据。
//所以，即使fetch的header设置为application/json，php也不识别
//js使用 FormData 或 URLSearchParams 可以直接使用 $_POST 数组获取数据
$body = @file_get_contents('php://input');
$bodyjson = json_decode($body,true);//传来的post信息
if(!$bodyjson){
	$bodyjson = $_POST;
}
//////////////////////以上做一些必要加载和处理/////////////////////////////////////

\sdLog::say("end: get body");


//处理/auth/auth类里的请求
//所有/auth/auth里的函数不会做登录检查
if($routerClass=="appsys/auth/auth"){
	$myclass = new $className($bodyjson,$routerFunc); 
	$back = $myclass->$funcName();
	echo json_encode($back);
	
	goto logEntry;
	exit(0);
}

\sdLog::say("end: auth process"); 

//检查是否登录
$auth =  new appsys\auth\auth();
$withUpdate=true;//是否更新会话的结束时间。页面定时获取数据的请求不应该更新这个时间。
if(isset($bodyjson['isPollingRequest']) and $bodyjson['isPollingRequest']){
	$withUpdate=false;
}
$code = $auth->loginCheck($withUpdate);
if($code==1){
	//返回-1，会被ajax拦截，跳转到登录页面
	echo json_encode(['code'=>-1,'data'=>'未登录或会话已过期']);
	exit(0);
}

\sdLog::say("end: login check");

//检查类是否存在
if(!class_exists($className)){
	echo "类不存在：".$className;
	exit(0);
}

//检查父类是否合规
$parent = get_parent_class($className);
if($parent!='table'){
	echo "未继承自合法父类：".$className;
	exit(0);
}

\sdLog::say("end: check class is good");

//实例化类对象
$myclass = new $className($bodyjson,$routerFunc); 

\sdLog::say("end: new cloass");

//审计信息检查
$back = $myclass->auditCheck();
if($back){
	echo json_encode($back);
	exit(0);
}

\sdLog::say("end: audit check");
//执行函数
$back = $myclass->$funcName();
\sdLog::say("end: func execute");

//审计执行
$myclass->auditExec($back);

\sdLog::say("end: audit");

echo json_encode($back);

\sdLog::say("end: echo result");

logEntry://记录entry日志
\sdLog::say("end");
$endTime = microtime(true);
\sdLog::entry([
	'router'=>$_GET["router"],
	'post'=>$body,
	'take'=>$endTime-$sdLogGlobalStartTime,
]);

\DB::close();
exit(0);
?>
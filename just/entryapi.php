<?php
/*
url API，用于通过url操作本系统
访问 https://ip:port/just/entryapi.php，以查看详细参数
*/
ini_set("display_errors", "On");

require_once("./autoload.php");


require_once(__DIR__."/sdLog.php");
require_once(__DIR__."/DB/DB.php");
require_once(__DIR__."/table/table.php");
require_once(__DIR__."/aht/sdOtp.php");
require_once(__DIR__."/aht/qrcode/sdQrcode.php");

//分解参数
$htGet = $_GET;
if(count($htGet)==0){
	echo '<html><head><meta charset="utf-8"></head><body>';
	echo getArgsHelp();
	echo '</body></html>';
	exit(0);
}
$htArgs = getArgsArr($htGet);
$sysPost = $htArgs['sysPost'];
$routerClass = $htArgs['class'];
$routerFunc = $htArgs['func'];
$bodyjson= $htArgs['post'];;


//类名和函数名，类空间必须反斜杠
$className = "app".str_replace("/","\\",$routerClass);
$funcName = 'api_'.$routerFunc;

//登录
$auth =  new app\auth\auth($sysPost);
$back = $auth->api_login();
if($back['code']!='0'){
	echo json_encode($back);
	exit(0);
}

//===============下面部分和entry.php相同=====================================================//
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

//实例化类对象
$myclass = new $className($bodyjson,$routerFunc); 

//审计信息检查
$back = $myclass->auditCheck();
if($back){
	echo json_encode($back);
	exit(0);
}

//执行函数
$back = $myclass->$funcName();

//审计执行
$myclass->auditExec($back);

echo json_encode($back);

$auth->api_logout();
 


function getArgsArr($htGet){
	$myMap = getArgsMap();
	$back=[
		'sysPost'=>['user'=>'','pass'=>''],
		'class'=>'',
		'func'=>'',
		'post'=>[
			'formVal'=>[],
		]
	];	
	foreach($htGet as $htGetK=>$htGetV){
		if($htGetK=='sysuser'){
			$back['sysPost']['user']=$htGetV;
		
		}elseif($htGetK=='syspass' ){
			$back['sysPost']['pass']=$htGetV;
		
		}elseif($htGetK=='obj'){
			if(isset($myMap[$htGetV]['_real_'])){
				$back['class'] = $myMap[$htGetV]['_real_'];
			}else{
				echo json_encode(['code'=>1,'msg'=>'unknown obj: '.$class]);
				exit(0);
			}
			
		}else if($htGetK=='oper'){
			if(isset($myMap['_func_'][$htGetV])){
				$back['_func_'] = $myMap['_func_'][$htGetV];
			}else{
				echo json_encode(['code'=>1,'msg'=>'unknown oper: '.$htGetV]);
				exit(0);
			}
			
		}else{
			if(isset($myMap[$htGet['obj']][$htGet['oper']][$htGetK])){
				$formValKey = $myMap[$htGet['obj']][$htGet['oper']][$htGetK][0];
				$back['post']['formVal'][$formValKey] = $htGetV;
			}else{
				echo json_encode(['code'=>1,'msg'=>'unknown arg: '.$htGetK]);
				exit(0);
			}
		}
	}	
	
	return $back;
}


function getArgsHelp(){
	$myMap = getArgsMap();
	//$preurl = 'https://ip:port/php/api/entryapi.php?';
	
	$html='<table cellpadding=3 cellspacing=0>';
	
	foreach($myMap as $myMapK=>$myMapV){
		if(stristr($myMapK,'_')){
			continue;
		}
		foreach($myMapV as $myMapVK=>$myMapVV){
			if(stristr($myMapVK,'_')){
				continue;
			}
			$html.='<tr><td>一.</td><td colspan="10">'.$myMapVV['_mark_'].'</td></tr>';
			$getArgs='sysuser=...&syspass=...&obj='.$myMapK.'&oper='.$myMapVK;
			foreach($myMapVV as $myMapVVK => $myMapVVV){
				if(stristr($myMapVVK,'_')){
					continue;
				}
				$getArgs.='&'.$myMapVVK.'=...';
			}
			$url = ($_SERVER['HTTPS'] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$html.='<tr><td></td><td>示例：</td><td>'.$url.'?'.$getArgs.'</td></tr>';
			foreach($myMapVV as $myMapVVK => $myMapVVV){
				if(stristr($myMapVVK,'_')){
					continue;
				}
				$html.='<tr><td></td><td>'.$myMapVVK.'</td><td>'.$myMapVVV[1].'</td></tr>';
			}
			$html.='<tr><td>&nbsp;</td></tr>';
		}
	}
	$html.='<table>';
	return $html;
	
}



function getArgsMap(){
	return [
		'_func_'=>[
			'add'=>'crudAddSave',
			'mod'=>'crudModSave',
		],
		'user'=>[
			'_real_'=>'/radUser/radUser',
			'_mark_'=>'自然人',
			'add'=>[
				'_mark_'=>'自然人添加',
				
				'name'=>['us_name','用户名称'],
				'user'=>['us_user','用户登录名'],
			],
			'mod'=>[
				'_mark_'=>'自然人修改',
				'name'=>['us_name','用户名称'],
				'user'=>['us_user','用户登录名'],
				
			]
		]
	];
}
?>
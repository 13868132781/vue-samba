<?php 
//这个是打开PHP错误显示
ini_set("display_errors","on");//总开关
//ini_set("error_reporting",E_ALL );
ini_set("error_reporting",E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING);
//程序执行完才结束
set_time_limit(0);
?>
<?php require_once(dirname(__FILE__).'/mysqli.php');?>
<?php 
//在使用时，这里都不要改
//所有页面都要包含这个
//全局变量----php 连接mysql的两种方式
// function sdmysql($p=true){
	// if($p==true){
		// $hlcphp_mysql_lj = mysql_pconnect("localhost","root","jbgsn!2716888") or trigger_error(mysql_error(),E_USER_ERROR); 
	// }else{
		// $hlcphp_mysql_lj = mysql_connect("localhost","root","jbgsn!2716888") or trigger_error(mysql_error(),E_USER_ERROR); 
	// }
	// mysql_query("SET NAMES 'utf8'");
	// return $hlcphp_mysql_lj;
// }
// $mysite=sdmysql();

//数据库连接
function new_sdmysql(){
	$mysite = new mysqli("127.0.0.1", "softdomain", "jbgsn!2716888");   
if ($mysite->connect_error) {  
    trigger_error("Connection failed: " . $mysite->connect_error, E_USER_ERROR);  
}   
$mysite->set_charset("utf8");
return $mysite;
}
$new_mysite=new_sdmysql();

//判断一个表是不是存在
function tableexist($db,$table){
	global $mysite;
	$exist_obj=mysql_query("select TABLE_NAME from INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA='".$db."' and TABLE_NAME='".$table."'", $mysite) or die(mysql_error());
	return mysql_num_rows($exist_obj);
}


//一、初始化$_HLCPHP
$_HLCPHP["name"]="杭州软域XXXX系统";
$_HLCPHP["type"]="default";
$_HLCPHP["skin"]="default";
$_HLCPHP["index"]="index";
$_HLCPHP["left"]=array(
	"first"=>array(
		'name'=>"首页概况信息",
		'path'=>"main/default.php",
		'list'=>array(
			'firstone'=>array(
			'name'=>'管理管理管理',
				'path'=>'main/default.php'
			)
		)
	)
);



//二、读取cookie或session
if(json_decode($_COOKIE[$_SERVER['SERVER_PORT']])){
	$_HLCPHP['global']=json_decode($_COOKIE[$_SERVER['SERVER_PORT']],true);
}else{
	$_HLCPHP['global']=unserialize($_COOKIE[$_SERVER['SERVER_PORT']]);
}

//echo $_HLCPHP['global']['acid'] 
//Array ( [mark] => system [user] => system [pass] => [acid] => 1 [seid] => 6654 [client] => 192.168.0.196 [organs] => [type] => 0 )   ;

//三、导入对应的站点配置文件
$directory=dirname(__FILE__)."/../webs";
$mydir = dir($directory);
while($file = $mydir->read()){
	if(($file!=".") AND ($file!=".."))
		require_once($directory.'/'.$file);
}
if($_HLCPHP["name"]=="杭州软域XXXX系统"){//没找到对应的系统
	//echo "invalid system \n";
	//exit;
}



//以下是函数，包含两个css，如果有第二个的话，和全部的js

//<meta http-equiv="X-UA-Compatible" content="IE=7" />
function hlcphp_html_head(){
global $_HLCPHP;
$res='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>'.$_HLCPHP['name'].'</title>';


//include下面的skin下面的default，有css或者js都包含进来
$mydir = dir(dirname(__FILE__)."/../skin/default");
while($file = $mydir->read()){
	if(($file!=".") AND ($file!="..")){
		if(stristr($file,"css")){
			$res.='
<link href="../include/skin/default/'.$file.'" rel="stylesheet" type="text/css" />';
		}else if(stristr($file,"js")){
			$res.='
<script type="text/javascript" src="../include/skin/default/'.$file.'"></script>';
		}
	}
}


//如果有额外的css和js就继续引用进来	
$mydir = dir(dirname(__FILE__)."/../skin/".$_HLCPHP['skin']);
while($_HLCPHP['skin']!='default' and $mydir and $file=$mydir->read()){
	if(($file!=".") AND ($file!="..")){
		if(stristr($file,"css")){
			$res.='
<link href="../include/skin/'.$_HLCPHP['skin'].'/'.$file.'" rel="stylesheet" type="text/css" />';
		}else if(stristr($file,"js")){
			$res.='
<script type="text/javascript" src="../include/skin/'.$_HLCPHP['skin'].'/'.$file.'"></script>';
		}
	}
}
	
$res.='
<script type="text/javascript" src="../include/js/rewrite.js"></script>
<script type="text/javascript" src="../include/js/md5.js"></script>
<script type="text/javascript" src="../include/js/my_ajax.js"></script>
<script type="text/javascript" src="../include/js/select.js"></script>
<script type="text/javascript" src="../include/tools/My97DatePicker/hlctime.js"></script>
<script type="text/javascript" src="../include/js/html5/html5.js"></script>
<script type="text/javascript" src="../include/js/html5/excanvas.compiled.js"></script>
<script type="text/javascript" src="../include/js/dialog/zDialog/zDialog.js"></script>
<script type="text/javascript" src="../include/js/dialog/zDialog/zDrag.js"></script>
<script type="text/javascript" src="../include/js/pageupdate_diag.js"></script>
<script type="text/javascript" src="../include/js/tree/folder.js"></script>
<script type="text/javascript" src="../include/js/tree/checkbox.js"></script>
<script type="text/javascript" src="../include/js/check/check_func.js"></script>
<script type="text/javascript" src="../include/js/check/check_browser.js"></script>
<script type="text/javascript" src="../include/js/hlcaddbox.js"></script>
<script type="text/javascript" src="../include/js/checkclt.js"></script>
<script type="text/javascript" src="../include/js/troper.js"></script>
<script type="text/javascript" src="../include/js/tableclick.js"></script>
<script>
IMAGESPATH = "../include/js/dialog/zDialog/images/";
function g(o){return document.getElementById(o);}
function s(node,id){if(node && node.nodeType==1 && node.hasChildNodes){var sonnodes = node.childNodes; for (var i = 0; i < sonnodes.length; i++){var sonnode = sonnodes.item(i);if(sonnode.id==id){return sonnode;}var backnode=s(sonnode,id);if(backnode){return backnode;}}}return null;}
if("'.$_GET['scrollTop'].'"!=""){
	window.onload=function(){
		document.documentElement.scrollTop="'.$_GET['scrollTop'].'";
		document.body.scrollTop="'.$_GET['scrollTop'].'";
	}
}
//if(top.location.href.indexOf("/login.php")==-1 //&&top.location.href.indexOf("/index.php")==-1 //&&top.location.href.indexOf("/frame.php")==-1)
//{
//	top.location="请不要直接访问内部页面";
//}

</script>
<style>
* {margin:0;padding:0;font-size: 12px;}
</style>

</head>
';
return $res;
}

//包含本目录下其他文件  
require_once(dirname(__FILE__).'/GetSQLValueString.php');
require_once(dirname(__FILE__).'/organ_equip.php');
require_once(dirname(__FILE__).'/tabtable.php');
require_once(dirname(__FILE__).'/pagination.php');
require_once(dirname(__FILE__).'/url.php');
require_once(dirname(__FILE__).'/get_organ.php');
require_once(dirname(__FILE__).'/get_orgau.php');
require_once(dirname(__FILE__).'/ips_eth.php');
require_once(dirname(__FILE__).'/ips_php.php');
require_once(dirname(__FILE__).'/write_systemsen.php');
require_once(dirname(__FILE__).'/write_systemlog.php');
require_once(dirname(__FILE__).'/del.php');
require_once(dirname(__FILE__).'/sddownload.php');
require_once(dirname(__FILE__).'/get_server_port.php');
require_once(dirname(__FILE__).'/envir.php');
require_once(dirname(__FILE__).'/get_maxid.php');
require_once(dirname(__FILE__).'/hlcotp.php');
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/encode.php');
require_once(dirname(__FILE__).'/msgsend.php');
require_once(dirname(__FILE__).'/usersafe.php');
require_once(dirname(__FILE__).'/export_txt.php');
require_once(dirname(__FILE__).'/random.php');

//以下是检测是否登录
//echo $_COOKIE[$_SERVER['SERVER_PORT'].'_loginname'];

if($apiargs['nologin']=='' and !stristr($_SERVER['PHP_SELF'],'/login.php')){
	if($_HLCPHP['global']['user']==""){
		echo "<script>top.location.href='/index.php'</script>";
	}

	if($apiargs['nosen']=='' and check_systemsen($_HLCPHP['global']['seid'])=='0'){
		echo "<script>top.location.href='/index.php'</script>";
	}else{
		if($_GET['systemsen_keepalive'] == "" ){
			action_systemsen($_HLCPHP['global']['seid']);
		}
	}
}

?>

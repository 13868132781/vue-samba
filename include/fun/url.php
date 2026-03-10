<?php 

function curpageurl() 
{
    $pageURL = 'http';

    if ($_SERVER["HTTPS"] == "on") 
    {
        $pageURL .= "s";
    }
    $pageURL .= "://";

    if ($_SERVER["SERVER_PORT"] != "80") 
    {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } 
    else 
    {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}
$_HLCPHP['urlnow']="curpageurl";



//本函数是处理参数串，不包含url参数前面的php页面,即返回？之后的部分
//$a['query']="id=33&head=h"; 为空表示，取当前页面整串url
//$a['key']="id";
//$a['val']="50";  为空，表示把这个删除
function make_new_url($query,$key,$val){
	$new_qury="";
	$getit="";
	if($query==''){
		$old_qury=$_SERVER['QUERY_STRING'];
	}else{
		$old_qury=$query;
	}
	$params = explode("&",$old_qury);
	foreach ($params as $param) {
		if($param == "" ) continue;
		$pas=explode("=",$param);
		if ($pas[0]==$key){
			$getit="1";
			if($val != "" ){
				$new_qury.="&".$key."=".$val;
			}
		}else{
			$new_qury.="&".$param;	
		}
	}
	if($getit=="") $new_qury.="&".$key."=".$val;
	
	return trim($new_qury,"&");
	
}

$_HLCPHP['urlnew']="make_new_url";




$old_qury=$_SERVER['QUERY_STRING'];
$queryarray=array();
$params = trim($old_qury,'?');
$paramsc = explode("&",$params);
foreach($paramsc as $para){
	$paras = explode("=",$para);
	if($paras[0]=='' or $paras[1]=='') continue;
	$queryarray[$paras[0]]=$paras[1];
}

function joinquery_mymymymymy($myqueryarray){
	if($myqueryarray=='') $myqueryarray=$queryarray;
	foreach($myqueryarray as $urlk => $urlv){
		$res.=$urlk.'='.$urlv.'&';
	}
	return trim($res,'&');
}

$_HLCPHP['joinquery']="joinquery_mymymymymy";








?>
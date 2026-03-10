<?php
if(($_SERVER['SERVER_PORT']=='4430') or ($_SERVER['SERVER_PORT']=='3031' ) or ($_SERVER['SERVER_PORT']=='443' )){
	$_HLCPHP["name"]="软域科技用户自助平台";
	$_HLCPHP["type"]="aau";
	$_HLCPHP["skin"]="aaa";
	$_HLCPHP["index"]="index_aau";
	$_HLCPHP["left"]=array(
		"item1"=>array(
			'name'=>"设备列表管理",
			'path'=>"aau_src/default.php",
		),
		
		"item2"=>array(
			'name'=>"登录审计管理",
			'path'=>"aaa_login/default.php?user=".$_HLCPHP['global']['user']."&onlyuser=aau",
		),

                "itemlog"=>array(
                        'name'=>"用户认证日志",
                        'path'=>"aau_exlog/mapi_auth.php",
                        //'img'=>'include/image/new-tb/yonghu.png',

                        'list'=>array(
								'itemsub3'=>array(
                                        'name'=>'激活信息',
                                        'path'=>'aau_exlog/active.php'
                                ),
								'itemsub4'=>array(
                                        'name'=>'OWA认证日志',
                                        'path'=>'aau_exlog/owa_auth.php'
                                ),
                                'itemsub5'=>array(
                                        'name'=>'MAPI认证日志',
                                        'path'=>'aau_exlog/mapi_auth.php'
                                ),                              
                                'itemsub8'=>array(
                                        'name'=>'手机认证日志',
                                        'path'=>'aau_exlog/activesync_auth.php'
                                ),
                                'itemsub10'=>array(
                                        'name'=>'RPC认证日志',
                                        'path'=>'aau_exlog/rpc_auth.php'
								),
                                'itemsub12'=>array(
                                        'name'=>'标准协议认证日志',
                                        'path'=>'aau_exlog/pop3_auth.php'
                                ),
								'itemsub13'=>array(
                                        'name'=>'EWS协议认证日志',
                                        'path'=>'aau_exlog/ews_auth.php'
                                ), 

                        )
                ),
		
 
		"item4"=>array(
			'name'=>"用户修改密码",
			'path'=>"aau_user/default.php",
		),
		"item5"=>array(
			'name'=>"用户手机号码",
			'path'=>"aau_user/default_mobile.php",
		),
                "item6"=>array(
                        'name'=>"二维码扫描验证",
                        'path'=>"aau_user/default_qrcode.php",
                ),
			
	);
	
		
	if($_HLCPHP['global']['user_email']==''){
		unset($_HLCPHP['left']['itemlog']);
	}

	$_HLCPHP["service"][]="radius";
	$_HLCPHP["service"][]="tacplus";
	$_HLCPHP["service"][]="rsyslogd";
	
	
	
	
	$_HLCPHP["notadmin"]=true;//是否是自然人登录，用于判断选择哪个密码复杂度
	$_HLCPHP["loginstart"]="function_random_lkjhgbwde44565544fe";
	function function_random_lkjhgbwde44565544fe($user){
		global $_POST,$mysite,$_HLCPHP;
		//限制组锁定
		$_HLCPHP['usersafecheck']("radius.rad_user","userID","SELECT * FROM radius.rad_user where UserName='".$_POST['user']."'");

		$login_sql="SELECT a.*,b.Attribute as passtype,b.value as passreal FROM (radius.rad_user a left join radius.radcheck b on a.UserName=b.UserName and b.Attribute in ('MD5-Password','Crypt-Password','Cleartext-Password')) where a.UserName='".$_POST['user']."' and a.username not in ('\$enab15\$','\$beifen')";
		$login_obj = mysql_query($login_sql, $mysite) or die(mysql_error());
		$login_row = mysql_fetch_array($login_obj); 
		$login_num = mysql_num_rows($login_obj);
		$login_row['authtype']='static';
		if($login_row['seed']!='' and $_HLCPHP['envir']['radiusmode']=='2'){
			$login_row['authid']='5';
			$login_row['ac_seed']=$login_row['seed']; 
		} 
		$login_row['tablename']="radius.rad_user";
		$login_row['usercol']="UserName";
		$login_row['modpass']="../aaa_user/modpass.php";
		return $login_row;
	}
	$_HLCPHP["logincookie"]="function_random_kggt765y6554t4";
	function function_random_kggt765y6554t4($login_row,$sessionid){
		global $_POST,$mysite,$_HLCPHP;
		$thiscookie['user']=$login_row['UserName'];
		$thiscookie['pass']=$login_row['input_pass'];
	        $thiscookie['client']=get_client_ip();
		$thiscookie['seid']=$sessionid;
		$thiscookie['user_email']=$login_row['user_email'];
		setcookie($_SERVER['SERVER_PORT'],serialize($thiscookie),0,'/');
	}
	
	
	
	global $mysite;
	$table_sql="select * from radius.aaa_nas_class order by c_oid";
	$table_obj=mysql_query($table_sql, $mysite) or die(mysql_error());
	$table_row=mysql_fetch_assoc($table_obj);
	$table_num=mysql_num_rows($table_obj); 
	if($table_num!=0){
		do{
			$thisrow['id']=$table_row['class_id'];
			$thisrow['name']=$table_row['class_name'];
			$thisrow['enable']=$table_row['class_enable'];
			$thisrow['default']=$table_row['class_default'];
			$_HLCPHP["class"]['maps'][$table_row['class_id']]=$table_row['class_name'];
			$_HLCPHP["class"]['list'][]=$thisrow;
		}while($table_row=mysql_fetch_assoc($table_obj));
	}
	
	
	$_HLCPHP["srcorder"][0]='norder,inet_aton(nasname)';
	$_HLCPHP["srcorder"][1]='norder,shortname';
	$_HLCPHP["srcorder"][2]='norder,id';
	
}
?>

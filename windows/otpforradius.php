<?php require_once(dirname(__FILE__).'/../include/fun/hlcotp.php'); ?>
<?php require_once(dirname(__FILE__).'/../include/fun/hlclog.php'); ?>
<?php

function logError1($content) 
{ 
  $logfile = "/var/log/softdomain/win_2fa.txt"; 
  if(!file_exists(dirname($logfile))) 
  { 
    @File_Util::mkdirr(dirname($logfile)); 
  } 
  error_log(date("[Y-m-d H:i:s]")." -[".$_SERVER['REQUEST_URI']."] :".$content."\n",3,$logfile); 
}
 

//这一段定义三个函数，第一个产生随机数,
function random($length, $numeric = 0) {
	if (!isset($length)) $length = rand(2,4);
	//加入$length=rand(2,4)就可以了
	PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
	if ($numeric) {
		$hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
	} else {
		$hash = '';
		//$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		$chars = '0123456789';
		$max = strlen($chars) - 1;
		for ($i = 0; $i < $length; $i++) {
			$hash .= $chars[mt_rand(0, $max)];
		}
	}
	return $hash;
}

//第二个函数 阿里云的短信接口
function msgsend_aly1($call,$msg){
	
	exec('curl --connect-timeout 2 -i -k --get --include "http://smsmsgs.market.alicloudapi.com/smsmsgs?param='.urlencode($msg).'&phone='.$call.'&skin=900564&sign=500375"  -H \'Authorization:APPCODE e05a4656d8314321927f7bd69298ca81\' > /dev/null 2>&1',$output,$return);
	return $return;
}

//这是第三个函数，发送短信用的
function sms_password($u){
$user = $u;
$code = random(6);	
$mysite= mysql_connect("localhost","root","jbgsn!2716888") or trigger_error(mysql_error(),E_USER_ERROR); 
mysql_query("SET NAMES 'utf8'");

$qury_sql="SELECT a.*,b.or_name FROM (select * from radius.rad_user where username='".mysql_escape_string($user)."') a left join radius.organ b on a.organ=b.or_id";
$qury_obj=mysql_query($qury_sql, $mysite) or die(mysql_error());
$qury_row=mysql_fetch_assoc($qury_obj);
$qury_num=mysql_num_rows($qury_obj);

//也许是通过短信，也许是邮件，两个都获取来
$phone=$qury_row["mobile_nmb"];
$email=$qury_row["user_email"];

//echo $phone.$email;  显示这个用户的手机和邮箱
$query_h = "SELECT en_val FROM system.aaa_envir where en_key='otp_keep'"; 
$p_h = mysql_query($query_h, $mysite) or die(mysql_error());
$row_h = mysql_fetch_assoc($p_h);
$otpkeep=(int)$row_h['en_val'];
$useold=false;

//2020-09-04 14:37:03_954981 这个是one_pass的格式
if($otpkeep>0){
	$onepasss = explode('_',$qury_row['one_pass']);
	if(count($onepasss)==2 and time()<(strtotime($onepasss[0])+$otpkeep)){
		//如果时间没到，就不用新生成的code,用老的
		$code = $onepasss[1];
		$useold=true;// echo time()."|".(strtotime($onepasss[0])+$otpkeep);
	}
}
  //查询发送短信的方式
    $smsway="SELECT  en_val FROM system.aaa_envir where en_key='sms_way'";
    $smsway_obj = mysql_query($smsway, $mysite) or die(mysql_error());
	$smsway_obj_row1 = mysql_fetch_assoc($smsway_obj);
	$smsway0=$smsway_obj_row1['en_val'];
    
	//如果是2就是邮件发送
	if ($smsway0==2){ 
	//如果选择了邮件发送，但是邮件没设置，则直接失败
	if($email==""){
		exit(1);
	}
	
	//组织msg 的格式
   $query_h = "SELECT en_val FROM system.aaa_envir where en_key='sms_head'"; 
   $p_h = mysql_query($query_h, $mysite) or die(mysql_error());
   $row_h = mysql_fetch_assoc($p_h);
   $sms_h=$row_h['en_val'];

   $query_t = "SELECT en_val FROM system.aaa_envir where en_key='sms_tail'"; 
   $p_t = mysql_query($query_t, $mysite) or die(mysql_error());
   $row_t = mysql_fetch_assoc($p_t);
   $sms_t = $row_t['en_val'];

   //给令牌增加一些前后描述
   $code0 = $sms_h.$code.$sms_t;
	
	$msg = $qury_row['UserName']."[".$qury_row['full_name']."]:".$code0;
	//这里msgsend多加一个参数，表示是用什么方式发送，主要区别邮件还是短信，因为发送的目标不一样
    $res=msgsend($email,$msg,'2');
	
	}
	
    //如果是0就是阿里云，或者其他短信
    if ($smsway0==0){ 	
	   if($phone==""){
	         exit(1);
	   }
	$msg = $qury_row['UserName']."【".$qury_row['full_name']."】: ".$code;
	$res=msgsend_aly1($phone,$msg);
	}	

//这里要根据实际情况调整
//$msg = "用户".$qury_row['full_name']."【".$qury_row['or_name']."】: ".$code;
// 这个返回要按照格式去写，因为上一个 freeradius  定义的是 replay 这里要写一个reply的参数
//echo "Reply-Message := '".$res."'";
if($res!==0){
	//echo "Reply-Message := 'send sms error!".$res."'";
	exit(1);
}
//如果不是保持老密码（密码在一定时间内保持一致）
if(!$useold){
	$onepasssql="update radius.rad_user set one_pass=concat(now(),'_".$code."') where username='".$user."'";
	mysql_query($onepasssql, $mysite) or die(mysql_error());
}

exit(0);	
}
?>
<?php

$cont='start....';
logError1($cont);

$nasip=$_SERVER["REMOTE_ADDR"];
$HLCLOG_LEVEL=1;

$mysite= mysql_connect("localhost","root","jbgsn!2716888") or trigger_error(mysql_error(),E_USER_ERROR); 
mysql_query("SET NAMES 'utf8'");

function getprivatekey(){
	$remote_ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'';

	return "hong";
}

function checkexist($user){ 

    $cont=$user.'检查用户名是否存在....';
    logError1($cont);
	global $mysite;
	$qury_sql="select * from radius.rad_user where username='".mysql_escape_string($user)."'";
	HLCLOG("sql for check exist:\n".$qury_sql);
	$qury_obj=mysql_query($qury_sql, $mysite) or die(mysql_error());
	$qury_row=mysql_fetch_assoc($qury_obj);
	$qury_num=mysql_num_rows($qury_obj);
	if($qury_num==0){
		$cont=$user.'用户不存在1....';
    logError1($cont);
		return 0;
	
	}else{
	$cont=$user.'用户存在....';
    logError1($cont);
		return 1;
	}
}


function checktoken_local($user,$token){
	
	$cont=$user.'进入检查用户动态密码...';
    logError1($cont);
	//以下为发送SMS阶段，这里传来的$token 只是个标记，只要获取$user 就可以了
	//用户有四次机会返回争取 1  用户名和OTP正确（且可以登录这个设备） 2  推权的用户OTP正确，且可以登录这个设备   3 用户禁用了OTP直接可以登录  4  短信SMS正确
	if ($token=='sms'){ 
	HLCLOG("checktoken:start send sms password!!!");
	$cont=$user.'发送短信...';
    logError1($cont);
	$c=sms_password($user);
   }	
	//发送结束
	
	//要可以推权
	$result=1;
	
	global $mysite,$_HLCPHP;
	$qury_sql="select * from radius.rad_user where username='".mysql_escape_string($user)."'";
	HLCLOG("sql for check token:\n".$qury_sql);
	$qury_obj=mysql_query($qury_sql, $mysite) or die(mysql_error());
	$qury_row=mysql_fetch_assoc($qury_obj);
	$qury_num=mysql_num_rows($qury_obj);

	
	//<1> 这里是判断他发来的动态令牌跟用户实际的种子产生的令牌是否相符，是的话$result=0;但是还要看有没有权限登录
	if($qury_row['seed']!=''){	
		$seed = $qury_row['seed'];
		$TimeStamp = $_HLCPHP["otp"]::get_timestamp();
		$secretkey = $_HLCPHP["otp"]::base32_decode($seed);
		$realotp   = $_HLCPHP["otp"]::oath_hotp($secretkey, $TimeStamp);
		
		
		HLCLOG("checktoken:".$token."===".$realotp);

		
		if($token==$realotp){
			//return 0;
			$remote_ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'';
			$login_log=mysql_query("insert into zlog_radius.rad_logn(username,user,pass,reply,authdate,date,logtime,nas_port_id,NAS_Identifier,nasip,callingip) value('".$user."','".$user."','*****','Access-Accept',now(),now(),now(),'".$user."','win','".$remote_ip."','win-otp-right'); ", $mysite) or die(mysql_error());
			$result=0;
			$cont=$user.'OTP相符合，返回正确...';
          logError1($cont);
			return(intval($result));
		}else{
			$cont=$user.'OTP不相符合，设置结果是1，但是还要继续...';
          logError1($cont);	
		$remote_ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'';
			//$login_log=mysql_query("insert into zlog_radius.rad_logn(username,user,pass,reply,authdate,date,logtime,nas_port_id,NAS_Identifier,nasip,callingip) value('".$user."','".$user."','*****','Access-Reject',now(),now(),now(),'".$user."','win','".$remote_ip."','win-otp-error".$token.$realotp."'); ", $mysite) or die(mysql_error());
			$result=1;
			//return(intval($result));		
		}
	}
	
	//<2> 如果他自己的没有通过，那就要看有没有存在推权的情况，也就是其他用户的令牌可以关联这个账户 
	if($result!=0){
		$cont=$user.'OTP不相符合，进入推权检测...';
          logError1($cont);
		$useri_sql = "select * from radius.rad_user where UserName in ('".str_replace(",","','",$qury_row['authto'])."')";
		$useri_obj = mysql_query($useri_sql, $mysite) or die(mysql_error());
		while($useri_row = mysql_fetch_assoc($useri_obj)){
			if($useri_row['seed']!=''){	
				$seed = $useri_row['seed'];
				$TimeStamp = $_HLCPHP["otp"]::get_timestamp();
				$secretkey = $_HLCPHP["otp"]::base32_decode($seed);
				$realotp   = $_HLCPHP["otp"]::oath_hotp($secretkey, $TimeStamp);
			
           //下面所有的流程都是基于找到了推权的OTP用户，也就是用户输入的OTP匹配到了一个用户，如果没有匹配到，则往下看SMS或者禁用OTP流程			
			if($token==$realotp){
				$realuser=$useri_row['UserName'];
				HLCLOG("checktoken:tuiquan ligin!!");
			$cont=$user.'OTP不相符合，但是找到了推权的用户...'.$realuser;
          logError1($cont);
			//写到数据库登录日志里		
			$remote_ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'';
			
			//这里是判断用户禁止登录的设备IP里面有没有当前登录的WIN主机IP $remote_ip，禁止列表在radius.radcheck
			$deny_ip_sql="SELECT count(*) as nas FROM radius.radcheck where (username='".$realuser."' and attribute='NAS-IP-Address' and op='!=' and value='".$remote_ip."')";
			$deny_ip=mysql_query($deny_ip_sql, $mysite) or die(mysql_error());
			$deny_ip_num=mysql_fetch_assoc($deny_ip);
			
			if ($deny_ip_num["nas"]==0){	

           HLCLOG("checktoken:tuiquan ligin!!");
				$cont=$user.'推权成功登陆...';	
              logError1($cont);				
			$login_log=mysql_query("insert into zlog_radius.rad_logn(username,user,pass,reply,authdate,date,logtime,nas_port_id,NAS_Identifier,nasip,callingip) value('".$user."','".$user."','******','Access-Accept',now(),now(),now(),'".$realuser."','win','".$remote_ip."','win-tuiquan-right'); ", $mysite) or die(mysql_error());
               	$result=0;
				    return(intval($result));	//如果推权成功，且允许访问这个设备，就直接返回正确
					break;
				}else{
					 HLCLOG("checktoken:tuiquan ligin!!");
				$cont=$user.'推权给了'.$realuser.'虽然令牌正确，但是'.$realuser.'不允许登录这个设备';
            logError1($cont);				
				//虽然令牌正确，但是不允许登录这个设备,限制列表有他的限制名单
				//$login_log=mysql_query("insert into zlog_radius.rad_logn(username,user,pass,reply,authdate,date,logtime,nas_port_id,NAS_Identifier,nasip,callingip) value('".$user."','".$user."','*****','Access-Reject',now(),now(),now(),'".$realuser."','win','".$remote_ip."','win-not-allow'); ", $mysite) or die(mysql_error());
				

               $result=1;
			   	HLCLOG("checktoken:not allow login  this device");
				return(intval($result));	//如果推权成功，但是不允许访问这个设备，就直接返回错误
				break;	
					
				}
				}   //上面的判断都是基于找到了推权的用户，即他输入的OTP属于没有一个允许推权的账户
               				
			}     //这个是如果推权列表的用户，有种子信息的时候，逐个判断
		}   //while循环的结束
	}      //这个是整个推权逻辑的结束
	
	
	//exit(intval($result));
	//如果上面真实对比otp ,推权也不对，这里检查是不是禁用了
	if($result!=0){	
	
	 $cont=$user.'推权也不对，这里检查是不是禁用了双因素即白名单用户...';
    logError1($cont);
	
	HLCLOG("checktoken: start check sms_password!!");
	$mysite= mysql_connect("localhost","root","jbgsn!2716888") or trigger_error(mysql_error(),E_USER_ERROR); 
    mysql_query("SET NAMES 'utf8'");


//首先判断，该用户是不是可以不检查双因素（禁用）的特殊用户，如果是直接方放行
$nas_sql = "SELECT mfa FROM radius.rad_user where username='".$user."'";
$nas_obj = mysql_query($nas_sql, $mysite) or die(mysql_error());
$nas_row = mysql_fetch_assoc($nas_obj);


	
	if ($nas_row['mfa'] != 0) {
		$result=0;
		
		//要写日志,返回信息里标注这个是disable_otp
	   $cont=$user.'用户白名单禁用双因素...';
      logError1($cont);
	  
		$remote_ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'';
		$login_log=mysql_query("insert into zlog_radius.rad_logn(username,user,pass,reply,authdate,date,logtime,nas_port_id,NAS_Identifier,nasip,callingip) value('".$user."','".$user."','*****','Access-Accept',now(),now(),now(),'".$user."','win','".$remote_ip."','win-disable_otp'); ", $mysite) or die(mysql_error());
       
	   return(intval($result));		
       
	}else{
	 $cont=$user.'用户没有禁用双因素...';
      logError1($cont);	
	} 
	}
	
	//这里开始检查是不是短信
if($result!=0){	

	   $cont=$user.'开始检查是不是短信...';
      logError1($cont);
	HLCLOG("checktoken: enable check mfa!");
	
$qury_sql="select * from radius.rad_user where username='".mysql_escape_string($user)."'";
$qury_obj=mysql_query($qury_sql, $mysite) or die(mysql_error());
$qury_row=mysql_fetch_assoc($qury_obj);
$qury_num=mysql_num_rows($qury_obj);

$onepasss=explode('_',$qury_row["one_pass"]);
HLCLOG("checktoken_sms:".$onepasss[0].$onepasss[1]);

//如果短信的基础信息都没有，则不用判断了，直接退出

if(count($onepasss)<2){
		$cont=$user.'onepass数据格式不对，或者没有写入短信内容，直接退出...';
      logError1($cont);
	$result=1;
	HLCLOG("checktoken:radius.rad_user no right sms_password!!");
	
	   //写日志，表示是短信密码错误,这个是最后的失败
	   $remote_ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'';
		$login_log=mysql_query("insert into zlog_radius.rad_logn(username,user,pass,reply,authdate,date,logtime,nas_port_id,NAS_Identifier,nasip,callingip) value('".$user."','".$user."','*****','Access-Reject',now(),now(),now(),'".$user."','win','".$remote_ip."','win-mfa-error'); ", $mysite) or die(mysql_error());
	
	
	return(intval($result));
}

//2020 6月30日 超时时间由数据库里环境变量确定，临时获取
$q="SELECT * FROM system.aaa_envir where en_key='otp_timeout'";
$qury_obj=mysql_query($q, $mysite) or die(mysql_error());
$qury_row=mysql_fetch_assoc($qury_obj);

$t=$qury_row['en_val'];

if(time()>(strtotime($onepasss[0])+$t)){
	$onepasssql="update radius.rad_user set one_pass='' where username='".$user."'";
	mysql_query($onepasssql, $mysite) or die(mysql_error());
    $result=1;	
	HLCLOG("checktoken:sms_password timeout!!".$onepasss[0]);
		   $cont=$user.'短信超时...';
      logError1($cont);
	//写日志，表示是短信密码错误
	   $remote_ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'';
		//$login_log=mysql_query("insert into zlog_radius.rad_logn(username,user,pass,reply,authdate,date,logtime,nas_port_id,NAS_Identifier,nasip,callingip) value('".$user."','".$user."','*****','Access-Reject',now(),now(),now(),'".$user."','win','".$remote_ip."','win-sms-timeout'); ", $mysite) or die(mysql_error());
	
	
	
	//return(intval($result)); 这个时候还不能返回，只能说短信不对
}

if($token!=$onepasss[1]){
	HLCLOG("checktoken:sms pass error:".$token."###".$onepasss[1]);
	$result=1;
	
		   $cont=$user.'短信不对！...';
      logError1($cont);
	//写日志，表示是短信密码错误
	   $remote_ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'';
		//$login_log=mysql_query("insert into zlog_radius.rad_logn(username,user,pass,reply,authdate,date,logtime,nas_port_id,NAS_Identifier,nasip,callingip) value('".$user."','".$user."','*****','Access-Reject',now(),now(),now(),'".$user."','win','".$remote_ip."','win_sms_pass_err'); ", $mysite) or die(mysql_error());
	//return(intval($result));
}elseif(($token!='') and ($onepasss[1]!='') and ($token==$onepasss[1])){
//那就是说到了这里，token和SMS发送的一次密码是对的
	   $cont=$user.'短信验证是对的...';
      logError1($cont);
$result=0;

$remote_ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'';
$login_log=mysql_query("insert into zlog_radius.rad_logn(username,user,pass,reply,authdate,date,logtime,nas_port_id,NAS_Identifier,nasip,callingip) value('".$user."','".$user."','*****','Access-Accept',now(),now(),now(),'".$user."','win','".$remote_ip."','win_sms_right'); ", $mysite) or die(mysql_error());
return(intval($result));	
}else{
   $cont=$user.'短信为空！...';
   logError1($cont);	
}
}
	
	
	
	//如果上面都没有成功，那就是失败，最终的失败，OTP也不对，也不是推权，也不是SMS，也没有列入白名单（禁用双因素检测）
    $remote_ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'';
	if (intval($result) != 0) {
		
	   $cont=$user.'四种机会都不对，OTP，推权，短信，白名单...';
      logError1($cont);
		
	$login_log=mysql_query("insert into zlog_radius.rad_logn(username,user,pass,reply,authdate,date,logtime,nas_port_id,NAS_Identifier,nasip,callingip) value('".$user."','".$user."','*****','Access-Reject',now(),now(),now(),'".$user."','win','".$remote_ip."','win-mfa-final-error'); ", $mysite) or die(mysql_error());
	}
	return(intval($result));//失败
}

function checktoken_proxy($user,$token){
		   $cont=$user.'代理到其他地方验证，比如其他的国密系统...';
      logError1($cont);
	
	//该函数用于处理代理转发到其他radius服务器进行验证OTP，分为原账户名代理验证，和推权代理验证两类情况	
    //对于前者，用户直接输入用户名  密码，系统将转到代理服务器验证，对于后者，需要在密码部分输入user_otppass 这种方式，如 zhangsan_234454
	//系统将对其进行拆分，形成用户名密码后去代理服务器验证，注意，对于推权的验证，还可以设置权限，是否允许zhangsan访问该主机
	//从数据库里获取代理转发过去的IP和密钥KEY
	$mysite= mysql_connect("localhost","root","jbgsn!2716888") or trigger_error(mysql_error(),E_USER_ERROR); 
    mysql_query("SET NAMES 'utf8'");   
	
	$qury_ip="SELECT en_val FROM system.aaa_envir where en_key='radius_proxy_ip'";
	$qury_obj=mysql_query($qury_ip, $mysite) or die(mysql_error());
	$qury_row=mysql_fetch_assoc($qury_obj);
	$proxy_radius_ip=$qury_row["en_val"];
	
	$qury_key="SELECT en_val FROM system.aaa_envir where en_key='radius_proxy_key'";
	$qury_obj=mysql_query($qury_key, $mysite) or die(mysql_error());
	$qury_row=mysql_fetch_assoc($qury_obj);
	$proxy_radius_key=$qury_row["en_val"];
	
	$result=1;
	//return(intval($result));
	
	
	$p=explode('_',$token);
	//如果令牌里不包含#表示他就是个单纯的令牌，直接去验证，用户也是发来的登录用户，否则就是代理加推权
	if (count($p)<2){
	$pass = $token;
exec ("sudo /usr/bin/radtest ".$user." ".$pass." ".$proxy_radius_ip." 0 ".$proxy_radius_key,$a,$b);

if ($b===0){
	    //写日志
		$remote_ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'';
		$login_log=mysql_query("insert into zlog_radius.rad_logn(username,user,pass,reply,authdate,date,logtime,nas_port_id,NAS_Identifier,nasip,callingip) value('".$user."','".$user."','*****','Access-Accept',now(),now(),now(),'".$proxy_radius_ip."','win','".$remote_ip."','proxy_otp_right'); ", $mysite) or die(mysql_error());
	
	
	$result=0;
return(intval($result));	
}else{
	
	
	$remote_ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'';
	$login_log=mysql_query("insert into zlog_radius.rad_logn(username,user,pass,reply,authdate,date,logtime,nas_port_id,NAS_Identifier,nasip,callingip) value('".$user."','".$user."','*****','Access-Reject',now(),now(),now(),'".$proxy_radius_ip."','win','".$remote_ip."','proxy_otp_error'); ", $mysite) or die(mysql_error());
	
	$result=1;
return(intval($result));
}	
	
	
	}elseif (count($p)==2){	
    //这里处理代理+推权的情况
    $p_user=$p[0];  //代理过去的用户
    $p_pass=$p[1];   //代理过去的密码
	$remote_ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'';	
	
	
	//先检查这个$p_user有没有权限访问$remote_ip，没有的话直接退出
	
	$deny_ip_sql="SELECT count(*) as nas FROM radius.radcheck where (username='".$p_user."' and attribute='NAS-IP-Address' and op='!=' and value='".$remote_ip."')";
	$deny_ip=mysql_query($deny_ip_sql, $mysite) or die(mysql_error());
	$deny_ip_num=mysql_fetch_assoc($deny_ip);
			
	if ($deny_ip_num["nas"]>0){		
	$login_log=mysql_query("insert into zlog_radius.rad_logn(username,user,pass,reply,authdate,date,logtime,nas_port_id,NAS_Identifier,nasip,callingip) value('".$user."','".$user."','******','Access-Reject',now(),now(),now(),'".$proxy_radius_ip."_".$p_user."','win','".$remote_ip."','win-not-allow'); ", $mysite) or die(mysql_error());
   $result=1;
	return(intval($result));	//如果不允许访问，那就直接退出了
	}
	
	
		
	exec ("sudo /usr/bin/radtest ".$p_user." ".$p_pass." ".$proxy_radius_ip." 0 ".$proxy_radius_key,$a,$b);
   if ($b===0){
	    //写日志
		$login_log=mysql_query("insert into zlog_radius.rad_logn(username,user,pass,reply,authdate,date,logtime,nas_port_id,NAS_Identifier,nasip,callingip) value('".$user."','".$user."','*****','Access-Accept',now(),now(),now(),'".$proxy_radius_ip."_".$p_user."','win','".$remote_ip."','proxy_tuiquan_right'); ", $mysite) or die(mysql_error());
	
	
	$result=0;
return(intval($result));	
}else{
	
	
	$remote_ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'';
	$login_log=mysql_query("insert into zlog_radius.rad_logn(username,user,pass,reply,authdate,date,logtime,nas_port_id,NAS_Identifier,nasip,callingip) value('".$user."','".$user."','*****','Access-Reject',now(),now(),now(),'".$proxy_radius_ip."_".$p_user."','win','".$remote_ip."','proxy_tuiquan_error'); ", $mysite) or die(mysql_error());
	
	$result=1;
return(intval($result));
		

	}
}
}
/*
//测试数据
$_POST['data']='<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>
<multiOTP version="4.0" xmlns="http://www.sysco.ch/namespaces/multiotp">
<ServerChallenge>dkVPWT8qHzY8MzBmNGoyAT5/IGd8HzIzZWZpaD5sDDUiKW9x</ServerChallenge>
<CheckUserExists>
  <UserId>administrator</UserId>
  <Chap>
      <ChapId>5b</ChapId>
      <ChapChallenge>b2f0f70caa0e312075cb55a426ad21f3</ChapChallenge>
      <ChapPassword>6988ecee6a32ea8fd161baf59833d1d0</ChapPassword>
      <ChapHash>RzgfCy4f</ChapHash>
  </Chap>
  <CacheLevel>1</CacheLevel>
</CheckUserExists>
</multiOTP>';
*/





require_once('libhlc/MultiotpXmlParser.php');

$remote_ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'';
 
HLCLOG(urldecode("request from ".$remote_ip." :\n".file_get_contents("php://input")));

$command_name="";
$error_code=99;
$user_info="";
$cache_data="";
 
$cache_data_template = <<<EOL
      <Cache>
      *UserInCache*</Cache>
EOL;

$user_template = <<<EOL
          <User UserId="*UserId*">
              <UserData>*UserData*</UserData>
          </User>
EOL;

$xml_data = <<<EOL
*XmlVersion*
<multiOTP version="4.0" xmlns="http://www.sysco.ch/namespaces/multiotp">
<DebugCode>*Command*</DebugCode>
<ServerPassword>*ServerPassword*</ServerPassword>
<ErrorCode>*ErrorCode*</ErrorCode>
<ErrorDescription>*ErrorDescription*</ErrorDescription>
*UserInfo**Cache*</multiOTP>
EOL;
$xml_data = str_replace('*XmlVersion*', '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>', $xml_data);
      






//$hlcpost=file_get_contents("php://input");

$xml = new MultiotpXmlParser(isset($_POST['data'])?$_POST['data']:'');
$xml->Parse();

HLCLOG("xml:\n".print_r($xml,true));

$ServerSecret=getprivatekey();

$server_challenge = Hlc_Decrypt('ServerChallenge', (isset($xml->document->serverchallenge[0])?($xml->document->serverchallenge[0]->tagData):''),$ServerSecret);


if(isset($xml->document->checkusertoken[0])) {
	$command_name = "CheckUserToken";
	
	$user_id = (isset($xml->document->checkusertoken[0]->userid[0])?($xml->document->checkusertoken[0]->userid[0]->tagData):'');
	$chap_id = (isset($xml->document->checkusertoken[0]->chap[0]->chapid[0])?($xml->document->checkusertoken[0]->chap[0]->chapid[0]->tagData):'00');
	$chap_challenge = (isset($xml->document->checkusertoken[0]->chap[0]->chapchallenge[0])?($xml->document->checkusertoken[0]->chap[0]->chapchallenge[0]->tagData):'');
	$chap_password = (isset($xml->document->checkusertoken[0]->chap[0]->chappassword[0])?($xml->document->checkusertoken[0]->chap[0]->chappassword[0]->tagData):'');

	$chap_hash = (isset($xml->document->checkusertoken[0]->chap[0]->chaphash[0])?($xml->document->checkusertoken[0]->chap[0]->chaphash[0]->tagData):'');
	if ('' != $chap_hash) {
		$chap_hash = Hlc_Decrypt('ChapHash', $chap_hash, $chap_id.$server_challenge.$chap_id);
	}
	
	

	
	
	
	//根据环境变量的配置，确定到底是本地的认证还是转发到其他的rdius验证
	
	$mysite= mysql_connect("localhost","root","jbgsn!2716888") or trigger_error(mysql_error(),E_USER_ERROR); 
    mysql_query("SET NAMES 'utf8'");   
	
	$qury_proxy="SELECT en_val FROM system.aaa_envir where en_key='windows_radius_proxy'";
	$qury_obj=mysql_query($qury_proxy, $mysite) or die(mysql_error());
	$qury_row=mysql_fetch_assoc($qury_obj);
	$proxy=$qury_row["en_val"];
	
	//定义两个函数处理，一个是本地处理动态令牌的验证，一个是代理到其他地方去处理
	if($proxy['en_val']==0){
	   $cont='本地验证模式...';
      logError1($cont);
	$checktoken='checktoken_local';	
	}else{
	   $cont='代理验证模式...';
      logError1($cont);
	$checktoken='checktoken_proxy';	
	}
	//$checktoken='checktoken_proxy';
	
	if($checktoken($user_id,$chap_hash)==0){
		$error_code=0;
	}else{
		$error_code=99;
	}
	
}elseif (isset($xml->document->readuserdata[0])) {
	$command_name="ReadUserData";
	$user_id = (isset($xml->document->readuserdata[0]->userid[0])?($xml->document->readuserdata[0]->userid[0]->tagData):'');
	$error_code=19;
	$user_data = "algorithm:=Vm96eg==
attributed_tokens:=
autolock_time:=Eg==
cache_level:=EQ==
cache_lifetime:=ETg/ODkbDzw=
challenge_validity:=EA==
create_host:=U3FqcH5+YWx0ayUn
create_time:=ESg8MiMpOyUpKQ==
delta_time:=Fw==
desactivated:=Fw==
description:=
dialin_ip_address:=
dialin_ip_mask:=
email:=
error_counter:=Eg==
group:=
key_id:=
language:=
last_cached_credential:=
last_error:=HjsqJggiEyUtNg==
last_event:=Ajw=
last_failed_credential:=GjV7IgZwBDwnIjgdAnExITMuAjY4ajIjRiMUdmUiBjY1GGJlETBuaA==
last_failed_time:=HjsqJgghAD4uIA==
last_login:=HjsqJwggBTMjKA==
last_login_for_cache:=Hw==
last_success_credential:=Hj0uJAIxFmAlJSBjPjZ3JDdyGiw5Zzd6AyVgEHRhAWdjYHVJQyo4ZA==
last_sync_update:=Hw==
last_sync_update_host:=
last_update:=HjsqJggyETMjMQ==
last_update_host:=XGJ8ZVVuTmB6bDQb
ldap_hash_validity:=Hw==
locked:=Hw==
multi_account:=Hg==
number_of_digits:=Gw==
private_id:=
request_ldap_pwd:=AQ==
request_prefix_pin:=AQ==
sms:=
sms_validity:=AA==
synchronized:=AA==
synchronized_channel:=
synchronized_dn:=
synchronized_server:=
synchronized_time:=AA==
time_interval:=BDU=
token_algo_suite:=f05DQy14ekgk
token_serial:=
user:=
user_last_login:=Byk8IQggCyQ2Gg==
user_principal_name:=
delayed_account:=
delayed_time:=Fw==
delayed_finished:=Fw==";//
	$user_info = str_replace('*UserId*', $user_id, $user_template);
    $user_info = str_replace('*UserData*', $user_data, $user_info);
	
	
}elseif (isset($xml->document->checkuserexists[0])) {
	$command_name="CheckUserExists";
	$user_id = (isset($xml->document->checkuserexists[0]->userid[0])?($xml->document->checkuserexists[0]->userid[0]->tagData):'');
	if(checkexist($user_id)==0){
		$error_code=21;//不存在
	}else{
		$error_code=22;
	}
	
}



//这个主要是客户端那边要验证当前服务器是否合法
$server_password = md5($command_name.$ServerSecret.$server_challenge);

$error_description=ResetErrorsArray($error_code);

$xml_data = str_replace('*Command*', $command_name, $xml_data);
$xml_data = str_replace('*ServerPassword*', $server_password, $xml_data);
$xml_data = str_replace('*ErrorCode*', intval($error_code), $xml_data);
$xml_data = str_replace('*ErrorDescription*', $error_description, $xml_data);
$xml_data = str_replace('*UserInfo*', $user_info, $xml_data);
$xml_data = str_replace('*Cache*', $cache_data, $xml_data);


HLCLOG("response to ".$remote_ip." :\n".$xml_data);

echo $xml_data;
exit();

function Hlc_Decrypt(
      $key,
      $value,
      $encryption_key
){
	$result = '';
	if (strlen($encryption_key) > 0){
		if (0 < strlen($value)){
			$value_to_Hlc_Decrypt = base64_decode($value);
			for ($i=0;  $i < strlen($value_to_Hlc_Decrypt); $i++){
				$encrypt_char = ord(substr($encryption_key,$i % strlen($encryption_key),1));
				$key_char = ord(substr($key,$i % strlen($key),1));
				$result .= chr($encrypt_char^$key_char^ord(substr($value_to_Hlc_Decrypt,$i,1)));
			}
		}
	}else{
		$result = $value;
	}
	return $result;
}




  // Reset the errors array
  function ResetErrorsArray($code)
  {
    $errors_text[0] = "OK: Token accepted";

    $errors_text[9] = "INFO: Access Challenge returned back to the client";
    $errors_text[10] = "INFO: Access Challenge returned back to the client";

    $errors_text[11] = "INFO: User successfully created or updated";
    $errors_text[12] = "INFO: User successfully deleted";
    $errors_text[13] = "INFO: User PIN code successfully changed";
    $errors_text[14] = "INFO: Token has been resynchronized successfully";
    $errors_text[15] = "INFO: Tokens definition file successfully imported";
    $errors_text[16] = "INFO: QRcode successfully created";
    $errors_text[17] = "INFO: UrlLink successfully created";
    $errors_text[18] = "INFO: SMS code request received";
    $errors_text[19] = "INFO: Requested operation successfully done";

    $errors_text[20] = "ERROR: User blacklisted";
    $errors_text[21] = "ERROR: User doesn't exist000";
    $errors_text[22] = "ERROR: User already exists";
    $errors_text[23] = "ERROR: Invalid algorithm";
    $errors_text[24] = "ERROR: User locked (too many tries)";
    $errors_text[25] = "ERROR: User delayed (too many tries, but still a hope in a few minutes)";
    $errors_text[26] = "ERROR: This token has already been used";
    $errors_text[27] = "ERROR: Resynchronization of the token has failed";
    $errors_text[28] = "ERROR: Unable to write the changes in the file";
    $errors_text[29] = "ERROR: Token doesn't exist";

    $errors_text[30] = "ERROR: At least one parameter is missing";
    $errors_text[31] = "ERROR: Tokens definition file doesn't exist";
    $errors_text[32] = "ERROR: Tokens definition file not successfully imported";
    $errors_text[33] = "ERROR: Encryption hash error, encryption key is not matching";
    $errors_text[34] = "ERROR: Linked user doesn't exist";
    $errors_text[35] = "ERROR: User not created";
    $errors_text[36] = "ERROR: Token doesn't exist";
    $errors_text[37] = "ERROR: Token already attributed";
    $errors_text[38] = "ERROR: User is desactivated";
    $errors_text[39] = "ERROR: Requested operation aborted";
   
    $errors_text[40] = "ERROR: SQL query error";
    $errors_text[41] = "ERROR: SQL error";
    $errors_text[42] = "ERROR: They key is not in the table schema";
    $errors_text[43] = "ERROR: SQL entry cannot be updated";

    $errors_text[50] = "ERROR: QRcode not created";
    $errors_text[51] = "ERROR: UrlLink not created (no provisionable client for this protocol)";
    $errors_text[58] = "ERROR: File is missing";
    $errors_text[59] = "ERROR: Bad restore configuration password";

    $errors_text[60] = "ERROR: No information on where to send SMS code";
    $errors_text[61] = "ERROR: SMS code request received, but an error occurred during transmission";
    $errors_text[62] = "ERROR: SMS provider not supported";
    $errors_text[63] = "ERROR: This SMS code has expired";
    $errors_text[64] = "ERROR: Cannot resent an SMS code right now";
    $errors_text[69] = "ERROR: Failed to send email";
    
    $errors_text[70] = "ERROR: Server authentication error";
    $errors_text[71] = "ERROR: Server request is not correctly formatted";
    $errors_text[72] = "ERROR: Server answer is not correctly formatted";
    $errors_text[79] = "ERROR: AD/LDAP connection error";
    
    $errors_text[80] = "ERROR: Server cache error";
    $errors_text[81] = "ERROR: Cache too old for this user, account autolocked";
    $errors_text[82] = "ERROR: User not allowed for this device";
    $errors_text[88] = "ERROR: Device is not defined as a HA slave";
    $errors_text[89] = "ERROR: Device is not defined as a HA master";

    $errors_text[93] = "ERROR: Authentication failed (time based token probably out of sync)";
    $errors_text[94] = "ERROR: API request error";
    $errors_text[95] = "ERROR: API authentication failed";
    $errors_text[96] = "ERROR: Authentication failed (CRC error)";
    $errors_text[97] = "ERROR: Authentication failed (wrong private id)";
    $errors_text[98] = "ERROR: Authentication failed (wrong token length)";
    $errors_text[99] = "ERROR: Authentication failed (and other possible unknown errors)";
	
	return $errors_text[intval($code)];
	
}
 
function logError($content) 
{ 
  $logfile = '/var/log/win_otp'.date('Ymd').'.log'; 
  if(!file_exists(dirname($logfile))) 
  { 
    @File_Util::mkdirr(dirname($logfile)); 
  } 
  error_log(date("[Y-m-d H:i:s]")." -[".$_SERVER['REQUEST_URI']."] :".$content."\n", 3,$logfile); 
}


?>
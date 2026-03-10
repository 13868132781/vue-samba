<?php require_once(dirname(__FILE__).'/include/fun/hlcotp.php'); ?>
<?php require_once(dirname(__FILE__).'/include/fun/mysqli.php'); ?>
<?php
header('HTTP/1.1 200 ok');
echo '{"code":"200","data":"success"}';
echo 'err';
exit(0);

//增加防渗透20240626
$username=$_POST['username'];
$otp=$_POST['password'];
//$ip=explode(':',$_POST["ip"])[0];
//$ip='10.10.10.10';
file_put_contents("/temp/lin_otp",date("Y-m-d H:i:s")."user: ".$username."otp: ".$otp."ip: ".$ip." \n",FILE_APPEND);
function logEntry($message) {
    // 假设日志文件路径为 'access.log'
    $logFile = 'access_otp.log';
    file_put_contents($logFile, date('Y-m-d H:i:s') . $_SERVER['REQUEST_URI'] . ' ' . $message . PHP_EOL, FILE_APPEND | LOCK_EX);
}
logEntry($_POST['token'].'==='.$_POST["ip"].'==='.$_POST['user']);

// 黑名单文件路径
$blacklistFile = 'blacklist_otp.txt';
// 读取黑名单列表
function readBlacklist($blacklistFile) {
    $blacklist = [];
    if (($handle = fopen($blacklistFile, "r")) !== FALSE) {
        while (($ip = fgets($handle)) !== FALSE) {
            $ip = trim($ip); // 移除行尾的换行符
            if (!empty($ip)) {
                $blacklist[] = $ip;
            }
        }
        fclose($handle);
    }
    return $blacklist;
}

// 将IP添加到黑名单
function addToBlacklist($blacklistFile, $ip) {
    file_put_contents($blacklistFile, $ip . PHP_EOL, FILE_APPEND | LOCK_EX);
}

// 检查IP是否在黑名单中
function isIPBlacklisted($blacklist, $ip) {
    return in_array($ip, $blacklist);
}

// 验证URL查询字符串
function isValidQueryString($queryString) {
    // 假设查询字符串是类似 ?c=123456789 的格式
	$patterns = [
    '/[^a-z\/A-Z\\\0-9_\-.@]/',	// 包含大小写数字_-.@四个特殊字符之外的字符,以及SQL语句
    '/\b(SELECT|INSERT|UPDATE|DELETE|DROP|TRUNCATE|EXEC|UNION|FROM|WHERE|ORDER BY|GROUP BY|LIMIT|HAVING|COUNT|DISTINCT|LIKE|IN|BETWEEN|AND|OR)\b/i' 
    ];
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $queryString)) {
			 //echo '匹配到非法字符';
           return false;
        }
    }
    return true;
}




// 读取黑名单
$blacklist = readBlacklist($blacklistFile);

// 获取客户端IP
$clientIP = $ip;

// 检查IP是否在黑名单中
if (isIPBlacklisted($blacklist, $clientIP)) {
    header('HTTP/1.1 403 Forbidden');
    //echo '拒绝访问';
	logEntry("IP {$clientIP} 已被拒绝访问，因为它在黑名单中。");
    exit;
}

// 假设这是请求的URL（这里我们使用$_SERVER['REQUEST_URI']来获取）
$requestURI = $_SERVER['REQUEST_URI'];

// 提取查询字符串部分（如果有的话）
    if (!isValidQueryString($username)) {
        // 如果查询字符串不符合规范，将IP添加到黑名单
		 //echo '--增加黑名单';
        addToBlacklist($blacklistFile, $clientIP);
        header('HTTP/1.1 403 Forbidden');
        //echo '非法访问，IP已加入黑名单';
		logEntry("IP {$clientIP} 尝试非法访问，提交数据有非法字符。已将该IP添加到黑名单。");
        exit;
    }


// 如果IP不在黑名单且URL查询字符串符合规范，则继续处理请求...
logEntry("IP {$clientIP} 请求已验证，继续处理...");

//增加防渗透结束
 

// 测试 curl -X POST http://127.0.0.1:1111/check_otp.php -d user=admin -d token=259967


if (strpos($username,"\\")!=''){
	$u=explode("\\",$username);
	$username=$u[1];	
}


   $user_sql = "select * from radius.rad_user where UserName='".$username."'";
	$user_obj = mysql_query($user_sql, $mysite) or die(mysql_error());
	$user_row = mysql_fetch_assoc($user_obj);
	$user_num = mysql_num_rows($user_obj);
	
	if(intval($user_row["safe_status"])!=0){
	$mfa_type='otp';
	$mfa_result='用户已锁定';
	mysql_query("insert into vpd.owa_auth (user,mfa_type,mfa_result,sms_mobile,mfa_code,ip) values ('".$username."','".$mfa_type."','".$mfa_result."','".$user_row["mobile_nmb"]."','".$otp."','".$ip."')", $mysite) or die(mysql_error());
	echo "reject";
	exit(1);	
}

	//print_r($user_row); 
if(intval($user_row["mfa_owa"])>0){
	$mfa_type='otp';
	$mfa_result='用户白名单otp';
	mysql_query("insert into vpd.owa_auth (user,mfa_type,mfa_result,sms_mobile,mfa_code,ip) values ('".$username."','".$mfa_type."','".$mfa_result."','".$user_row["mobile_nmb"]."','".$otp."','".$ip."')", $mysite) or die(mysql_error());
	echo 'accept';	
	exit(0);	
}

	

	   if($user_row['seed']!=''){	
		$seed = $user_row['seed'];
		//echo $seed;
		$TimeStamp = $_HLCPHP["otp"]::get_timestamp();
		$secretkey = $_HLCPHP["otp"]::base32_decode($seed);
		$realotp   = $_HLCPHP["otp"]::oath_hotp($secretkey, $TimeStamp);
	
		//echo $otp.'--'.$realotp;
		if($otp==$realotp){
	
	$mfa_type='otp';
	$mfa_result='OTP正确';
	mysql_query("insert into vpd.owa_auth (user,mfa_type,mfa_result,sms_mobile,mfa_code,ip) values ('".$username."','".$mfa_type."','".$mfa_result."','".$user_row["mobile_nmb"]."','".$otp."','".$ip."')", $mysite) or die(mysql_error());
	//echo $mfa_result;
	echo 'accept';
   exit(0);	
		 }else{
    $mfa_type='otp';
	$mfa_result='OTP错误';
	mysql_query("insert into vpd.owa_auth (user,mfa_type,mfa_result,sms_mobile,mfa_code,ip) values ('".$username."','".$mfa_type."','".$mfa_result."','".$user_row["mobile_nmb"]."','".$otp."','".$ip."')", $mysite) or die(mysql_error());
   	 echo "reject";
	 exit(1);
		 }
	 }else{
	//这里是种子为空，就是要验证OTP但是没有seed	 
	$mfa_type='otp';
	$mfa_result='OTP没有秘钥';
	mysql_query("insert into vpd.owa_auth (user,mfa_type,mfa_result,sms_mobile,mfa_code,ip) values ('".$username."','".$mfa_type."','".$mfa_result."','".$user_row["mobile_nmb"]."','".$otp."','".$ip."')", $mysite) or die(mysql_error());
	//echo '3';
	echo "reject";
   exit(2);	
	 }

?>

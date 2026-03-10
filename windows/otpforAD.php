<?php require_once(dirname(__FILE__).'/../just/aht/sdOtp.php'); ?>
<?php require_once(dirname(__FILE__).'/libhlc/MultiotpXmlParser.php');?>
<?php
$mysite= mysqli_connect("127.0.0.1","root","jbgsn!2716888");
if (mysqli_connect_errno()){ 
    echo "连接 MySQL 失败: " . mysqli_connect_error(); 
}  
mysqli_query($mysite,"SET NAMES 'utf8'");

function _decrypt_php($val ){
		$key="sd-secret-key";
		$method = "AES-256-CBC";
		$iv = base64_decode("SIm6PCCARjsfnolZ37dd9Q==");
		
		$val = base64_decode($val);
		$val = openssl_decrypt($val, $method, $key, OPENSSL_RAW_DATA, $iv);
		
	   return $val;
}


function ldapGetConn(){
	global $mysite;
	$qury_sql="select * from sdsamba.adserver where as_default='1'";
	$qury_obj=mysqli_query($mysite,$qury_sql) or die(mysqli_error($mysite));
	$dftaw=mysqli_fetch_assoc($qury_obj);
	
	if(!$dftaw){
		return [null,null,null];
	}
		
	$ldap_host = $dftaw['as_url'];
	$domain = $dftaw['as_domain'];
	// 用户的凭证（如果需要进行验证的话）
	$ldap_username = $dftaw['as_user'].'@'.$domain;
	$ldap_password = _decrypt_php($dftaw['as_pass']);
			 
	// 创建一个LDAP连接
	$ldapconn = ldap_connect($ldap_host) or HLCLOG('无法连接到LDAP服务器');
	$timeout_sec = 15; 
	ldap_set_option($ldapconn, LDAP_OPT_TIMELIMIT, $timeout_sec);
	ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
	ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
	ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7); // 可用于调试
	ldap_set_option($ldapconn, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_NEVER);
	putenv('LDAPTLS_REQCERT=never'); // 跳过 SSL 证书验证（不安全）
				
	$ldapbind = ldap_bind($ldapconn, $ldap_username, $ldap_password);
    //$ldapbind = ldap_bind($ldapconn, "CN=Administrator,CN=Users,DC=IBM,DC=COM", $ldap_password);
	if (!$ldapbind) {
       $error = ldap_error($ldapconn); // Retrieve the LDAP error message
       $errno = ldap_errno($ldapconn); // Retrieve the LDAP error code
       HLCLOG("LDAP绑定失败。错误码: $errno,错误信息: $error");
	}
		
	$dn="DC=".str_replace('.',',DC=',$domain);
	return [$ldapconn,$dn,$domain];
}

?>
<?php

function getprivatekey(){
	$remote_ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'';
	return "hong";
}

function checkexist($user){ 

    $cont='检查用户名是否存在....'.$user;
    HLCLOG($cont);
	
	if($user=='administrator'){//multiotp客户端传来的，都是小写
		$user='Administrator';
	}
	
	$ldapobj = ldapGetConn();
	$ldapconn = $ldapobj[0];
	$ldap_dn = $ldapobj[1];
	$mySearch='(name='.$user.')';
	$objectCategory="CN=Person,CN=Schema,CN=Configuration,".$ldap_dn;
	$filter = '(&(objectCategory='.$objectCategory.')'.$mySearch.')';
	$result = ldap_search($ldapconn, $ldap_dn, $filter);
	if(ldap_errno($ldapconn)){
		HLCLOG(ldap_error($ldapconn));
	}
	$entries = ldap_get_entries($ldapconn, $result);
	
	if($entries['count']==0){
		$cont=$user.'用户不存在....';
		HLCLOG($cont);
		return 0;
	
	}else{
		$cont=$user.'用户存在....';
		HLCLOG($cont);
		return 1;
	}
}


function checktoken_local($user,$token){
	
	$cont='进入检查用户动态密码...'.$user.":".$token;
	HLCLOG($cont);
	$code = 1;
	$result = '';
	
	if($user=='administrator'){//multiotp客户端传来的，都是小写
		$user='Administrator';
	}
	
	$ldapobj = ldapGetConn();
	$ldapconn = $ldapobj[0];
	$ldap_dn = $ldapobj[1];
	$mySearch='(name='.$user.')';
	$objectCategory="CN=Person,CN=Schema,CN=Configuration,".$ldap_dn;
	$filter = '(&(objectCategory='.$objectCategory.')'.$mySearch.')';
	$searchresult = ldap_search($ldapconn, $ldap_dn, $filter);
	if(ldap_errno($ldapconn)){
		HLCLOG(ldap_error($ldapconn));
	}
	$entries = ldap_get_entries($ldapconn, $searchresult);
	
	if($entries['count']==0){//用户不存在
		$result = '用户不存在';
		HLCLOG($result);
		$code = 21;
	}else{
		$one = $entries[0];
		if(!isset($one['telexnumber'][0])){
			$result = '未设置telexnumber';
			HLCLOG($result);
			$code = 99;//未设置seed
		}else{
			$seed = $one['telexnumber'][0];
			$readpass = sdOtpOnePassGet($seed,60);
			if($readpass!=$token){
				$result = '一次口令错误：'.$token.":".$readpass;
				HLCLOG($result);
				$code = 99;
			}else{
				HLCLOG('一次口令正确');
				$code = 0;
			}
		}
	}
	
	$remote_ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'';
	global $mysite;
	$qury_sql="insert into sdsamba_log.adloginotp(ao_time,ao_user,ao_ip,ao_code,ao_result) values(now(),'".$user."','".$remote_ip."','".$code."','".$result."')";
	mysqli_query($mysite,$qury_sql) or HLCLOG(mysqli_error($mysite));
	
	return(intval($code));
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




HLCLOG('start....');

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
      

HLCLOG("post data:\n".$_POST['data']."\n");

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
	
	$error_code = checktoken_local($user_id,$chap_hash);
	
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
function ResetErrorsArray($code){
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
 


function HLCLOG($content){ 
	$logfile = '/temp/win_otp_'.date('Ymd').'.log'; 
	if(!file_exists(dirname($logfile))) 
	{ 
		@File_Util::mkdirr(dirname($logfile)); 
	}
	$backtrace = debug_backtrace();
	$data = date("[Y-m-d H:i:s]")." -[第".$backtrace[0]['line']."行] :".$content."\n";
	
	// 打开文件（如果不存在则创建）
	$file = fopen($logfile, "a") or die("Unable to open file!");
	fwrite($file, $data);
	fclose($file);
}


?>
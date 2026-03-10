<?php
/**
 * 大汉三通短信云平台http接入实例：json格式
 *
 */
//Base URL
define ( 'URL', "http://www.dh3t.com/json/sms" );
//账号，必填
define ( 'ACCOUNT', "dh01888" );
//密码，必填
define ( 'PASSWORD', md5("x8qPGuoN") );
//短信签名，必填
define ( 'SIGN', "永赢基金管理有限公司-永赢内部通知" );
//短信子码,选填
define ( 'SUBCODE', "853101" );

$ret = sendSms ( "13868132781", "您的验证码是:1234", uniqid ( rand (), true ), date("h:i:s"));
$ret = getSmsReport ();


/**
 * 发送短信
 * @param string $phones 手机号码,
 * @param string $content 短信内容
 * @param string $msgid 短信ID(唯一，UUID)，可空
 * @param string $sendtime 短信发送时间，可空
 * 
 */
function sendSms($phones, $content, $msgid, $sendtime) {
	// 发送数据包json格式：{"account":"8528","password":"e717ebfd5271ea4a98bd38653c01113d","msgid":"2c92825934837c4d0134837dcba00150","phones":"15711666132","content":"您好，您的手机验证码为：430237。","sign":"【8528】","subcode":"8528","sendtime":"201405051230"}
	$data = array ('account' => ACCOUNT, 'password' => PASSWORD, 'msgid' => $msgid, 'phones' => $phones, 'content' => $content, 'sign' => SIGN, 'subcode' => SUBCODE, 'sendtime' => $sendtime );
	return http_post_json ( __FUNCTION__, URL . "/Submit", json_encode ( $data ) );
}

/**
 * 获取短信状态报告
 * 
 */
function getSmsReport() {
	// 获取短信状态报告数据包json格式：{"account":"8528","password":"e717ebfd5271ea4a98bd38653c01113d"}
	$data = array ('account' => ACCOUNT, 'password' => PASSWORD );
	return http_post_json ( __FUNCTION__, URL . "/Report", json_encode ( $data ) );
}
/**
 * 获取手机回复的上行短信
 * 
 */
function getSms() {
	// 获取上行数据包json格式：{"account":"8528","password":"e717ebfd5271ea4a98bd38653c01113d"}
	$data = array ('account' => ACCOUNT, 'password' => PASSWORD );
	return http_post_json ( __FUNCTION__, URL . "/Deliver", json_encode ( $data ) );
}

/**
 * PHP发送Json对象数据, 发送HTTP请求
 *
 * @param string $url 请求地址
 * @param array $data 发送数据
 * @return String
 */
function http_post_json($functionName, $url, $data) {
	$ch = curl_init ( $url );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	curl_setopt ( $ch, CURLOPT_FRESH_CONNECT, 1 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_FORBID_REUSE, 1 );
	curl_setopt ( $ch, CURLOPT_TIMEOUT, 30 );
	curl_setopt ( $ch, CURLOPT_HTTPHEADER, array ('Content-Type: application/json; charset=utf-8', 'Content-Length: ' . strlen ( $data ) ) );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
	$ret = curl_exec ( $ch );
	echo $functionName . " : Request Info : url: " . $url . " ,send data: " . $data . "  \n";
	echo $functionName . " : Respnse Info : " . $ret . "  \n";
	curl_close ( $ch );
	return $ret;
}
?>
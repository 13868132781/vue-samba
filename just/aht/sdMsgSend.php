<?php // require_once(dirname(__FILE__).'/api.php'); ?>
<?php

function sdMsgSend($call,$msg,$way=0){
	//return msgsend_aly($call,$msg);
	if ($way=='2'){
		return msgsend_mail($call,'OTP-PASSCODE',$msg);
	}elseif ($way=='0'){
		return msgsend_aly($call,$msg);	
	}else{
		//return msgsend_aly('13868132781',$msg);
		return msgsend_ejdx($call,$msg);
	}
}

//绍兴烟草
function msgsend_sxyc($call,$msg){
	
	$mssqldb=mssql_connect('10.46.0.203','openmas888','grgzpt');
	if($mssqldb){
		if(mssql_query("INSERT INTO [OpenMas].[dbo].[COM_SmsSent_888] ([MessageID] ,[MessageContent] ,[ExtendCode] ,[DestinationAddress],[SendType] ,[SendTime] ,[IsWapPush] ,[WapUrl] ,[CreateTime]) VALUES('9090','".iconv("utf-8","gb2312//IGNORE",$msg)."' ,'111' ,'".$call."' ,'0' ,getdate() ,0 ,null ,getdate())",$mssqldb) or dir(mysql_error())){
		//if(mssql_rows_affected($msgsenddb)>0){//该函数获取不到
			mssql_close($msgsenddb);
			return 0;
		}else{
			mssql_close($msgsenddb);
			return 2;
		}
	}else{
		return 1;
	}	
}

//路桥财政 
function msgsend_lqcz($call,$msg){
	$msgsenddb=mysql_connect('192.168.99.139','monitor','monitor');
	if($msgsenddb){
		mysql_query("insert mas.api_mt_001(mobiles,content) values('".$call."','".iconv("utf-8","gb2312//IGNORE",$msg)."')",$msgsenddb);
		if(mysql_affected_rows($msgsenddb)>0){
			mysql_close($msgsenddb);
			return 0;
		}else{
			mysql_close($msgsenddb);
			return 2;
		}
	}else{
		return 1;
	}
}



//舟山烟草
function msgsend_zsyc($call,$msg){
	$msgsenddb=mysql_connect("10.46.168.8", "zsyc", "zsycyxzx1234");
	if($msgsenddb){
		mysql_query("insert mas.api_mt_zsycxxzx(SM_ID,SRC_ID,MOBILES,CONTENT) values(0,0,'".$call."','".iconv("utf-8","gb2312//IGNORE",$msg)."')",$msgsenddb);
		if(mysql_affected_rows($msgsenddb)>0){
			mysql_close($msgsenddb);
			return 0;
		}else{
			mysql_close($msgsenddb);
			return 2;
		}
	}else{
		return 1;
	}
}


//阿里云短信平台
function msgsend_aly($call,$msg){
	
	//echo $call.$msg;
	$output=[];
	$code=0;
	exec('curl --connect-timeout 10 -i -k --get --include "http://smsmsgs.market.alicloudapi.com/smsmsgs?param='.urlencode($msg).'&phone='.$call.'&skin=900564&sign=500375"  -H \'Authorization:APPCODE e05a4656d8314321927f7bd69298ca81\' 2>&1',$output,$code);
	//echo 'curl -i -k --get --include "http://smsmsgs.market.alicloudapi.com/smsmsgs?param='.urlencode($msg).'&phone='.$call.'&skin=900564&sign=500375"  -H \'Authorization:APPCODE e05a4656d8314321927f7bd69298ca81\' > /dev/null 2>&1';
	//print_r($output);
	
	$output = join("=+=",$output);
	if(strstr($output,'"Code":"OK"')){
		return '';
	}else{
		if(strstr($output,'"Message":"')){
			return explode('","',explode('"Message":"',$output)[1])[0];
		}else if(strstr($output,'HTTP/')){
			return explode('=+=','HTTP/'.explode('HTTP/',$output)[1])[0];
		}else{
			return $output;
		}
	}
}

//某家的
function msgsend_temp($call,$msg){
	$client = new SoapClient("http://192.26.28.172:8118/smspf-ws/services/WebServiceEntry?wsdl");
	$client->soap_defencoding = 'utf-8';
	$client->decode_utf8 = false;
	$client->xml_encoding = 'utf-8';
	$param=array('arg0' => '
	<request>
		<user>XXXX</user>
		<password>XXXX</password>
		<phone>'.$call.'</phone>
		<text>'.$msg.'</text>
	</request>');
	$res = $client->__soapCall('sendMsg',$param);
	if(strstr($res->return,'<code>200</code>')){
		return 0;
	}else{
		return 1;
	}
}



//卫生厅的微信接口
function msgsend_wstwx($call,$msg){
	$client = new SoapClient("http://192.26.31.95:8080/sysManager/SysService?wsdl");
	$client->soap_defencoding = 'utf-8';
	$client->decode_utf8 = false;
	$client->xml_encoding = 'utf-8';
	$param=array('arg0' => '<?xml version="1.0" encoding="UTF-8"?>
		<request>
			<orgCode>3301060000000000000001</orgCode>
			<sysCode>GSBEMAIL</sysCode> 
			<email>'.$call.'</email> 
			<text>'.$msg.'</text> 
		</request>');
	$res = $client->__soapCall('sendWxMessageByEmail',$param);
	if(strstr($res->return ,'<code>200</code>')){
		return 0;
	}else{
		//如果
		return $res;
	}
}


//卫生厅的微信接口,实际在运行的是这个版本，暂时没有判断返回
function msgsend_wstwx_real($call,$msg){
	//设置超时时间
	try{
	ini_set('default_socket_timeout', 4);
	$client = new SoapClient("http://192.26.31.95:8080/sysManager/SysService?wsdl");
	$client->soap_defencoding = 'utf-8';
	$client->decode_utf8 = false;
	$client->xml_encoding = 'utf-8';
	$param=array('arg0' => '<request>		
			<orgCode>3301060000000000000001</orgCode>
			<sysCode>GSBEMAIL</sysCode> 
			<email>'.$call.'</email> 
			<text>'.$msg.'</text> 
		</request>');			
	$res = $client->__soapCall('sendWxMessageByEmail',$param);
	} catch (SoapFault $e) {
    $res='send sms timeout! ';
   } catch (Exception $e) {
    $res='exception won';
   } catch (ErrorException $e) {
    $res='error exception also doesnt error';
   } finally{
	 if(strstr($res,'<code>200</code>')){
		return 0;
	}else{
		return $res;
	}
   }
	
	/*
	if(strstr($res,'<code>200</code>')){
		return 0;
	}else{
		return $res;
	}
	*/
}

//鹿城农信 
function msgsend_lcnx($call,$msg){
	$msg=urlencode("您好，本次虚拟桌面登录OTP验证码为：".$msg."，有效期2分钟。");
	$url="http://msg.ops.local/?mobiles=".$call."&content=".$msg."&key=6f073d94b2aef12873b30123b0221d6c&&encode=UTF-8";
	//$url="https://www.runoob.com/php/php-ref-curl.html";
	//$headerArray =array("Content-type:application/json;","Accept:application/json");
    $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//curl_setopt($ch,CURLOPT_HTTPHEADER,$headerArray);
	$output = curl_exec($ch);
	curl_close($ch);
	//echo $output;
	
	if(strstr($output,'success')){
		return 0;
	}else{
		return 1;
	}
	
	//$output = json_decode($output,true);
	//return $output;
}

	//use PHPMailer\PHPMailer\PHPMailer;
	//use PHPMailer\PHPMailer\SMTP;
	//use PHPMailer\PHPMailer\Exception;
	
//msgsend_mail('1615249035@qq.com','标题','<img border="0" id="showqrcode" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAOQAAADkAQMAAACymRrOAAAABlBMVEX///8AAABVwtN+AAAACXBIWXMAAA7EAAAOxAGVKw4bAAACXklEQVRYhe2YUW4DMQhEuQH3vyU3oLzB6zTq9+QjqlslKz9LpQYP4434H18+uruyumq++cyZKybNdD6rIjObNQmfRZr30nmep0GzpFjQ+voEnYnuBGYQXnyITkzzHeQhWvvzAcrv7MFEwxLiq3jLkYdSYH/GW016KCN7VrElJ7oX8VGKbHKuBMxeTEY2WDe91TaxNKtmwYZoppPrOWIT0+zJHO2k7urZECuNU2ocL1WDCt9MkwiaHVFI89BSGDedP7+pT82QfpifIt6ExM8qzIzMcNOSknG2JhbEu/cIuCndSvuQmwFV/CMqPqr/O0PqzdHmyE2QR0V9FMmUjsEqt/xoG2a6+zFnK56mRZAn+UaKcCMoLUNCfLB8Zd9FkW4cGIZkrdjrkBlprn5t0ygpOavDTXXKYgVNNZBbDGWmqrJWs5LTTJrHLToj3U6hHQmxJ04vFag9bX3i+5UFG+VEEwk1fualo2WmVBvuAzuwbmibpJvi+CTcUetGSEbdmH0UJ4J6SkCJT84g3LQVVh8Xlnvu+roCG92r0rq+loKG6iHMlOdV7N7K183xcSM+ClsfFOrLuk9E2KkKTTYXBX8k/LlN+GhLRTVDf261jWuDfFT3Fm5Qldsv3qvOR2nO6owyIb1VV8+dzkd75fOkXW7kV9XZaGsDYq9utGhC6mo33cqTH6DeQ+7+ugIfVXkrKr1uUnBV9zZho3VecO2tUdfGtZxuurtA0bH5eVznR2jvdVGVvw7oSZGXrnwiKnotkNcUOOkxXuc9hBKS97boo1t1uK7UEQu16KuxNvo/vnj8AOppknOjUFPbAAAAAElFTkSuQmCC" style="">');
function msgsend_mail($address,$title,$msg){
	require_once(dirname(__FILE__)."/../tools/mail/phpmailer/Exception.php");
	require_once(dirname(__FILE__)."/../tools/mail/phpmailer/PHPMailer.php");
	require_once(dirname(__FILE__)."/../tools/mail/phpmailer/SMTP.php");
		
		
		
    //获取系统里配置的邮件发送的参数
	
	
   $mysite= mysql_connect("localhost","root","jbgsn!2716888") or trigger_error(mysql_error(),E_USER_ERROR); 
   mysql_query("SET NAMES 'utf8'");

   $smtp0 = "SELECT val FROM system.aaa_outway where type='mail'"; 
   $smtp1 = mysql_query($smtp0, $mysite) or die(mysql_error());
   $row_smtp = mysql_fetch_assoc($smtp1);
   $smtp_val=$row_smtp['val'];

   $s0=explode("|",$smtp_val);
   $mail_server=explode("=",$s0[0]);
   $mail_user=explode("=",$s0[1]);
   $mail_pass=explode("=",$s0[2]);

   //echo $mail_server[2];
   //echo $mail_user[2];
   //echo $mail_pass[2];
    $smtp_server=$mail_server[2];
    $smtp_user=$mail_user[2];
    $smtp_pass=$mail_pass[2];
	
	
	$mail = new PHPMailer\PHPMailer\PHPMailer(); //建立邮件发送类
	 
	$mail->IsSMTP(); // 使用SMTP方式发送
	
	//$mail->Host = "smtp.softdomain.com.cn"; 
	$mail->Host = $smtp_server;
	
	$mail->SMTPAuth = true; // 启用SMTP验证功能
	
	//$mail->Username = "miaowang@softdomain.com.cn";
    $mail->Username = $smtp_user;
	
	//$mail->Password = 'qqq000,,,Aa'; // 
	$mail->Password = $smtp_pass;
	
	//$mail->SMTPSecure = "ssl";
	//$mail->Port = 465;
	//$mail->SMTPDebug = 10;
	$mail->SMTPOptions = array(
		'ssl' => array(
			'verify_peer' => false,
			'verify_peer_name' => false,
			'allow_self_signed' => true,
		)
	);

	$mail->From = $smtp_user; //邮件发送者email地址
	//$mail->From = "miaowang@softdomain.com.cn";
	$mail->FromName = "OTP-PASSCODE";
	$mail->AddAddress($address, "");//收件人地址，可以替换成任何想要接收邮件的email信箱,格式是AddAddress("收件人email","收件人姓名")
	//$mail->AddReplyTo("", "");

	//$mail->AddAttachment("/var/tmp/file.tar.gz"); // 添加附件
	$mail->IsHTML(true); // set email format to HTML //是否使用HTML格式
	$mail->CharSet='UTF-8';
	$mail->Subject = $title; //邮件标题
	$mail->Body = $msg; //邮件内容
	//$mail->AltBody = "This is the body in plain text for non-HTML mail clients"; 

	if(!$mail->Send())
	{
		//echo "email send failed. <p>\r\n";
		//echo "err: " . $mail->ErrorInfo;
		return 1;
	}else{
		//echo "email send success";
		return 0;
	}
}

function msgsend_ejdx($call,$msg){
	$url="http://服务器地址/Com/SmsInterface/DXBZJK.cfm?LX=0&DLZH=admin&
DLMM=admin&SJHM=".$call."&DXNR=".urlencode($msg)."&FHLS=0";
	//$url="https://www.runoob.com/php/php-ref-curl.html";
	//$headerArray =array("Content-type:application/json;","Accept:application/json");
    $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//curl_setopt($ch,CURLOPT_HTTPHEADER,$headerArray);
	$output = curl_exec($ch);
	curl_close($ch);
	//echo $output;
	
	return $output;
	
}



?>


function checksaf(){

	try{
		 var obj = new ActiveXObject("WScript.Shell");
		  
	}catch(e){
		     alert("为保证您正常使用绍兴烟草IT运维审计系统，请您按照以下指导设置您的浏览器参数");
		     location = "tool_readme/pagesafe.php";
	}

}


function checkclt(){
	return 0;//该检测废弃
	var port=window.location.port?window.location.port:'443';

	var versionStr= getCookie("clientversion"+port);

	var url="../sys_software/down_soft.php?file_dir=/home/softdomain/software&filename=sdbljclient_";
		
	try{ 		
			var obj = new ActiveXObject("WScript.Shell");
			var str="HKEY_CLASSES_ROOT\\softdomain\\version";	  
			var sNic = obj.RegRead(str);
	        if(sNic!=null){
				if(sNic<versionStr){
				alert("系统检测到您安装的客户端版本过低，请您重新下载安装");
				<!--location="../sys_software/list_soft/down_soft.php?file_dir=/home/softdomain/software&filename=sdbljclient.zip";-->
				location=url+versionStr+".exe";
				return 3;		 		
			       }else{
					    return 0;
				       }
			}
	}catch(e){
			if(confirm("系统检测到您未安装户端，或者客户端损坏，请下载安装\n若已安装，请点击取消，并重启浏览器，再登录！")){
				location=url+versionStr+".exe";
				//location = "tool_readme/pagesafe.php";
			}else{
				location="error.php";
			}
			return 1;		
	}

}

function getCookie(c_name){
if(document.cookie.length>0){
    c_start=document.cookie.indexOf(c_name + "=");
    if(c_start!=-1){ 
       c_start=c_start + c_name.length+1; 
       c_end=document.cookie.indexOf(";",c_start);
       if(c_end==-1) 
	       c_end=document.cookie.length;
       return unescape(document.cookie.substring(c_start,c_end));
    }
}
return ""
}
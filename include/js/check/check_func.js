//检测函数  密码、邮箱、电话、数字


//检测是否是强口令
function checkPass(pass){
   if(pass.length < 8){   
             return 0;   
   }   
   var ls = 0;   
   if(pass.match(/([a-z])+/)){   
      ls++;   
   }   
   if(pass.match(/([0-9])+/)){   
      ls++;     
   }    
   if(pass.match(/([A-Z])+/)){         
      ls++;   
   }   
   if(pass.match(/[^a-zA-Z0-9]+/)){   
      ls++;   
   }   
   return ls;   
}   



function checkpwd(pass,tag){
   var tags=tag.split(":");
   
   var ask="密码要求长度大于"+tags[0];
   if(tags[1]!='0'){
	   ask+=",小写";
   }
   if(tags[2]!='0'){
	   ask+=",大写";
   }
   if(tags[3]!='0'){
	   ask+=",数字";
   }
   if(tags[4]!='0'){
	   ask+=",特殊字符";
   }
   if(pass==''){
	   return ask;
   }else{
		ask="("+ask+")";
   }
   if(pass.length < tags[0]){   
      return '长度不够'+ask;   
   }     
   if( tags[1]!='0' && !pass.match(/([a-z])+/)){   //小写
      return '里没有小写'+ask;  
   }     
   if( tags[2]!='0' && !pass.match(/([A-Z])+/)){      //大写
      return '里没有大写'+ask;   
   }
   if( tags[3]!='0' && !pass.match(/([0-9])+/)){   //数字
      return '里没有数字'+ask;     
   }
   if( tags[4]!='0' && !pass.match(/[^a-zA-Z0-9]+/)){   //特殊字符
      return '里没有符号'+ask;  
   }   
   return 1;   
}



//检测是否是邮箱
function isEmail(val) {
var   strEmail=val; 
var exp = /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/;
var reg=strEmail.match(exp);
          var   ErrMsg="你输入的是一个非法的邮件地址！\n"           
          var   Msg="你输入的是一个合法的邮件地址！"           
          if(reg==null)   
          {   
                  alert(ErrMsg);
				  regForm.email.focus();
				  return false;
          }   
          else   
          {   
				  return true;
          }   
}

//检测电话号码
function istel(val) {
var   strtel=val; 
var exp = /^((\(\d{3}\))|(\d{3}\-))?13\d{9}$/;
var reg=strtel.match(exp);
          var   ErrMsg="手机号码输入有误！\n"           
          if(reg==null)   
          {   
                  alert(ErrMsg);
				  regForm.tel.focus();
				  return false;
          }   
          else   
          {   
				  return true;
          }   
}
//检测键入的是否是纯数字
function onlyNum()
{
if(!((event.keyCode>=48&&event.keyCode<=57)||(event.keyCode>=96&&event.keyCode<=105)))
event.returnvalue=false;
}

//检查ip地址是否合法
function   checkIP(val)   
{   
  var   sIPAddress=val;         
  var   exp=/^(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$/;   
          var   reg   =   sIPAddress.match(exp);   
          var   ErrMsg="你输入的是一个非法的IP地址段！\nIP段为：:xxx.xxx.xxx.xxx（xxx为0-255)！"           
          var   Msg="你输入的是一个合法的IP地址段！"           
          if(reg==null)   
          {   
		  		  document.regForm.nasname.focus();
				  document.regForm.nasname.select();
                  alert(ErrMsg);
				  return false;
          }   
          else   
          {   
                  return true;  
          }   
  }   
  
//检查下拉框是否有大于0的值
function checkselect(id,tag){
   var obj=document.getElementById(id);
   if(obj.length==0){
      return false;
   }
   if(obj.value==""){
     return false;
   }
   
   if(tag){
      if(obj.value < tag){
        return false;
      }
   }
   return true;
}
function createXMLHttpRequest(){
 try
    {
   // Firefox, Opera 8.0+, Safari
    var xmlHttp=new XMLHttpRequest();
    }
 catch (e)
    {
  // Internet Explorer
   try
      {
      var xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
      }
   catch (e)
      {
      try
         {
         var xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
         }
      catch (e)
         {
         alert("您的浏览器不支持AJAX！");
         return false;
         }
      }
    }
	return xmlHttp;
}

function sendRequest(url,func,arr){ 
      var xmlhttp;
      if(xmlhttp=createXMLHttpRequest()){ 
 	  xmlhttp.onreadystatechange = function(){
		  if(xmlhttp.readyState==4 && xmlhttp.status==200) 
          {
			  if (func){
				 if(arr){
                    func(xmlhttp.responseText,arr);
				 }else{
					func(xmlhttp.responseText);
				 }
			  }
		  }
	  };
	  xmlhttp.open('GET',url,true); 
	  xmlhttp.setRequestHeader("If-Modified-Since","0");
	  xmlhttp.send(null); 
	  }
} 




function sendRequestex(args){ 
	var url=args['url'];
	var func=args['seccess'];
	var error=args['error'];
    var xmlhttp;
    if(xmlhttp=createXMLHttpRequest()){ 
		xmlhttp.onreadystatechange = function(){
			if(xmlhttp.readyState==4){//这是xmlhttp处理流程各个阶段的状态
				if(xmlhttp.status==200){//这是服务器返回的状态码
					if (func){
						func(xmlhttp.responseText);	
					}
				}else{
					if (error){
						error(xmlhttp);
					}
				}
			}
		};
		xmlhttp.open('GET',url,true); 
		xmlhttp.setRequestHeader("If-Modified-Since","0");
		xmlhttp.send(null); 
	}
} 


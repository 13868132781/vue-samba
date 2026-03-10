export default{
	
	ajax:function(args){
		if(!args.silent){
			hlc.$emit('sdloadingwait',true);
		}
		
		var doTimeout=false;
		if(args.post && args.post.isPollingRequest){//轮询请求，要超时限定
			doTimeout=true;
		}
		
		var url = hlc.entry+"?router="+args.router;
		var post = args.post||{};
		var hsOk = args.ok;
		var hsErr = args.err;
		
		const timeout = 30000; // 设置默认超时时间为20s
		const controller = new AbortController();
		setTimeout(() => controller.abort('timeout'), timeout);
		//fetch默认用浏览器设定的超时时间
		
		fetch(url,{
			method: 'POST',
			body: args.upload?args.upload:JSON.stringify(post),
			signal: (doTimeout?controller.signal:null)
		
		}).then(response => {
			if(!args.silent){
				hlc.$emit('sdloadingwait',false);
			}
			if (!response.ok){
				//抛出错误，会中断then链，直接跳到catch
				throw new Error("request status: "+response.status); 
			}
			hlc.$emit("onSdNetError",false);
			return response.text();
		
		}).then(resText => {
			var fgfbegin = '<fengexiancongzhelikais>';
			var fgfend = '</fengexiancongzhelikais>';
			if(resText.indexOf(fgfbegin)!=-1){
				var resTexts=resText.split(fgfbegin);
				resText = "";
				var sdalertcontent=[];
				for(var i =0;i< resTexts.length;i++){
					var resTexto = resTexts[i];
					if(resTexto.indexOf(fgfend)!=-1){
						var restemp = resTexto.split(fgfend);
						//alert(restemp[0]);
						sdalertcontent.push(restemp[0]);
						resText += restemp[1];
					}else{
						resText += resTexto;
					}
				}
				//显示sdAlert内容
				for(var j=0;j<sdalertcontent.length;j++){
					var thisindex = sdalertcontent.length-1-j;
					var alertText = sdalertcontent[thisindex];
					var alertTitle = "";
					if(alertText.indexOf("<fengexiancongtitle>")!=-1){
						var alertfenbt = alertText.split("<fengexiancongtitle>");
						alertTitle = alertfenbt[0];
						alertText = alertfenbt[1];
					}
					hlc.popup.open({
						name: "sdAlert信息，第"+(thisindex+1)+"条。"+alertTitle,
						width:(80-j*5)+'%',
						height:(80-j*5)+'%',
						text: alertText,
					});
				}
			}
			
			try{
				var jsonstr = JSON.parse(resText);
			}catch(e){
				//alert(JSON.stringify(args)+"\n响应文本："+resText);
				hlc.popup.open({
					name: "分解响应的JSON数据时出错",
					width:'85%',
					height:'85%',
					text:"请求参数："+JSON.stringify(args),
					html: resText,
				});
				return;							
			}
			if(jsonstr.code==-1){
				window.location.reload();
			}
			
			if(jsonstr.msg){
				hlc.$emit("onSdReqMsg",jsonstr);
			}
			
			if(jsonstr.js){
				eval(jsonstr.js);
			}
					
			if (hsOk){
				hsOk(jsonstr);	
			}
		
		}).catch(error => {// 处理错误
			if(!args.silent){
				hlc.$emit('sdloadingwait',false);
			}
			
			var msg = JSON.stringify(args)+"\n error name:"+error.name+".  msg:"+error.message;
			
			hlc.$emit("onSdNetError", msg);
			
		}).finally(()=>{
			//console.log("End");
		});;
	}
}

/*
fetch默认发送json类型数据，php的$_POST只识别urlencoded或formData
可设置fetch发送formData
const data = new FormData();
data.append('key1', 'value1');
data.append('key2', 'value2');

fetch('/your-endpoint', {
	method: 'POST',
	body: data
})
		
或fetch发送urlencoded
		
const data = new URLSearchParams();
data.append('key1', 'value1');
data.append('key2', 'value2');

fetch('/your-endpoint', {
	method: 'POST',
	headers: {
		'Content-Type': 'application/x-www-form-urlencoded'
	},
	body: data
})

不会我们的post数据可能结构复杂，json层级深，所以不转换，依旧用json
formData条目里存不了{}类型，必须转为string
urlencoded更是只能string
*/















/*
//老的方式
function createXMLHttpRequest(){
   var xmlHttp=null;
    try{
     // Firefox, Opera 8.0+, Safari
        xmlHttp=new window.XMLHttpRequest();
     }catch (e){
         // Internet Explorer
         try{
             xmlHttp=new window.ActiveXObject("Msxml2.XMLHTTP");
         }catch (e){
             try{
                 xmlHttp=new window.ActiveXObject("Microsoft.XMLHTTP");
             }catch (e){
                 alert("您的浏览器不支持AJAX！");
                 return false;
             }
         }
     }
     return xmlHttp;
}



export default{

	ajaxOld:function(args){
		if(!args.silent){
			hlc.$emit('sdloadingwait',true);
		}
		
		var url = hlc.entry+"?router="+args.router;
		var data = args.post||{};
		var func = args.ok;
		var error = args.err;
		var xmlhttp=createXMLHttpRequest();
		if(!xmlhttp){ 
			return;
		}
		//这条设置决定了php如何解析post数据
		//默认以&连接串的格式解析
		//但我们是把post body作为一个字符串对待，在两端手动去编解码的
		//xmlhttp.setRequestHeader('content-type', 'application/json');
		
		xmlhttp.onreadystatechange = function(){//每次状态改变都会触发
			if(xmlhttp.readyState!=4){//这是xmlhttp处理流程各个阶段的状态
				return;
			}
			if(!args.silent){
				hlc.$emit('sdloadingwait',false);
			}
			if(xmlhttp.status!=200){//这是服务器返回的状态码
				//alert(JSON.stringify(args)+"\n状态"+xmlhttp.status+"："+JSON.stringify(xmlhttp));
				hlc.$emit("onSdNetError",JSON.stringify(args)+"\n状态"+xmlhttp.status+"："+JSON.stringify(xmlhttp));
				return;
			}
			hlc.$emit("onSdNetError",false);
			
			//分离出sdAlert函数的内容
			var resText = xmlhttp.responseText;
			var fgfbegin = '<fengexiancongzhelikais>';
			var fgfend = '</fengexiancongzhelikais>';
			if(resText.indexOf(fgfbegin)!=-1){
				var resTexts=resText.split(fgfbegin);
				resText = "";
				var sdalertcontent=[];
				for(var i =0;i< resTexts.length;i++){
					var resTexto = resTexts[i];
					if(resTexto.indexOf(fgfend)!=-1){
						var restemp = resTexto.split(fgfend);
						//alert(restemp[0]);
						sdalertcontent.push(restemp[0]);
						resText += restemp[1];
					}else{
						resText += resTexto;
					}
				}
				//显示sdAlert内容
				for(var j=0;j<sdalertcontent.length;j++){
					var thisindex = sdalertcontent.length-1-j;
					var alertText = sdalertcontent[thisindex];
					var alertTitle = "";
					if(alertText.indexOf("<fengexiancongtitle>")!=-1){
						var alertfenbt = alertText.split("<fengexiancongtitle>");
						alertTitle = alertfenbt[0];
						alertText = alertfenbt[1];
					}
					hlc.popup.open({
						name: "sdAlert信息，第"+(thisindex+1)+"条。"+alertTitle,
						width:(80-j*5)+'%',
						height:(80-j*5)+'%',
						text: alertText,
					});
				}
			}
			
			try{
				var jsonstr = JSON.parse(resText);
			}catch(e){
				//alert(JSON.stringify(args)+"\n响应文本："+resText);
				hlc.popup.open({
					name: "分解响应的JSON数据时出错",
					width:'85%',
					height:'85%',
					text:"请求参数："+JSON.stringify(args),
					html: resText,
				});
				return;							
			}
			if(jsonstr.code==-1){
				window.location.reload();
			}
			
			if(jsonstr.msg){
				hlc.$emit("onSdReqMsg",jsonstr);
			}
					
			if (func){
				func(jsonstr);	
			}
		}
		xmlhttp.withCredentials = true;
		xmlhttp.open('POST',url,true); 
		//xmlhttp.setRequestHeader("If-Modified-Since","0");
		if(args.upload){//文件上传是二进制，不能变成json串
			xmlhttp.send(args.upload);
		}else{
			xmlhttp.send(JSON.stringify(data));
		}
		//xmlhttp.send可接受字符串或formData对象
		//不能直接传{}对象，会被识别为object，后台解析不了
	}
}

*/


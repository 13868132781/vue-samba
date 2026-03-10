/*
hlc是一个全局变量，所以需要全局调用的东西，就挂在这个下面
*/
window.hlc={};

hlc.config = {};//配置信息，会在index/app里去后台获取

hlc.entry = "/just/entry.php";//后台入口
import request from "./request.js";
hlc.ajax = request.ajax;

import valid from "./valid/valid.js";
hlc.valid = valid;

//页面缓存一些东西，避免总是取服务器取
hlc.cache={};

//只认为 undefined或null或'' 为false，0也是true
hlc.True=(o)=>{
	if(o||o===0||o==='0'){
		return true;
	}
	return false;
};

hlc.getKeys=(obj)=>{
	var aaa='';
	for(var t in obj){
		aaa+=", "+t;
	}
	return aaa;
};

hlc.copy=(obj)=>{
	if(!obj){
		return obj;
	}
	if(typeof(val)=='string' || typeof(val)=='boolean'){
		return obj;
	}
	return JSON.parse(JSON.stringify(obj));
};


//合并opt1和opt2，返回opt3。都有的项，后面覆盖前面
//d标识深入几层，0表示所有层
hlc.merge=(opt1,opt2,d)=>{
	d = d||0;
	if(typeof(opt1)!='object'){
		opt1 = {};
	}
	if(typeof(opt2)!='object'){
		opt2 = {};
	}
	
	var opt3=hlc.copy(opt1||{});
	
	for(var k in opt2){
		var val = opt2[k];
		if(Array.isArray(val)){
			opt3[k] = hlc.copy(val);
			
		}else if(val===null){
			opt3[k]=val;
		
		}else if(typeof(val)=='object'){
			if(d==1){
				opt3[k] = hlc.copy(val);
			}else{
				opt3[k] = hlc.merge(opt3[k],val,d-1);
			}
		}else{
			opt3[k]=val;
		}
	}
	//alert(JSON.stringify(opt3));
	return opt3;
};

/*
hlc.mergeAllOld=(opt1,opt2)=>{
	for(var k in opt2){
		var val = opt2[k];
		if(Array.isArray(val)){
			opt1[k] = hlc.copy(val);
					
		}else if(typeof(val)=='object'){
			opt1[k] = opt1[k]||{};
			for(var k1 in val){
				opt1[k][k1] = val[k1];
			}
			
		}else{
			opt1[k]=val;
		}
	}
	//alert(JSON.stringify(opt3));
};
*/

hlc.$on=(eventName,cb)=>{
	document.addEventListener(eventName,(args)=>{
		if(cb){
			cb(args);
		}
	})
};
hlc.$emit=(eventName,args)=>{
	const sendEvent = new CustomEvent(eventName, {
            detail: args,
            bubbles: true,
            cancelable: true,
        }); 
	document.dispatchEvent(sendEvent)
};

hlc.$off=(eventName)=>{//目前没用到
	//var func=null;//这个函数必须要有，应该在$on里保存到hlc.$onFunc[eventName]里
	//document.removeEventListener(eventName,func);
};

//浏览器可视区域高宽，在弹框限定高宽时会用到
//document.body.得在body撑开后获取，才有效
//hlc.cHeight= document.body.clientHeight;
//hlc.cWidth=document.body.clientWidth;
hlc.cHeight = window.innerHeight‌;//包含横向滚动条
hlc.cWidth = window.innerWidth;


/*
firstChild,lastChild,nextSibling,previousSibling都会将空格或者换行当做节点处理，但是有代替属性
所以为了准确地找到相应的元素，会用
firstElementChild,
lastElementChild,
nextElementSibling,
previousElementSibling
兼容的写法，这是JavaScript自带的属性。
但坏消息是IE6,7,8不兼容这些属性。IE9以上和火狐谷歌支持。

*/
/*
获取子元素，也可以用childNodes和children,这是元素的两个数组
但childNodes也可能获取换行、空白
children，获取的是绝对的元素，所以用这个 ,firstChild 就用children[0]代替
需注意children在IE中包含注释节点
*/

function getInnerText(element){
	//判断浏览器是否支持innerText
	if(typeof element.innerText==="string"){
		return element.innerText;
	}else{
		return element.textContent;
	}
}

function sdfirstChild(element){
	if(element.firstElementChild){
		return element.firstElementChild;
	}
	var e = element.firstChild;
    if(e == null){//测试同胞节点是否存在，否则返回空
        return null;
    }
    if(e.nodeType==1){//确认节点为元素节点才返回
		return e;
    }else{
		return sdfirstChild(e);
	}
}
function sdlastChild(element){
	if(element.lastElementChild){
		return element.lastElementChild;
	}
    var e = element.lastChild;
    if(e == null){//测试同胞节点是否存在，否则返回空
        return null;
    }
    if(e.nodeType==1){//确认节点为元素节点才返回
		return e;
    }else{
		return sdlastChild(e);
	}
}
function sdnextSibling(element){
	if(element.nextElementSibling){
		return element.nextElementSibling;
	}
    var e = element.nextSibling;
    if(e == null){//测试同胞节点是否存在，否则返回空
        return null;
    }
    if(e.nodeType==1){//确认节点为元素节点才返回
		return e;
    }else{
		return sdnextSibling(e);
	}
}
function sdpreviousSibling(element){
	if(element.previousElementSibling){
		return element.previousElementSibling;
	}
    var e = element.previousSibling;
    if(e == null){//测试同胞节点是否存在，否则返回空
        return null;
    }
    if(e.nodeType==1){//确认节点为元素节点才返回
		return e;
    }else{
		return sdpreviousSibling(e);
	}
}


/*
再用js设置tr的disply以显示tr时，
如果设置为block，
在其他浏览器正常
在IE9+，其宽度仅为第一个td的宽度
需设置为talbe-row
*/


function waitfordiv(){
	setTimeout(function(){
		var thishtml='<div style="position:fixed;left:0px;right:0px;height:0px;top:0px;text-align:center;padding:100px;color:#00f;background:rgba(255,255,255,0.7)">正在扫描中，时间有点长，请等待<br><img src="../include/image/loading.gif" style="width:50px;height:50px"/></div>';
		document.body.innerHTML+=thishtml;
	},0);
}


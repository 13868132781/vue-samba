


function hlcmenu(objs,list,func){
	if(!objs || !list || !func){
		alert('缺少参数');
		return;
	}
	
	if(objs.tagName){
		objs=new Array(objs);
	}

	for(var i=0;i<objs.length;i++){
		obj=objs[i];
		obj.oncontextmenu=function(ev){
			var e = ev || window.event;
			
			//var obj=e.srcElement ? e.srcElement : e.target;
			//alert(obj.tagName);
			//if(obj.id!='cellinput'){
			//	return false;
			//}
			
			var menu=document.getElementById("menudiv"); 
			if(menu){
				document.body.removeChild(menu);
			}
					

			var menu=document.createElement("div");
			menu.id="menudiv";
			menu.style.position="absolute";
			menu.style.zIndex="10000";
			//menu.style.width="150px";
			menu.style.padding="2px";
			menu.style.cursor="pointer";
			menu.style.background="#fff";
			menu.style.border="1px solid #ccc";
			menu.style.borderRadius="5px";
			menu.style.boxShadow="2px 2px 2px #888";

			for(var j=0;j<list.length;j++){
				var menuo=document.createElement("div");
				menuo.style.padding="3px";
				menuo.style.paddingLeft="10px";
				menuo.style.paddingRight="10px";
				menuo.style.lineHeight="16px";
				menuo.onmouseover=function(){
					this.style.background="#ddd";
				};
				menuo.onmouseout=function(){
					this.style.background="";
				};
				menuo.innerHTML=list[j];
				let myj=j;
				menuo.onclick=function(){func(myj,menuo);};
				menu.appendChild(menuo);
			}
			
			
			document.body.appendChild(menu);
			
			
		　　
		　　//var menu=document.getElementById("menudiv"); 
			menu.style.display = "block";
			
			var menuwidth=menu.offsetWidth;
			var menuheight=menu.offsetHeight;
			
			var winwidth =window.document.documentElement.clientWidth?window.document.documentElement.clientWidth:window.document.body.offsetWidth;
			var winheight =window.document.documentElement.clientHeight?window.document.documentElement.clientHeight:window.document.body.offsetHeight;
			var scrolltop =window.document.documentElement.scrollTop?window.document.documentElement.scrollTop:window.document.body.scrollTop;
			
			//alert("x:"+e.clientY+"	menuheight:"+menuheight+"	winheight:"+winheight);
			//alert(menuheight);
			var x=e.clientX+'px';
			var y=(e.clientY+scrolltop)+'px';
			if(e.clientX+menuwidth > winwidth){
				x=(e.clientX-menuwidth)+"px";
			}
			if(e.clientY+menuheight > winheight){
				y=(e.clientY+scrolltop-menuheight-1)+"px";
			}
			

			menu.style.left=x;
			menu.style.top=y;
			
		　　return false; //很重要，不能让浏览器显示自己的右键菜单
		}
	}
}

document.onclick = function(ev) {
	var menu=document.getElementById("menudiv"); 
	if(menu){
		document.body.removeChild(menu);
	}
			
}


function hlcmenu1(xy,list,func){
			var menu=document.getElementById("menudiv"); 
			if(menu){
				document.body.removeChild(menu);
			}
					

			var menu=document.createElement("div");
			menu.id="menudiv";
			menu.style.position="absolute";
			menu.style.zIndex="10000";
			//menu.style.width="150px";
			menu.style.padding="5px";
			menu.style.cursor="pointer";
			menu.style.background="#fff";
			menu.style.border="0px solid #ccc";
			menu.style.borderRadius="2px";
			menu.style.borderTop="0px solid #bbb";
			menu.style.borderLeft="0px solid #bbb";
			menu.style.boxShadow="1px 1px 2px 1px #ccc";

			for(var j=0;j<list.length;j++){
				var menuo=document.createElement("div");
				menuo.style.padding="5px";
				menuo.style.paddingLeft="10px";
				menuo.style.paddingRight="10px";
				menuo.style.lineHeight="16px";
				menuo.onmouseover=function(){
					this.style.background="#eee";
				};
				menuo.onmouseout=function(){
					this.style.background="";
				};
				menuo.innerHTML=list[j];
				let myj=j;
				menuo.onclick=function(){func(myj,menuo);};
				menu.appendChild(menuo);
			}
			
			
			document.body.appendChild(menu);
			
			menu.style.left=xy.x+"px";
			menu.style.top=xy.y+"px";

}
		
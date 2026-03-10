/*
<table>
<tr>//id的作用是通过count找tr
	<td><checkbox id='ti_chkbox'></td>
	....
	<td><img id='ti_status'></td>
	....
	<td><span id='ti_button' onclick="tableclick_click(this,'data')"></td>
	....
	<td><img id='ti_result'></td>
	....
</tr>
</table>
*/
tableclick_maxcount=10;
tableclick_pageexec="ht/ping.php?sid=0&hand=0&id=";
tableclick_pagelogs="show_detailbp.php?logid=";
tableclick_callback=null;
tableclick_debug=0;

tableclick_newtr="";
tableclick_nowcount=0;

function tableclick_allclick(clickid,n){
	n = n || '';
	if(!clickid) clickid="ti_button"+n;
	tableclick_nowcount=0;
	tableclick_newtr=document.getElementById("headtr");
    while(tableclick_newtr=sdnextSibling(tableclick_newtr)){
		var ti_click=s(tableclick_newtr,clickid);
		var ti_chkbox=s(tableclick_newtr,'ti_chkbox');
		if(ti_click&&(!ti_chkbox || (ti_chkbox&&ti_chkbox.checked)) ){
			ti_click.click();
			tableclick_nowcount++;
			if(tableclick_nowcount>=tableclick_maxcount){
				break;
			}
		}
	}	
}
// 配置备份哪里调用这个函数，又返回当前页面的函数
function tableclick_click(obj,data,n){
	n = n||'';
	var tr_obj=obj.parentNode.parentNode;
	var ti_button=s(tr_obj,'ti_button'+n);
	var ti_chkbox=s(tr_obj,'ti_chkbox');
	var ti_status=s(tr_obj,'ti_status'+n);
	if(ti_button)
		ti_button.style.background="url(../include/image/progress.gif)";
	if(ti_status)
		ti_status.src="../include/image/loading/042.gif";
	var arra=new Array();
    arra[0]=tr_obj;
	arra[1]=obj.id;
	arra[2]=n;
	sendRequest(window['tableclick_pageexec'+n]+data,tableclick_deall,arra);
}

function tableclick_deall(backval,arr){
	if(tableclick_debug!=0){
		alert(backval);
	}
	//下划线分割，第一个表示结果，0表示成功,其他表示失败,第二个表示logid
	if(backval.indexOf("<returnstr>") > -1 ){
		backval=backval.split("<returnstr>")[1];
	}
	var bvarr=backval.split("_");
	
	if(backval.indexOf("<jsonstr>") > -1 ){
		var jsonarr=eval("("+backval.split("<jsonstr>")[1]+")");
		bvarr[0]=jsonarr['code'];
		bvarr[1]=jsonarr['logid'];
	}
	
	
	tr_obj=arr[0];
	
	
	
	var ti_button=s(tr_obj,'ti_button'+arr[2]);
	var ti_chkbox=s(tr_obj,'ti_chkbox');
	var ti_status=s(tr_obj,'ti_status'+arr[2]);
	var ti_statustext=s(tr_obj,'ti_statustext'+arr[2]);
	var ti_result=s(tr_obj,'ti_result'+arr[2]);
	
	if(bvarr[2]&&ti_statustext)
		ti_statustext.innerHTML=bvarr[2];
	
	if (bvarr[0] == "0"){
		if(ti_button){
			ti_button.style.background="#CAFF70";
		}
		if(ti_status){
			if(bvarr[1]=="0")
				ti_status.src="../include/image/nas/7.gif";
			else
				ti_status.src="../include/image/nas/5.gif";
		}
		if(ti_result){
			ti_result.src="../include/image/project.gif";
			eval("ti_result.onclick = function(){tableclick_log(this,'"+bvarr[1]+"','"+arr[2]+"');}");
		}
	}else{
		if(ti_button)
			ti_button.style.background="#FFE4E1";
		if(ti_status){
			ti_status.src="../include/image/nas/6.gif";
		}
		if(ti_result){
			ti_result.src="../include/image/project1.gif";
			eval("ti_result.onclick = function(){tableclick_log(this,'"+bvarr[1]+"','"+arr[2]+"');}");
		}
   }
   
   if(tableclick_callback&&typeof(tableclick_callback)=="function"){ 
	   tableclick_callback(backval,arr);
   }
   
	var clickid='ti_button';
	if(arr[1]) clickid=arr[1];
	if(tableclick_newtr){
		while(tableclick_newtr=sdnextSibling(tableclick_newtr)){
			var ti_click=s(tableclick_newtr,clickid);
			var ti_chkbox=s(tableclick_newtr,'ti_chkbox');
			if(ti_click&&(!ti_chkbox || (ti_chkbox&&ti_chkbox.checked)) ){
				ti_click.click();
				break;
			}
		}
	} 
}


function tableclick_log(img_obj,logid,n){
    
	if(img_obj.src.indexOf('emp')> -1){
	   return;
	}
	
    var diag = new Dialog();
	diag.ID=10;
 	diag.Width = 600;
 	diag.Height = 480;
    diag.URL=window['tableclick_pagelogs'+n]+logid;
	diag.show();
}

/*
<table>
<tr id='6_tr' zdy-level='' zdy-fason='0' zdy-data='1.1.1.1_9'>//id的作用是通过count找tr
	....
	<td><img id='6_sta'></td>
	....
	<td><span id='6_bk'></td>//点击时，就是用过count 6来找6_tr的tr
	....
	<td><img id='6_res'></td>
	....
</tr>
</table>
*/
tableclick_maxcount=10;
tableclick_pageexec="ht/ping.php?sid=0&hand=0&id=";
tableclick_pagelogs="show_detailbp.php?logid=";


tableclick_newtr="";
tableclick_nowcount=0;

function tableclick_allclick(args){
	tableclick_nowcount=0;
	tableclick_newtr=document.getElementById("headtr");
    while(tableclick_newtr=tableclick_newtr.nextSibling){
		globalid=tableclick_newtr.id.split("_")[0];
		var bk_obj=document.getElementById(globalid+"_bk");
		var box_obj=document.getElementById(globalid+"_box");
		if(bk_obj&&(!box_obj || box_obj&&box_obj.checked) ){
			tableclick_click(globalid);
			tableclick_nowcount++;
			if(tableclick_nowcount>=tableclick_maxcount){
				break;
			}
		}
	}	
}

function tableclick_click(globalid){
	tr_obj=document.getElementById(globalid+"_tr");
	bk_obj=document.getElementById(globalid+"_bk");
    bk_obj.style.background="url(../include/image/progress.gif)";
	var arra=new   Array();
    arra[0]=globalid;
	sendRequest(tableclick_pageexec+tr_obj.getAttribute('zdy-data'),tableclick_deall,arra);
}

function tableclick_deall(backval,arr){
	//alert(backval);
	//下划线分割，第一个表示结果，0表示成功,其他表示失败,第二个表示logid
	var bvarr=backval.split("_");
	tr_obj=document.getElementById(arr[0]+"_tr");
	bk_obj=document.getElementById(arr[0]+"_bk");
	if (bvarr[0] == "0"){
		bk_obj.style.background="#CAFF70";
		if(document.getElementById(arr[0]+"_sta"))
			document.getElementById(arr[0]+"_sta").src="../include/image/nas/5.gif";
		if(document.getElementById(arr[0]+"_res")){
			res=document.getElementById(arr[0]+"_res");
			res.src="../include/image/project.gif";
			eval("res.onclick = function(){tableclick_log(this,'"+bvarr[1]+"');}");
		}
   }else{
		bk_obj.style.background="#FFE4E1";
		if(document.getElementById(arr[0]+"_sta"))
			document.getElementById(arr[0]+"_sta").src="../include/image/nas/6.gif";
		if(document.getElementById(arr[0]+"_res")){
			res=document.getElementById(arr[0]+"_res");
			res.src="../include/image/project1.gif";
			eval("res.onclick = function(){tableclick_log(this,'"+bvarr[1]+"');}");
		}
   }
   
   if(tableclick_newtr){
		while(tableclick_newtr=tableclick_newtr.nextSibling){
			globalid=tableclick_newtr.id.split("_")[0];
			var bk_obj=document.getElementById(globalid+"_bk");
			var box_obj=document.getElementById(globalid+"_box");
			if(bk_obj&&(!box_obj || box_obj&&box_obj.checked) ){
				tableclick_click(globalid);
				break;
			}
		}
   }
}


function tableclick_log(img_obj,logid){
    
	if(img_obj.src.indexOf('emp')> -1){
	   return;
	}
	
    var diag = new Dialog();
	diag.ID=10;
 	diag.Width = 600;
 	diag.Height = 380;
    diag.URL=tableclick_pagelogs+logid;
	diag.show();
}



//这一套是用于父子列表的,tr里新加一个属性zdy-fason
function tableclick1_allclick(args){
	tableclick_nowcount=0;
	tableclick_newtr=document.getElementById("headtr");
	var fason_data="";
    while(tableclick_newtr=tableclick_newtr.nextSibling){
		var fason=tableclick_newtr.getAttribute('zdy-fason');
		if(fason=='0'){
			
		}
		globalid=tableclick_newtr.id.split("_")[0];
		var bk_obj=document.getElementById(globalid+"_bk");
		var box_obj=document.getElementById(globalid+"_box");
		if(bk_obj&&(!box_obj || box_obj&&box_obj.checked) ){
			tableclick_click(globalid);
			tableclick_nowcount++;
			if(tableclick_nowcount>=tableclick_maxcount){
				break;
			}
		}
	}	
}

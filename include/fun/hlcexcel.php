<div ><?php echo $hlcexcel_debugstr;?></div>
<div style="text-align:center;color:#00f;" id="dataloading">数据加载中</div>
<?php echo hlcphp_html_head();?>
<style>
.menudiv{ 
	display: none;
	position: absolute;
	z-index:200;
	background:#ddd;
}
.menudivmid{ 
	position:relative;
	left:-1px;
	top:-1px;
	background:#bbb;
}
.menudivin{ 
	position:relative;
	left:-1px;
	top:-1px;
	background:#f8f8f8;
	border:1px solid #aaa;
	width:150px;
	padding:3px;
	
}

.menudiv table{ 
	cursor:default;
	width:100%;
}
.menudiv td{
	padding:2px;
	padding-left:5px;
}
.menudiv td:hover{
	background:#63B8FF;
}

td.td_splitline:hover{
	background:; 
}

.menudiv input{
	border:0px;
	background:#f8f8f8;
	width:100%;
	text-align:left;
	font-family:宋体;
	padding:2px;
	padding-left:5px;
}
.menudiv input:hover{
	background:#63B8FF;
}

.firstcellcss{
	width:1%;
	white-space: nowrap;
}

</style>
<body class='main_body'>
<div class="menudiv" id="menudiv" >
<div  class="menudivmid">
<div  class="menudivin">
	<table>
		<tr>
		<td onclick="hlcexcel_colpaste();">填充余列</td>
		<td onclick="hlcexcel_rowdel(1)">删除这一行</td>
		</tr>
		<tr>
		<td onclick="hlcexcel_colpaste(true);">填充新行</td>
		<td onclick="hlcexcel_rowadd(1)">插入下一行</td>
		</tr>
		<tr>
		<td onclick="hlcexcel_coloper('cut')"   >剪切余列</td>
		<td onclick="hlcexcel_rowadd(5)">插入下五行</td>
		</tr>
		<tr>
		<td onclick="hlcexcel_coloper('cpy')"  >复制余列</td>
		<td onclick="hlcexcel_rowadd(10)">插入下十行</td>
		</tr>
		<tr>
		<td onclick="hlcexcel_coloper('del')"  >清空余列</td>
		<td onclick="hlcexcel_rowadd()">插入上一行</td>
		</tr>
		<tr><td colspan='2' class="td_splitline"><hr style="width:100%;height:1px;border:none;border-top:1px dashed #aaa;"/></td></tr>
		<tr>
		<td onclick="hlcexcel_rowdel()">删除余行</td>
		<td onclick="hlcexcel_celldel()">删除单元格</td>
		
		</tr>
		<tr>
		<td onclick="hlcexcel_rowmove(1)">整行下移</td>
		<td onclick="hlcexcel_celladd(true)">插入下一格</td>
		
		</tr>
		<tr>
		<td onclick="hlcexcel_rowmove(-1)">整行上移</td>
		<td onclick="hlcexcel_celladd(false)">插入上一格</td>
		
		</tr>
		<tr><td colspan='2' class="td_splitline"><hr style="width:100%;height:1px;border:none;border-top:1px dashed #aaa;"/></td></tr>
		<tr>
		<td style="padding:0px;margin:0px"><input onclick="hlcexcel_copy()" type="button" value="复制"/></td>
		<td style="padding:0px;margin:0px"><input onclick="hlcexcel_paste()" type="button" value="粘贴"/></td>
		</tr>
		<tr>
		<td style="padding:0px;margin:0px"><input onclick="hlcexcel_cut()" type="button" value="剪切"/></td>
		<td style="padding:0px;margin:0px"><input onclick="hlcexcel_delete()" type="button" value="删除"/></td>
		</tr>
	</table>
</div>
</div>
</div>

<table id="tablelist" class="main_listtable" cellspacing="0" cellpadding="0" >
<tr class="main_listtable_titletr">
	<td colspan="20">
    	<div class="main_listtable_titletr_divleft" style="width:500px">批量操作列表【<?php if($_GET['mode']=='mod') echo '修改';else echo '添加'?>模式】（*不能为空，!不能重复，@必须是IP，#必须是数字）</div>
   </td>
</tr>

<tr class="main_listtable_headtr">
<td class="firstcellcss"></td>
<?php 
foreach($hlcexcel_list as $hlcexcel_list_one){
	if(strstr($hlcexcel_list_one['name'],'*')){
		$onename=str_replace('*',"<span style='color:#f00'>*",$hlcexcel_list_one['name']).'</span>';
	}else{
		$onename=$hlcexcel_list_one['name'];
	}
	$selecthtml="";
	if(is_array($hlcexcel_list_one['limit'])){
		$selecthtml='';
		foreach($hlcexcel_list_one['limit'] as $limitkey => $limitval){
			$selecthtml.='<span style="display:block;white-space: nowrap;">'.$limitkey.'：'.$limitval.'</span>';
		}
		$selecthtml.='';
		
		echo '<td><div style="position:relative;z-index:100;">'.$onename.'<div style="position:absolute;border:1px solid #aaa;width:12px;right:3px;top:0px;padding-left:5px;background-color:#fff;cursor:pointer" onclick="if(typeof(hlcexcel_xuanzex)!=\'undefined\'){hlcexcel_xuanzex.style.display=\'none\';};hlcexcel_xuanzex=sdnextSibling(this);sdnextSibling(this).style.display=\'block\';">></div><div style="z-index:100;background:#fff;position:absolute;left:-1px;top:16px;width:auto;display:none;border:1px solid #aaa;cursor:pointer;padding:5px"  onclick="this.style.display=\'none\';">'.$selecthtml.'</div></div></td>'; 
	}else{ 
		echo '<td'.$display.'>'.$onename.'</td>'; 
	}
	
}?>
</tr>



<tr class="main_listtable_headtr">
<td class="firstcellcss">例</td>
<?php 
foreach($hlcexcel_list as $hlcexcel_list_one){
	echo '<td>'.$hlcexcel_list_one['temp'].'</td>';
}?>
</tr>

<?php
if($hlcexcel_data==''){
	$hlcexcel_data=array(array());
}

$hlcexcel_nonedata=array();

$countnum=1;
foreach($hlcexcel_data as $hlcexcel_data_one){
	foreach($hlcexcel_nonelist as $hlcexcel_none_one){
		$hlcexcel_nonedata[$hlcexcel_data_one['key']][$hlcexcel_none_one['field']]=$hlcexcel_data_one[$hlcexcel_none_one['cols']];
	}
	
	echo '<tr class="main_listtable_celltr" id="'.$hlcexcel_data_one['key'].'">';
	echo '<td class="firstcellcss" style="background-color:#F0F0F0">'.$countnum;
	if($hlcexcel_data_one['key']!=''){
		echo '<span style="color:#aaa;margin-left:5px">('.$hlcexcel_data_one['key'].')</span>';
	}
	echo '</td>';
	$countnum++;
 
	foreach($hlcexcel_list as $hlcexcel_list_one){
		$mysqlcols=$hlcexcel_list_one['field'];
		if($hlcexcel_list_one['cols']!=''){
			$mysqlcols=$hlcexcel_list_one['cols'];
		}
		$mysqlval=$hlcexcel_data_one[$mysqlcols];
		if($hlcexcel_list_one['maps']!=''){
			foreach($hlcexcel_list_one['maps']as $mk => $mv){
				if($mv==$mysqlval){
					$mysqlval=$mk;
					break;
				}
			}
		}
		echo '<td><div  id="cellwraper" style="position:relative;padding-left:1px;padding-right:1px;border:1px solid #fff">
		<input  id="cellinput" type="text" style="line-height:18px;height:18px;width:100%;border:0px;" value="'.$mysqlval.'"/>
		<div id="cellcopy" style="width:10px; padding-left:5px; padding-right:5px; position:absolute; top:0px; right:0px; cursor:crosshair;">+</div></div></td>';
	}
	echo '</tr>';
}
?> 


</table>

<form method="post" onSubmit="return hlcexcel_submit()">
<input style="display:none" type="submit" id="submit" name="submit" value="submit"/>
<input type="hidden" id="hlcexcel_data" name="hlcexcel_data" value="" />
<input type="hidden" id="hlcexcel_delid" name="hlcexcel_delid" value="'0'" />
</form>

</body>
</html>
<script type="text/javascript" src="../include/js/json2.js"></script>
<script language="javascript">

hlcexcel_list=<?php echo json_encode($hlcexcel_list);?>;
hlcexcel_nonedata=<?php echo json_encode($hlcexcel_nonedata);?>;

hlcexcel_create();

function hlcexcel_create(data){
	hlcexcel_tableobj=document.getElementById('tablelist');
	hlcexcel_event();
	hlcexcel_olddata=hlcexcel_getdata(true);
	
	if(document.getElementById('dataloading')){
		document.getElementById('dataloading').style.display="none";
	}
}

function hlcexcel_cut(){
	hlcexcel_selectobj.focus();
	document.execCommand("Cut","false",null);
}
function hlcexcel_copy(){
	hlcexcel_selectobj.focus();
	document.execCommand("Copy");
}
function hlcexcel_delete(){
	hlcexcel_selectobj.focus();
	document.execCommand("Delete","false",null);
}
function hlcexcel_paste(){
	hlcexcel_selectobj.focus();
	document.execCommand("Paste");
}

function hlcexcel_coloper(oper){
	var table_td=hlcexcel_selectobj.parentNode.parentNode;
	var table_tr=table_td.parentNode;
	var val="";
	for(var i=table_tr.rowIndex;i<hlcexcel_tableobj.rows.length;i++){
		if(val==''){
			val=s(hlcexcel_tableobj.rows[i].cells[table_td.cellIndex],"cellinput").value;
		}else{
			val+="\r\n"+s(hlcexcel_tableobj.rows[i].cells[table_td.cellIndex],"cellinput").value;
		}
		if(oper=='cut' || oper=='del'){
			s(hlcexcel_tableobj.rows[i].cells[table_td.cellIndex],"cellinput").value="";
		}
	}
	if(oper!='del'){
		clipboardData.setData("text",val);
	}
}


function hlcexcel_colpaste(create){

	var content = clipboardData.getData("Text");
	if (content==null) {
		return;
	} 
	
	var table_td=hlcexcel_selectobj.parentNode.parentNode;
	var table_tr=table_td.parentNode;
	
	var contents=content.split("\r\n");
	
	for(var i=0;i<contents.length;i++){
		if(i!=0 && (create==true || !hlcexcel_tableobj.rows[table_tr.rowIndex+i]) ){
			hlcexcel_addtr(table_tr.rowIndex+i-1);
		}
		s(hlcexcel_tableobj.rows[table_tr.rowIndex+i].cells[table_td.cellIndex],"cellinput").value=contents[i];
	}

}


function hlcexcel_rowdel(num){
	var table_td=hlcexcel_selectobj.parentNode.parentNode;
	var table_tr=table_td.parentNode;
	
	if(!num){
		num=hlcexcel_tableobj.rows.length;
	}
	
	var deleteindex=table_tr.rowIndex;
	for(var i=0;i<num;i++){
		var thisrowtr=hlcexcel_tableobj.rows[deleteindex];
		if(!thisrowtr) break;
		if(thisrowtr.id){
			document.getElementById('hlcexcel_delid').value+=",'"+thisrowtr.id+"'";
		}
		hlcexcel_tableobj.deleteRow(thisrowtr.rowIndex); 
	}
	hlcexcel_order();
}

function hlcexcel_rowadd(num){
	//num为新建行数，为null时在上面插入一行
	var table_td=hlcexcel_selectobj.parentNode.parentNode;
	var table_tr=table_td.parentNode;
	if(num){
		for(var i=0;i<num;i++){
			hlcexcel_addtr(table_tr.rowIndex);
		}
	}else{
		hlcexcel_addtr(table_tr.rowIndex-1);
	}
}


function hlcexcel_celldel(){
	var table_td=hlcexcel_selectobj.parentNode.parentNode;
	var table_tr=table_td.parentNode;
	for(var i=table_tr.rowIndex;i<hlcexcel_tableobj.rows.length;i++){
		if(hlcexcel_tableobj.rows[i+1]){
			s(hlcexcel_tableobj.rows[i].cells[table_td.cellIndex],"cellinput").value=s(hlcexcel_tableobj.rows[i+1].cells[table_td.cellIndex],"cellinput").value;
		}else{
			s(hlcexcel_tableobj.rows[i].cells[table_td.cellIndex],"cellinput").value="";
		}
	}
}


function hlcexcel_celladd(next){
	//next为false时是在上面插入单元格，为true是在下面插入单元格
	var table_td=hlcexcel_selectobj.parentNode.parentNode;
	var table_tr=table_td.parentNode;
	var val="";
	
	if(s(hlcexcel_tableobj.rows[hlcexcel_tableobj.rows.length-1].cells[table_td.cellIndex],"cellinput").value!=''){
		hlcexcel_addtr(hlcexcel_tableobj.rows.length-1);
	}
	for(var i=hlcexcel_tableobj.rows.length-1;i>table_tr.rowIndex;i--){
		s(hlcexcel_tableobj.rows[i].cells[table_td.cellIndex],"cellinput").value=s(hlcexcel_tableobj.rows[i-1].cells[table_td.cellIndex],"cellinput").value;
	}
	if(next){
		s(hlcexcel_tableobj.rows[table_tr.rowIndex+1].cells[table_td.cellIndex],"cellinput").value="";
	}else{
		s(hlcexcel_tableobj.rows[table_tr.rowIndex].cells[table_td.cellIndex],"cellinput").value="";
	}
}



function hlcexcel_addtr(rowindex){
	trc = hlcexcel_tableobj.insertRow(rowindex+1);
	trc.className="main_listtable_celltr";
	var firstcell=trc.insertCell();
	firstcell.style.backgroundColor="#F0F0F0";
	firstcell.className="firstcellcss";
	firstcell.innerHTML='10';
	for(var i=0;i<hlcexcel_list.length;i++){
	var tdc=trc.insertCell(); 
		tdc.innerHTML='<div  id="cellwraper" style="position:relative;padding-left:1px;padding-right:1px;border:1px solid #fff">\
		<input  id="cellinput" type="text" style="line-height:18px;height:18px;width:100%;border:0px;" value=""/>\
		<div id="cellcopy" style="width:10px; padding-left:5px; padding-right:5px; position:absolute; top:0px; right:0px; cursor:crosshair;">+</div></div>';
	}
	hlcexcel_order();
	
}


function hlcexcel_order(){
	for(var p=0;p<hlcexcel_tableobj.rows.length;p++){
		if(p>2){
			var idhtml="";
			if(hlcexcel_tableobj.rows[p].cells[0].innerHTML.indexOf('<span')!=-1){
				idhtml=hlcexcel_tableobj.rows[p].cells[0].innerHTML.slice(hlcexcel_tableobj.rows[p].cells[0].innerHTML.indexOf('<span'));
				
			}
			if(hlcexcel_tableobj.rows[p].cells[0].innerHTML.indexOf('<SPAN')!=-1){
				idhtml=hlcexcel_tableobj.rows[p].cells[0].innerHTML.slice(hlcexcel_tableobj.rows[p].cells[0].innerHTML.indexOf('<SPAN'));
				
			}
			hlcexcel_tableobj.rows[p].cells[0].innerHTML=(p-2)+idhtml;
		}
	}
}

function hlcexcel_event(){
	window.onload = function(){
		document.oncontextmenu=function(ev){
			var e = ev || window.event;
			var obj=e.srcElement ? e.srcElement : e.target;
			//alert(obj.tagName);
			if(obj.id!='cellinput'){
				return false;
			}

		　　
		　　var menu=document.getElementById("menudiv"); 
			menu.style.display = "block";
			
			var menuwidth=menu.offsetWidth;
			var menuheight=menu.offsetHeight;
			
			var winwidth =window.document.documentElement.clientWidth?window.document.documentElement.clientWidth:window.document.body.offsetWidth;
			var winheight =window.document.documentElement.clientHeight?window.document.documentElement.clientHeight:window.document.body.offsetHeight;
			var scrolltop =window.document.documentElement.scrollTop?window.document.documentElement.scrollTop:window.document.body.scrollTop;
			
			//alert("x:"+e.clientY+"	menuheight:"+menuheight+"	winheight:"+winheight);
			
			var x=e.clientX+'px';
		　　var y=(e.clientY+scrolltop)+'px';
			if(e.clientX+menuwidth > winwidth){
				x=(e.clientX-menuwidth)+"px";
			}
			if(e.clientY+menuheight > winheight){
				y=(winheight-menuheight+scrolltop-1)+"px";
			}
			

			menu.style.left=x;
			menu.style.top=y;
			
		　　return false; //很重要，不能让浏览器显示自己的右键菜单
		}

		document.onclick = function(ev) {
			var menu=document.getElementById("menudiv"); 
			if(menu.style.display != "none"){
				menu.style.display = "none";
			}
			
		}
		
		document.onmousedown=function(ev){
			var e = ev || window.event;
			var obj=e.srcElement ? e.srcElement : e.target;
			
			if(typeof(hlcexcel_xuanzex)!='undefined'){hlcexcel_xuanzex.style.display='none';};

			if(obj.id=='cellinput'){
				if(typeof(hlcexcel_selectobj)!='undefined' && hlcexcel_selectobj.parentNode){
					hlcexcel_selectobj.parentNode.style.borderColor="#fff";
				}
				obj.parentNode.style.borderColor="#006400";
				hlcexcel_selectobj=obj;
				hlcexcel_selecttxt=window.getSelection?window.getSelection():document.selection.createRange().text;
			}

			if(obj.id!='cellcopy'){//有时遇到找不到该值
				return;
			}
			obj.style.backgroundColor="#4876FF";
			
			var scrolltop =window.document.documentElement.clientHeight?window.document.documentElement.scrollTop:window.document.body.scrollTop;
			
			var disX=e.clientX-obj.offsetLeft;
			var disY=e.clientY+scrolltop-obj.offsetTop;
			document.onselectstart=function(ev){
				return false;
			}
		 
			document.onmousemove=function(ev){
				var scrolltop =window.document.documentElement.clientHeight?window.document.documentElement.scrollTop:window.document.body.scrollTop;
				var e = ev || window.event;
				if(e.clientY+scrolltop-disY > 0){
					obj.style.height=e.clientY+scrolltop-disY+'px';
				}
			};
			document.onmouseup=function(ev){
				var eup = ev || window.event;
				var objup=eup.srcElement ? eup.srcElement : eup.target;
				

				
				var table_tr=obj.parentNode.parentNode.parentNode;
				var table_td=obj.parentNode.parentNode;
				//var hlong=objup.parentNode.parentNode.parentNode.rowIndex-table_tr.rowIndex;
				//上面的方法得要处理objup跑到别的元素上的问题
				var hlong=Math.ceil(obj.style.height.replace('px','')/(table_tr.offsetHeight));
				
				for(var i=1;i<hlong;i++){
					if((table_tr.rowIndex+i)<hlcexcel_tableobj.rows.length){
						s(hlcexcel_tableobj.rows[table_tr.rowIndex+i].cells[table_td.cellIndex],"cellinput").value=s(hlcexcel_tableobj.rows[table_tr.rowIndex].cells[table_td.cellIndex],"cellinput").value; 
					}
				}
				document.onmousemove=null;
				document.onmouseup=null;
				document.onselectstart=null;
				obj.style.height='';
				obj.style.backgroundColor="";
				
			};  
		};
	};
}



function hlcexcel_check(){
	var returnstr='';
	var cellstrings=[];
	for(var i=0;i<hlcexcel_tableobj.rows.length;i++){
		for(var j=0;j<hlcexcel_list.length;j++){
			var thisreturncode='';
			var inputobj=s(hlcexcel_tableobj.rows[i].cells[j+1],"cellinput");
			if(!inputobj) continue;
			if(!hlcexcel_list[j]['name']) continue;
			if(hlcexcel_list[j]['name'].indexOf('*')!=-1){
				if(inputobj.value==''){
					thisreturncode="不能为空";
				}
			}
			if(hlcexcel_list[j]['name'].indexOf('@')!=-1){
				var exp=/^(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$/;  
				if(!exp.test(inputobj.value)){
					thisreturncode="不是合法的ip地址";
				} 
				
			}
			if(hlcexcel_list[j]['name'].indexOf('#')!=-1){
				if(isNaN(Number(inputobj.value)) || inputobj.value.indexOf('.')!=-1){
					thisreturncode="必须为数字";
				}
			}
			if(hlcexcel_list[j]['name'].indexOf('!')!=-1){
				if(!cellstrings[j]) cellstrings[j]='|';
				if(cellstrings[j].indexOf('|'+inputobj.value+'|')!=-1){
					thisreturncode="内容跟之前单元格重复";
				}
				cellstrings[j]+=inputobj.value+"|";
			
			} 
			if(hlcexcel_list[j]['limit'] && hlcexcel_list[j]['limit']!=''){	
				if(typeof(hlcexcel_list[j]['limit'])=='function'){
					
				}else if(typeof(hlcexcel_list[j]['limit'])=='object'){
					var ishere='';
					for(var t in hlcexcel_list[j]['limit']){
						if(t==inputobj.value){
							ishere="true";
						}
					}
					if(!ishere){
						thisreturncode="超出限定范围";
					}
				}else if(typeof(hlcexcel_list[j]['limit'])=='string'){
					if(hlcexcel_list[j]['limit'].indexOf('mysqlexist|')!=-1){
						if(hlcexcel_list[j]['limit'].indexOf('|'+inputobj.value+'|')!=-1){
							thisreturncode="数据库里已存在该数据";
						}
					} 
				}
			}
			
			
			
			
			if(thisreturncode!=''){
				inputobj.parentNode.style.borderColor="#f00";
				inputobj.title =thisreturncode;
				returnstr='error';
			}else{
				inputobj.parentNode.style.borderColor="#fff";
				inputobj.title ="";
			}
		}
	}
	
	return returnstr;
}


function hlcexcel_rowmove(n){
	var table_td=hlcexcel_selectobj.parentNode.parentNode;
	var table_tr=table_td.parentNode;
	if(table_tr.rowIndex+n>2){
		hlcexcel_swap(table_tr,hlcexcel_tableobj.rows[table_tr.rowIndex+n]);
	}
}

//定义通用的函数交换两个结点的位置   
function hlcexcel_swap(node1,node2){  
	if(!node1 || !node2) return;
	//获取父结点   
	var _parent=node1.parentNode;   
	//获取两个结点的相对位置   
	var _t1=sdnextSibling(node1);   
	var _t2=sdnextSibling(node2);   
	//将node2插入到原来node1的位置   
	if(_t1)_parent.insertBefore(node2,_t1);   
	else _parent.appendChild(node2);   
	//将node1插入到原来node2的位置   
	if(_t2)_parent.insertBefore(node1,_t2);   
	else _parent.appendChild(node1);   
}


function hlcexcel_getdata(json){
	if(json){
		var arrayObj ={};
	}else{
		var arrayObj = new Array();
	}
	for(var i=3;i<hlcexcel_tableobj.rows.length;i++){
		var arrayObjsub = {};
		for(var j=0;j<hlcexcel_list.length;j++){
			var backname=hlcexcel_list[j]['field'];
			var backval=arrayObjsub[backname]=s(hlcexcel_tableobj.rows[i].cells[j+1],"cellinput").value.replace('"','');
			if(hlcexcel_list[j]['maps']){
				backval=hlcexcel_list[j]['maps'][backval];
			}
			arrayObjsub[backname]=backval;
		}
		if(hlcexcel_tableobj.rows[i].id && hlcexcel_tableobj.rows[i].id!=''){
			arrayObjsub['key']=hlcexcel_tableobj.rows[i].id;
			
			if(typeof(hlcexcel_olddata)!='undefined' && hlcexcel_olddata[arrayObjsub['key']] && JSON.stringify(arrayObjsub) == JSON.stringify(hlcexcel_olddata[arrayObjsub['key']])){
				continue;
			}
		}
		
		if(arrayObjsub['key'] && hlcexcel_nonedata[arrayObjsub['key']]){
			for(var nonetmp in hlcexcel_nonedata[arrayObjsub['key']]){
				arrayObjsub[nonetmp]=hlcexcel_nonedata[arrayObjsub['key']][nonetmp];
			}
			
		}
		
		
		if(json){
			if(arrayObjsub['key']){
				arrayObj[arrayObjsub['key']]=arrayObjsub;
			}
		}else{
			arrayObj.push(  arrayObjsub );
		}
	}
	//alert('[' + arrayObj.join(',') + ']');
	return arrayObj;
}


function hlcexcel_submit(){
	if(hlcexcel_check()!=''){
		return false;
	}else{
		document.getElementById("hlcexcel_data").value=JSON.stringify(hlcexcel_getdata());
		//alert(document.getElementById("hlcexcel_data").value);
		return true;
	}
}

</script>


<?php
//引用该页的页面得提供两个数组$hlcexcel_list和$hlcexcel_data
//$hlcexcel_list 定义表结构
//$hlcexcel_data 定义初始数据，每行保证有个key项

//form 返回 hlcexcel_data 和 hlcexcel_delid
//hlcexcel_data提供要修改或添加的行，存在key的行就是要修改的
//hlcexcel_delid 提供要删除的key

//注意，在该页面之前，不要有纯字符输出(但可以有标签)，否则会导致一些排版错乱问题，如菜单不能变色，单元格选中框右侧被盖，

?>
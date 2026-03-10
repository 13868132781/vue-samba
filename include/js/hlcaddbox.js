//name : 全局标识名
//str  ：相同则覆盖的列 如 0_2   则0列和2列相同就覆盖
//funcs：修改时，填回后执行的函数  如 aaa;;ccc   则填第0个和第2个值后执行对应的函数aaa()和ccc()
function hlcaddbox(name,str,funcs){
	this.tname=name;
	this.trcount=0;
	this.colortr="";
	if(str!='') this.myArray=str.split("_");
	this.funcs=funcs;
}


hlcaddbox.prototype.hlcaddbox_init = function(arr){	
	if(arr=="")
		return;
	var table=document.getElementById(this.tname+"_list");
	var rows=arr.split("^");
	for(var i=0;i<rows.length;i++){
		trc = table.insertRow();
		trc.id=this.tname+"_tr_"+this.trcount;
		var rectname=this.tname;
		trc.onclick=function (){hlcaddbox.chachar(rectname,this);}
		trc.className="main_listtable_celltr";
		this.trcount++;	
		var cells=rows[i].split("~");
		for(var j=0;j<table.rows[0].cells.length-2;j++){
			if(!cells[j])
				continue;
			var obj=document.getElementById(this.tname+"_val_"+j);
			var thistd=trc.insertCell();
			thistd.abbr=cells[j];
			if(obj&&obj.tagName=='SELECT'){
				obj.value=cells[j];
				thistd.innerHTML=obj.options[obj.options.selectedIndex].text;
			}else if(obj&&obj.tagName=='INPUT'&&obj.type=='text'){
				thistd.innerHTML=cells[j];
				obj.value="";
			}
		}
		trc.insertCell().innerHTML="<img onClick=\"hlcaddbox.hlcaddbox_mod('"+this.tname+"','"+trc.id+"','"+this.funcs+"')\" src='../include/image/10.gif' width='16' height='16' border='0'/>";
		trc.insertCell().innerHTML="<img onClick=\"hlcaddbox.hlcaddbox_del('"+this.tname+"','"+trc.id+"')\" src='../include/image/13.gif' width='16' height='16' border='0'/>";
	}
	
}


hlcaddbox.prototype.hlcaddbox_add = function(){
	var table=document.getElementById(this.tname+"_list");
	var equ=0;
	var trc="";
	
	for (var i=1;i<table.rows.length;i++){
		if(!this.myArray){
			break;
		}
		equ=1;
    	for(var p=0;p<this.myArray.length;p++){
			var obj=document.getElementById(this.tname+"_val_"+p);
			if(obj.value!=table.rows[i].cells[this.myArray[p]].abbr){
				equ=0;
				break;
			}
		}
		if(equ==1){
			trc = table.rows[i];
			while(trc.cells.length){
				trc.deleteCell(trc.cells.length-1);
			}
			break;
		}
	}
	
	if(!trc){
		trc = table.insertRow();
		trc.id=this.tname+"_tr_"+this.trcount;
		var rectname=this.tname;
		trc.onclick=function (){hlcaddbox.chachar(rectname,this);}
		this.trcount++;
	}
	
    
	for (var i=0;i<table.rows[0].cells.length-2;i++){
		var obj=document.getElementById(this.tname+"_val_"+i);
		var thistd=trc.insertCell();
		thistd.abbr=obj.value;
		if(obj&&obj.tagName=='SELECT'){
			thistd.innerHTML=obj.options[obj.options.selectedIndex].text;
		}else if(obj&&obj.tagName=='INPUT'&&obj.type=='text'){
			thistd.innerHTML=obj.value;
			obj.value="";
		}
	}
	trc.insertCell().innerHTML="<img onClick=\"hlcaddbox.hlcaddbox_mod('"+this.tname+"','"+trc.id+"','"+this.funcs+"')\" src='../bth_images/user/10.gif' width='16' height='16' border='0'/>";
	trc.insertCell().innerHTML="<img onClick=\"hlcaddbox.hlcaddbox_del('"+this.tname+"','"+trc.id+"')\" src='../bth_images/user/13.gif' width='16' height='16' border='0'/>";
}


hlcaddbox.hlcaddbox_mod=function(tname,trn,funcs){
	var modfuncs=funcs.split(";");
	var table=document.getElementById(tname+"_list");
	var trc=document.getElementById(trn);
	for (var i=0;i<table.rows[0].cells.length-2;i++){
		var obj=document.getElementById(tname+"_val_"+i);
		obj.value=trc.cells[i].abbr;
		if(modfuncs[i]){
			try{  
				if(typeof(eval(modfuncs[i]))=="function")  
				{
					eval(modfuncs[i]+"('"+tname+"')");
				}
			}catch(e){
				//alert("not function"); 
			} 	
		}
	}
}

hlcaddbox.hlcaddbox_del=function(tname,trn){
	var table=document.getElementById(tname+"_list");
	var trc=document.getElementById(trn);
	table.deleteRow(trc.rowIndex);
}


hlcaddbox.chachar=function(tname,self){  
	var table=document.getElementById(tname+"_list");
	for (var i=1;i<table.rows.length;i++){
		table.rows[i].style.background="";
	}
	self.style.background="#00FF00";		
}


hlcaddbox.prototype.getitems=function(){
	var table=document.getElementById(this.tname+"_list");
	var itemres="";
	for (var i=1;i<table.rows.length;i++){
		var itemrow="";
		for (var j=0;j<table.rows[0].cells.length-2;j++){
			if(itemrow==""){
				itemrow=table.rows[i].cells[j].abbr;
			}else{
				itemrow+="~"+table.rows[i].cells[j].abbr;
			}
		
		}
		if(itemrow!=""){
			if(itemres==""){
				itemres=itemrow;
			}else{
				itemres+="^"+itemrow;
			}
		}
	}
	var itemsobj=document.getElementById(this.tname+"_items");
	if(itemsobj.value==""){
		itemsobj.value=itemres;
	}else{
		itemsobj.value+="^"+itemres;
	}
}
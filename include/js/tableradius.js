function table_tab_radius(radius){

	var color;
	thistds=_hlcclassname('td','main_tabtable_tabtr_ungettd');
	for(var i=0;i < thistds.length; i++){
	    thistd=thistds[i];
	    color=window.getComputedStyle?getComputedStyle(thistd,null).borderColor:thistd.currentStyle.borderColor;
		thistd.style.position="relative";
		oDiv=_createradius('lt',radius,color);
		thistd.appendChild(oDiv); 
		oDiv=_createradius('rt',radius,color);
		thistd.appendChild(oDiv);      
	}

	thistds=_hlcclassname('td','main_tabtable_tabtr_gettd');
	for(var i=0;i < thistds.length; i++){
	    thistd=thistds[i];
		thistd.style.position="relative";
		oDiv=_createradius('lt',radius,color);
		thistd.appendChild(oDiv); 
		oDiv=_createradius('rt',radius,color);
		thistd.appendChild(oDiv);       
	}	

	thistds=_hlcclassname('td','main_tabtable_tabtr_splittd');
	for(var i=0;i < thistds.length; i++){
		if(navigator.userAgent.indexOf("MSIE") !=-1){
			thistds[i].style.display="block"; 
		}else{
		   	thistds[i].style.display="table-cell";      
		}
	}
}


function table_list_radius(radius){
	tables=_hlcclassname('table','main_listtable');
	
	for(var i=0;i < tables.length; i++){
		tobj=tables[i];
		//tobj=document.getElementById('mytable');
		//if(!Raphael.vml){
		//if(navigator.userAgent.indexOf("MSIE")==-1){
		//	tobj.style.borderRadius=radius+'px';
		//	tobj.rows[0].cells[0].style.borderRadius=radius+'px '+radius+'px 0 0';
		//	tobj.rows[tobj.rows.length-1].cells[tobj.rows[0].cells.length-1].style.borderRadius='0 0 '+radius+'px '+radius+'px';
		//	continue;
		//}
		
		

		
		var color=window.getComputedStyle?getComputedStyle(tobj,null).borderColor:tobj.currentStyle.borderColor;
		
		oDiv=_createradius('lt',radius,color);
		thistd=	tobj.rows[0].cells[0];
		thistd.style.position="relative";
	    thistd.appendChild(oDiv);
		
		oDiv=_createradius('rt',radius,color);
		thistd=	tobj.rows[0].cells[tobj.rows[0].cells.length-1]
		thistd.style.position="relative";
		thistd.style.paddingRight=(radius+2)+"px";
	    thistd.appendChild(oDiv);
		
		oDiv=_createradius('lb',radius,color);
		thistd=	tobj.rows[tobj.rows.length-1].cells[0]
		thistd.style.position="relative";
		thistd.style.borderLeftWidth="0px";
		if(thistd.innerHTML=='') thistd.innerHTML="&nbsp;";
	    thistd.appendChild(oDiv);
		 
		oDiv=_createradius('rb',radius,color);
		thistd=	tobj.rows[tobj.rows.length-1].cells[tobj.rows[tobj.rows.length-1].cells.length-1]
		thistd.style.position="relative";
		if(thistd.innerHTML=='') thistd.innerHTML="&nbsp;";
	    thistd.appendChild(oDiv);
	}
}

function _createradius(p,radius,color){

	var outpx="-2px";
	var fix=0;
	if(navigator.userAgent.indexOf("MSIE") ==-1){//非IE
		fix=2;
	}


	var oDiv = document.createElement('div');
	//oDiv.style.backgroundColor="#0f0";
	oDiv.style.boxSizing="border-box";
	oDiv.style.position="absolute";
	oDiv.style.padding="0px";
	oDiv.style.right=outpx;oDiv.style.bottom=outpx;
	oDiv.style.width='20px';
	oDiv.style.height='20px';
    var paper = Raphael(oDiv, 20, 20);

	if(p=='lt'){
		x=0,y=0;
		oDiv.style.left=outpx;oDiv.style.top=outpx;
    	paper.path(
	    	['M', x+radius+fix, y+fix, 'c', 0,0 , radius*-1, 0, radius*-1,radius ,
	      	'h',-1, 'v', (radius+1)*-1,'h',radius+1,'v',1,'Z']
	    ).attr({'fill':'#fff','stroke':'#fff','stroke-width':0});
	    paper.path(
	    	['M', x+radius+fix, y+fix, 'c', 0,0 , radius*-1, 0, radius*-1,radius]
	    ).attr({'stroke':color,'stroke-width':1});
	    paper.path(
	    	['M', x+radius+fix, y+fix, 'c', 0,0 , radius*-1, 0, radius*-1,radius]
	    ).attr({'stroke':color,'stroke-width':1});
    }else if(p=='rt'){
    	x=17,y=0;
        oDiv.style.right=outpx;oDiv.style.top=outpx;
        paper.path(
	    	['M', x-radius+fix/2, y+fix, 'c', 0,0 , radius, 0, radius,radius ,
	    	'h',1, 'v', (radius+1)*-1,'h',(radius+1)*-1,'v',1,'Z']
	    ).attr({'fill':'#fff','stroke-width':0});
	    paper.path(
	    	['M', x-radius+fix/2, y+fix, 'c', 0,0 , radius, 0, radius,radius]
	    ).attr({'stroke':color,'stroke-width':1});
	    paper.path(
	    	['M', x-radius+fix/2, y+fix, 'c', 0,0 , radius, 0, radius,radius]
	    	).attr({'stroke':color,'stroke-width':1});
    }else if(p=='lb'){
    	x=0,y=17;
    	oDiv.style.left=outpx;oDiv.style.bottom=outpx;
       	paper.path(
	   		['M', x+fix, y-radius+fix/2, 'c', 0,0 , 0,radius, radius,radius , 
	    	'v',1, 'h', (radius+1)*-1,'v',(radius+1)*-1,'h',1,'Z']
	    ).attr({'fill':'#fff','stroke-width':0});
	    paper.path(
	    	['M', x+fix, y-radius+fix/2, 'c', 0,0 , 0,radius, radius,radius]
	    ).attr({'stroke':color,'stroke-width':1});
	    paper.path(
	    	['M', x+fix, y-radius+fix/2, 'c', 0,0 , 0,radius, radius,radius]
	    ).attr({'stroke':color,'stroke-width':1});
    }else if(p=='rb'){
    	x=17,y=17;
        oDiv.style.right=outpx;oDiv.style.bottom=outpx;
        paper.path(	
	      	['M', x+fix/2, y-radius+fix/2, 'c', 0,0 , 0, radius, radius*-1,radius ,
	       	'v',1, 'h', radius+1,'v',(radius+1)*-1,'h',-1,'Z']
	    ).attr({'fill':'#fff','stroke-width':0});
	    paper.path(
	   		['M', x+fix/2, y-radius+fix/2 , 'c', 0,0 , 0, radius, radius*-1,radius]
	    ).attr({'stroke':color,'stroke-width':1});//不知为何，这个会不显示
	    paper.path(
	    	['M', x+fix/2, y-radius+fix/2 , 'c', 0,0 , 0, radius, radius*-1,radius]
	    ).attr({'stroke':color,'stroke-width':1});
		paper.path(
	    	['M', x+fix/2, y-radius+fix/2 , 'c', 0,0 , 0, radius, radius*-1,radius]
	    ).attr({'stroke':color,'stroke-width':1});
    }
	return oDiv;
}


function _hlcclassname(tagName,className){
    if(document.getElementsByClassName){ 
    	return document.getElementsByClassName(className);
    }else{       
    	
    	var tags=document.getElementsByTagName(tagName);
    	
        var tagArr=[];
        for(var i=0;i < tags.length; i++){
            if(tags[i].className == className){
                tagArr[tagArr.length] = tags[i];
            }
        }
        return tagArr;
    }

}


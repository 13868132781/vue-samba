

function boxcheck(obj){//调用时，在checkbox上，onClick='boxcheck(this)'
	var tr_obj=obj.parentNode.parentNode;
	var level=tr_obj.getAttribute('zdy-level');
	if(!level){
		level=tr_obj.children[0].abbr;
	}
	while(tr_obj=sdnextSibling(tr_obj)){
		if(tr_obj.nodeType != 1)
			continue;
		var thislevel=tr_obj.getAttribute('zdy-level');
		//if(!tr_obj.children[0]) alert(tr_obj.outerHTML);
		if(!thislevel) thislevel=tr_obj.children[0].abbr;
		if( thislevel >level){
			var findbox=null;
			if(s(tr_obj,'ti_chkbox')){
				s(tr_obj,'ti_chkbox').checked=obj.checked;
				findbox=s(tr_obj,'ti_chkbox');
			}else{
				organcount=tr_obj.id.split("_")[0];
				document.getElementById(organcount+"_box").checked=obj.checked;
				findbox=document.getElementById(organcount+"_box");
			}
			if(typeof(boxcheck_callback)==="function"){ 
			   boxcheck_callback(findbox);
			}
	  }else{
	      break;
	  }
   }
}






function boxcheck_get(a){//获取checkbox的值，主要是tr下去，取出每个tr的第一个td的id，判断该id前半部分是否是所要取的，然后取出id后半部分
	if(!a['tag'])
		a['tag']='organ';
	if(!a['val'])
		a['val']=true;
	if(!a['jge'])
		a['jge']=',';
	
	if(a['robj']){
		tr_obj=a['tr'];
	}else if(a['rid']){
		tr_obj=document.getElementById(a['rid']);
	}else if(a['tobj']){
		tr_obj=a['tobj'].rows[0];
	}else if(a['tid']){
		tr_obj=document.getElementById(a['tid']).rows[0];
	}else{
		return '-1';	
	}
	
	var res='';
	
	if(!tr_obj) return '-1';
	
	do{
		if(tr_obj.nodeType != 1|| !tr_obj.children[0] ||!tr_obj.children[0].id)
			continue;
		var ids=tr_obj.children[0].id.split("_");
		
		var boxobj=s(tr_obj,'ti_chkbox');
		if(!boxobj){		
			boxobj=document.getElementById(tr_obj.id+"_box");
		}
    	if( ids[0]==a['tag'] && boxobj.checked==a['val'] ){
			if(a['yin']){
				if(res==''){
					res="'"+ids[1]+"'";
				}else{
					res+=a['jge']+"'"+ids[1]+"'";	
				}
			}else{
				if(res==''){
					res=ids[1];
				}else{
					res+=a['jge']+ids[1];	
				}
			}
			if(typeof(boxcheck_get_callback)=="function"){ 
			   boxcheck_get_callback(boxobj,ids);
			}
		}
	}while(tr_obj=sdnextSibling(tr_obj));
	
	if(res=='')
		res='-1';
	
	return res;	
}




/*
$lines0是全局计数
$lines1是第一层计数，并不用在ID等里面，只是对比是否是最后一个，下同
$lines2=0;
$lines3=0;


每一行的<tr>的id唯一计数，
该tr下的元素以此计数加字符唯一标识lines0，
<tr>下的第一个<td>的abbr确定层级,
id存储单元格id，如单位是organ_0,设备tree_ip_1.1.1.1,


<tr id='8'>
   <td addr='3' id='ip_1.1.1.1_status'>
	<input type='chackbox' id='8_box'/>
   </td>
   <td><img id='8_bk'/></td>
   
   
 收合图标的id='lines0_plus'          checkbox的id='lines0_box'
 
 
 

   
   
   */
//用于farr[0]['id']='11';farr[0]['name']='a';farr[0]['id']='23';farr[0]['name']='b'; select.value为 11 23 text为 a b 
function showfather(fid,farr,first,findex,ftag){
	if(!first){
	   var 	first=0;
	}
    var fobj=document.getElementById(fid);
	if(farr){
   	   
	   fobj.length=farr.length+first;
	   
	   
	   
	   var len=first;
	   for   (n=0;n<farr.length;n++)   
       {   
	       if(!ftag||farr[n]['priv']==ftag){
              var op=fobj.options[len];   
              op.value=farr[n]['id'];   
              op.text=farr[n]['name'];
		      if(findex&&op.value==findex){
	             op.selected = true;
		      }
			  len++;
		   }   
       }
	   fobj.length=len;
	}else{
	   fobj.length=first;
	}
}



//用户farr[11]='a';farr[23]='b';select.value 为 11 23  ，text里为 a b
//showfather1('rg_timer',<?php echo json_encode($abc_zdy_timer_lst);?>,1,0,'0');
function showfather1(fid,farr,first,findex,ftag){
    if(!first){
	   var 	first=0;
	}
    var fobj=document.getElementById(fid);
	if(farr){ 
	   var len=first;
	   
	   for (var tmp in farr){
		   fobj.length=len+1;
		   var op=fobj.options[len]; 
		   op.value=tmp;
		   op.text=farr[tmp];
		   if(findex&&op.value==findex){
	             op.selected = true;
		      }
	 	   len++;
	   }
	   fobj.length=len;
	}else{
	   fobj.length=first;
	}
}

//用于farr[0]='a';farr[2]='b';  select里的value就是0 1,text就是a b
function showsimple(fid,farr,first,findex){
	if(!first){
	   var 	first=0;
	}
    var fobj=document.getElementById(fid);
	if(farr){
	   fobj.length=farr.length+first;
	   var len=first;
	   for   (n=0;n<farr.length;n++)   
       {   
           var op=fobj.options[len];   
           op.value=n;   
           op.text=farr[n];
		   if(findex&&op.value==findex){
	          op.selected = true;
		   }
	       len++; 
       }
	   fobj.length=len;
	}else{
	   fobj.length=first;
	}
	
}


function showson(sid,sarr,first,findex,sindex){
	var sobj=document.getElementById(sid);
	sobj.length=sarr[findex].length+first;
	
	for   (n=0;n<first;n++)   
    {   
         var op=sobj.options[n];   
         op.value="-1";   
         op.text="";
		 if(sindex&&op.value==sindex){
	         op.selected = true;
		 }
    }
	
	for   (n=first;n<sobj.length;n++)   
    {   
         var op=sobj.options[n];   
         op.value=sarr[findex][n-first]['id'];   
         op.text=sarr[findex][n-first]['name'];
		 if(sindex&&op.value==sindex){
	         op.selected = true;
		 }
    }
}


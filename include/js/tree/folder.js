function folder(obj){//调用时，在图标上 onClick='folder(this)'
   tr_obj=obj.parentNode.parentNode;
   if(obj.name=='1'){
      //修改点击图标的加减
      var disp='none';
	  obj.name='0';
	  if(obj.src.indexOf('bottom')> -1)
	     obj.src='../include/image/tree/plusbottom.gif';
	  else
	     obj.src='../include/image/tree/plus.gif';
	  
	  //修改开合图标
	  var folder=document.getElementById(tr_obj.id+"_folder")
	  if(folder&&folder.src.indexOf('image/tree/folder')> -1)
	      folder.src="../include/image/tree/folder.gif";
		  
	   //合起来   
	  var level=tr_obj.children[0].abbr;
	  if(!level) level=tr_obj.getAttribute('zdy-level');
      while(tr_obj=sdnextSibling(tr_obj)){
		var thislevel=tr_obj.children[0].abbr;
		if(!thislevel) thislevel=tr_obj.getAttribute('zdy-level');
		if( thislevel >level){
            tr_obj.style.display =disp;
	    }else{
	        break;
	    }
      }	  
   }else{
      //修改点击图标的加减
      var disp='block';
	  obj.name='1';
	  if(obj.src.indexOf('bottom')> -1)
	     obj.src='../include/image/tree/minusbottom.gif';
	  else
	     obj.src='../include/image/tree/minus.gif';
	  
	  
	  //修改开合图标
	  var folder=document.getElementById(tr_obj.id+"_folder")
	  if(folder&&folder.src.indexOf('image/tree/folder')> -1)
	      folder.src="../include/image/tree/folderopen.gif";
	  
	
	  //展开
	  opentree(tr_obj); 
	/*  var level=tr_obj.children[0].abbr;
      while(tr_obj=sdnextSibling(tr_obj)){
         if(tr_obj.children[0].abbr >level){
		    if(tr_obj.children[0].abbr==(Number(level)+1)){
               tr_obj.style.display =disp;
			}else{
			   tr_obj.style.display="none";
			}
	     }else{
	        break;
	     }
      }	 */
	  
   }
   
   
}

function opentree(tr_obj){//一个递归函数，被上面的forder调用
	var level=tr_obj.children[0].abbr;
	if(!level) level=tr_obj.getAttribute('zdy-level');
	var old_obj=tr_obj;
    while(tr_obj=sdnextSibling(tr_obj)){
		var thislevel=tr_obj.children[0].abbr;
		if(!thislevel) thislevel=tr_obj.getAttribute('zdy-level');
         if( thislevel>level){
		    if(thislevel==(Number(level)+1)){
               tr_obj.style.display ="";
			   //在IE9以上，这里写block，居然会错位，td全部往前挤了
			   var obj_plus=document.getElementById(tr_obj.id+"_plus");
			   if(obj_plus&&obj_plus.name=='1')
					tr_obj=opentree(tr_obj);
			}
			old_obj=tr_obj;
	     }else{
	        break;
	     }
    }	
	return  old_obj;
}

//obj  table对象; level 要收放的层级; op: 0为关闭，1为打开 ; al:0为不递归处理，1为递归处理

function folderlevel(obj,level){
	var tr_obj=	obj.rows[0];
	while(tr_obj=sdnextSibling(tr_obj)){
		var thislevel=tr_obj.children[0].abbr;
		if(!thislevel) thislevel=tr_obj.getAttribute('zdy-level');
		
		if( thislevel<= level){//小于的层级全部展开
			tr_obj.style.display="table-row";	
		}else{
			tr_obj.style.display="none";
			
		}
		
		if( thislevel< level){	
			var plus=document.getElementById(tr_obj.id+"_plus");
			if(!plus){continue;}
			plus.name='0';
	  		if(plus&&(plus.src.indexOf('/plus')> -1||plus.src.indexOf('/minus')> -1)){
				plus.src=plus.src.replace('/plus','/minus');
			}
			//修改开合图标
	  		var folder=document.getElementById(tr_obj.id+"_folder");
	  		if(folder&&folder.src.indexOf('/folder')> -1){
	      		folder.src="../include/image/tree/folderopen.gif";
			}
			
			
		}else{//大于的层级全部收缩
			
			var plus=document.getElementById(tr_obj.id+"_plus");
			if(!plus){continue;}
			plus.name='0';
	  		if(plus&&(plus.src.indexOf('/plus')> -1||plus.src.indexOf('/minus')> -1)){
				plus.src=plus.src.replace('/minus','/plus');
			}
			//修改开合图标
	  		var folder=document.getElementById(tr_obj.id+"_folder");
	  		if(folder&&folder.src.indexOf('/folder')> -1){
	      		folder.src="../include/image/tree/folder.gif";
			}
			
		}
	}

}












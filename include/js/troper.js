
var tr_selected_lock="";
var tr_selected_over="";

function over_selected_tr(obj){
	if(tr_selected_over!=''){
		tr_selected_over.style.background ="#FFFFFF";
	}
	tr_selected_over="";
	if(obj!=tr_selected_lock){
		tr_selected_over=obj;
		obj.style.background =over_selected_tr_color;
	}
}


function lock_selected_tr(obj){
	if(tr_selected_lock!=''){
		tr_selected_lock.style.background ="#FFFFFF";
	}
	tr_selected_lock=obj;
	obj.style.background =lock_selected_tr_color;
	
	tr_selected_over="";
}

//用该移动的话，确保tr的第一个td的addr表示level，而该td的id是有下划线，且下划线前面相同的可移动

function move_selected_tr_up(){  
	obj_this=tr_selected_lock;
	if(obj_this==''){
		return;	
	}
	obj_up=obj_this.previousSibling;
	
	if(!obj_up){
		return;	
	}
	
	level_this=obj_this.children[0].abbr;
	level_up=obj_up.children[0].abbr;
	
	if(!level_up||level_this!=level_up){
		return;	
	}  
	
	id_this=obj_this.children[0].id.split("_")[0];
	id_up=obj_up.children[0].id.split("_")[0];
	if(id_this!=id_up){
		return	
	}
	
	
	swapNode(obj_this,obj_up);   
}   

//使表格行下移，接收参数为链接对象   
function move_selected_tr_down(){  
	obj_this=tr_selected_lock;
	if(obj_this==''){
		return;	
	}
	obj_down=sdnextSibling(obj_this);
	
	if(!obj_down){
		return;	
	}
	
	level_this=obj_this.children[0].abbr;
	level_down=obj_down.children[0].abbr;
	
	if(!level_down||level_this!=level_down){
		return;	
	}  
	
	id_this=obj_this.children[0].id.split("_")[0];
	id_down=obj_down.children[0].id.split("_")[0];
	if(id_this!=id_down){
		return	
	}
	
	swapNode(obj_this,obj_down);    
}   



//定义通用的函数交换两个结点的位置   
function swapNode(node1,node2){   
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
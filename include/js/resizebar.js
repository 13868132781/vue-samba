
var resizebar=function(bar,div){
	this.resize_start = false;
	this.resize_md_x = 0;
	//this.resize_md_y = 0;
	this.resize_bar = document.getElementById(bar);
	this.resize_div = document.getElementById(div);
	this.bind();
}
 
resizebar.prototype.bind = function () {
	//鼠标按下
	var myself=this;
	myself.resize_bar.addEventListener('mousedown',function(){
		myself.resize_start = true;
	 
		//获取鼠标按下时坐标
		var m_down_x = event.pageX;//鼠标点离整个页面左上角的距离
		//var m_down_y = event.pageY;
	 
		//获取div坐标
		var dx = myself.resize_bar.offsetLeft;//div离父级容器（这里就是整个页面）左上角的距离
		//var dy = myself.resize_bar.offsetTop;
	 
		//获取鼠标点与div偏移量
		myself.resize_md_x = m_down_x - dx;
		//resize_md_y = m_down_y - dy;

		myself.resize_div.style.display='block';
	});
	 
	   //鼠标移动
	myself.resize_div.addEventListener('mousemove',function(){
		if(!myself.resize_start){return;}
		
		//获取鼠标移动实时坐标
		var m_move_x = event.pageX;//当前鼠标点离左上角距离
		//var m_move_y = event.pageY;
	 
		 //获取新div坐标，鼠标实时坐标 - 鼠标与div的偏移量
		var ndx = m_move_x - myself.resize_md_x;
		//var ndy = m_move_y - resize_md_y;
	 
		 //把新div坐标值赋给div对象
		myself.resize_bar.style.left = ndx+"px";
		 //myself.resize_bar.style.top = ndy+"px";
		document.getElementById("wrap_left").style.width = ndx+"px";
		document.getElementById("wrap_right").style.left = (ndx+5)+"px";
		
	});
		
	myself.resize_div.addEventListener('mouseup',function(){
		myself.resize_start = false;
		myself.resize_div.style.display='none';
	});

}

//new resizebar('resize_bar','resize_div');


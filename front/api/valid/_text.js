export default{
	hint:(item,list)=>{
		var back='';
		var valid = item.valid;
		if(parseInt(valid.min)){
			if(back!=''){back+='，';}
			back+='至少'+valid.min+'个字符';
		}
		if(parseInt(valid.max)){
			if(back!=''){back+='，';}
			back+='最多'+valid.max+'个字符';
		}
		return back;
	},
	check:(item,list)=>{
		var back='';
		var valid = item.valid;
		var len = item.value.length;
		if(parseInt(valid.min) && len<parseInt(valid.min)){
			if(back!=''){back+='，';}
			back+='长度少于'+valid.min;
		}
		if(parseInt(valid.max)&& len>parseInt(valid.max)){
			if(back!=''){back+='，';}
			back+='长度大于'+valid.max;
		}
		return back;
	}
	
}
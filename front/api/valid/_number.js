export default{
	hint:(item,list)=>{
		var back='数字';
		var valid = item.valid;
		if(parseInt(valid.min)){
			if(back!=''){back+='，';}
			back+='大于等于'+valid.min;
		}
		if(parseInt(valid.max)){
			if(back!=''){back+='，';}
			back+='小于等于'+valid.max;
		}
		return back;
	},
	check:(item,list)=>{
		var back='';
		var valid = item.valid;
		
		var val = parseInt(item.value);
		if(val+'' !=item.value){
			back = '包含非数字字符';
		}else{
			if(parseInt(valid.min) && val<parseInt(valid.min)){
				if(back!=''){back+='，';}
				back+='不可小于'+valid.min;
			}
			if(parseInt(valid.max) && val>parseInt(valid.max)){
				if(back!=''){back+='，';}
				back+='不可大于'+valid.max;
			}
			
		}
		
		return back;
	}
	
}
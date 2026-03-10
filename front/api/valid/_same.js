export default{
	hint:(item,list)=>{
		var back='';
		var valid = item.valid;
		var as = valid.as;
		var name='';
		for(var i in list){
			if(list[i].col==as){
				name = list[i].name;
				break;
			}
		}
		back="必须与‘"+name+"’一致";
		return back;
	},
	check:(item,list)=>{
		var back='';
		var valid = item.valid;
		var sameAs = valid.as;
		var name='';
		for(var i in list){
			if(list[i].col==sameAs){
				if(list[i].value!=item.value){
					name = list[i].name;
				}
				break;
			}
		}
		if(name){
			back="与‘"+name+"’的值不一致";
		}
		return back;
	}
	
}
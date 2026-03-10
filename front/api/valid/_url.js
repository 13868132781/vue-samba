export default{
	hint:(item,list)=>{
		var back='';
		return back;
	},
	check:(item,list)=>{
		var back='';
		var val = item.value;
		var myreg = /^(https|http):\/\//;
		if (!myreg.test(val)) {
			back='url不合法';
		}
		return back;
	}
	
}
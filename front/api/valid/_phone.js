export default{
	hint:(item,list)=>{
		var back='11位手机号';
		return back;
	},
	check:(item,list)=>{
		var back='';
		var val = item.value;
		var myreg = /^1[3-9]\d{9}$/;
		if (!myreg.test(val)) {
			back='手机号不合法';
		}
		return back;
	}
	
}
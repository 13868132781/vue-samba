export default{
	hint:(item,list)=>{
		var back='邮箱地址';
		return back;
	},
	check:(item,list)=>{
		var back='';
		var val = item.value;
		var myreg = /^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/;
		if (!myreg.test(val)) {
			back='邮箱不合法';
		}
		return back;
	}
	
}
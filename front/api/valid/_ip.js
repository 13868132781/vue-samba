export default{
	hint:(item,list)=>{
		var back='IP地址';
		return back;
	},
	check:(item,list)=>{
		var back='';
		var val = item.value;
		var myreg = /^(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$/;
		if (!myreg.test(val)) {
			back='ip不合法';
		}
		return back;
	}
	
}
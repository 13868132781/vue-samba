export default{
	hint:(item,list)=>{
		var back='长度为0表示不限制长度';
		return back;
	},
	check:(item,list)=>{
		var back='';
		var val = item.value;
		var cxtys = val.split('_');
		var len = parseInt(cxtys[0]);
		if(len+''!=cxtys[0]){
			back='长度值不合法';
		}
		
		return back;
	}
	
}
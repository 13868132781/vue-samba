export default{
	hint:(item,list)=>{
		var back='';
		return back;
	},
	check:(item,list)=>{
		var back='';
		var val = item.value;
		var options = item.options;
		if (!options[val]) {
			back='值不在选项中';
		}
		return back;
	}
	
}
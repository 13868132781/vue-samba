export default{
	hint:(item,list)=>{
		var back='';
		var back='';
		var val = item.value;
		var valid = item.valid;
		var cxty = valid.cxty||'';
		var cxtys = cxty.split('_');
		
		var len = parseInt(cxtys[0]);
		if(len>0){
			back='长度>='+len;
		}
		
		cxtys.shift();
		
		if(cxtys.length==0){
			return back;
		}
		
		var backa = [];
		var pxword = '';
		if(cxtys[cxtys.length-1]=='xze'){
			cxtys.pop();
			if(cxtys.length==3){
				pxword = '，三选二';
			}else{
				pxword = '，四选三';
			}
		}
		for (var i in cxtys){
			var cx = cxtys[i];
			if(cx=='low'){
				backa.push('小写');
			}
			if(cx=='big'){
				backa.push('大写');
			}
			if(cx=='num'){
				backa.push('数字');
			}
			if(cx=='tes'){
				backa.push('特殊字符');
			}
		}
		back+='，必须包含'+backa.join('，')+pxword;
		
		
		return back;
	},
	check:(item,list)=>{
		var back='';
		var val = item.value;
		var valid = item.valid;
		var cxty = valid.cxty||'';
		var cxtys = cxty.split('_');
		
		var len = parseInt(cxtys[0]);
		if(len>0 && val.length<len){
			back='长度不够';
			return back;
		}
		cxtys.shift();
		
		if(cxtys.length==0){
			return back;
		}
		
		var backa = [];
		var pxnum = 0;
		if(cxtys[cxtys.length-1]=='xze'){
			cxtys.pop();
			pxnum = 1;
		}
		
		for (var i in cxtys){
			var cx = cxtys[i];
			if(cx=='low'){
				var regex=/[a-z]/;
				if(!regex.test(val)){
					backa.push('小写');
				}
			}
			if(cx=='big'){
				var regex=/[A-Z]/;
				if(!regex.test(val)){
					backa.push('大写');
				}
			}
			if(cx=='num'){
				var regex=/[0-9]/;
				if(!regex.test(val)){
					backa.push('数字');
				}
			}
			if(cx=='tes'){
				var regex=/[\~\!\@\#\$\%\^\&\*\<\>\,\.\?\/]/;
				if(!regex.test(val)){
					backa.push('特殊字符');
				}
			}
		}
		if(backa.length>pxnum){
			back='必须再包含'+backa.join('，');
			if(pxnum==1){
				back+='中的'+(backa.length-1)+'项';
			}			
		}
		
		return back;
	}
	
}
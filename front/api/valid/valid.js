
var validObj={};

import valid_text from "./_text.js";
validObj.text = valid_text;

import valid_ip from "./_ip.js";
validObj.ip = valid_ip;


import valid_email from "./_email.js";
validObj.email = valid_email;

import valid_number from "./_number.js";
validObj.number = valid_number;

import valid_password from "./_password.js";
validObj.password = valid_password;

import valid_date from "./_date.js";
validObj.date = valid_date;

import valid_phone from "./_phone.js";
validObj.phone = valid_phone;

import valid_url from "./_url.js";
validObj.url = valid_url;

import valid_same from "./_same.js";
validObj.same = valid_same;

import valid_cxty from "./_cxty.js";
validObj.cxty = valid_cxty;

import valid_option from "./_option.js";
validObj.option = valid_option;


export default{
	hint:function(list){//生成提示信息
		for(var i in list){
			var item = list[i];//对象是指针引用，修改item同时就是修改list[i]
			
			//如果 为undefined，或为null，或为''
			if( !hlc.True(item.value)){
				item.value="";//统一置为 ''
			}
			//存储一下老的数据，方便对比是否修改
			item.oldValue=item.value;
			
			//初始时清空，所以配置里设置是无效的，应该设置hintVal\errMsgVal
			item.hint = '';
			item.errMsg = '';//初始时清空
			
			if(item.type=='show'){
				if(item.hint){
					item.hint+='，';
				}
				item.hint += '只读，不可修改';
			}
			
			var valid = item.valid;
			if(valid && validObj[valid.type]){
				var res = validObj[valid.type].hint(item,list);
				if(res){
					item.hint = res;
				}
			}
			if(item.unique){
				if(item.hint){
					item.hint+='，';
				}
				item.hint += '唯一性';
			}
			
			if(item.hintMore){
				if(item.hint){
					item.hint+='，';
				}
				item.hint += item.hintMore;
			}
			
		}
	},
	
	//onlyinfo是list里的一项，若有，则只检查该项，若没有，检查整个list
	//所有字段，都会返回，即使你没设置
	check:function(list,onlyinfo){
		var mylist = list;
		if(onlyinfo){
			mylist = [onlyinfo];
		}
		
		var back={};
		var getErr=false;
		for(var i in mylist){
			var item = mylist[i];
			item.errMsg = '';
			
			
			//不验证
			//back[item.col] = item.value;
			//continue;
			
			//规整value值，全转为字符串
			//value 为undefined，为null，为''时，设为空
			if(!hlc.True(item.value)){
				item.value = '';
			}
			if(typeof(item.value)=='number'){
				item.value = item.value.toString();
			}
			if(typeof(item.value)!='string'){
				getErr=true;
				item.errMsg = '数据类型不合法';
				continue;
			}
			//如果是整个form全检查的话，每项value要去两空格
			//如果是检查单项的话，就不去除两边空格，因为这是在输入时逐改逐验
			if(!onlyinfo){
				item.value = item.value.trim();
			}
			//到此为止，item.value必然是个字符串
			
			if(item.value===''){
				if(item.ask){
					getErr=true;
					item.errMsg = '不可为空';
					continue;
				//即便允许空，也得看看是不是需要same验证
				}else if(!item.valid || item.valid.type!='same'){
					back[item.col] = '';
					continue;
				}
				
			}
			
			//针对select，radio
			if((item.type=='select'||item.type=='radio') && !item.valid){
				item.valid={type:'option'};
			}
			
			
			
			var valid = item.valid;
			if(valid && validObj[valid.type] ){
				var res = validObj[valid.type].check(item,list);
				if(res){
					getErr=true;
					item.errMsg = res;
				}
			}
			if(!getErr){
				back[item.col] = item.value;
			}
			
		}
		if(!getErr){
			return back;
		}
		return ;
		
	}
	
	
}



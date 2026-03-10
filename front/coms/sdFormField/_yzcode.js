var template = `
<div style="display:flex">
	<div style="flex:1">
		<input ref="input" type="text" style="width:100%;border:0px;padding:10px;outline:none;" :placeholder="info.holder"  v-model="info.value" @input="dochange" @focus="dofocus" @blur="doblur"/>
	</div>
	<div style="flex:1; border-left:1px solid #ccc;background-color:#f0f0f0; display:flex; align-items:center; justify-content:center; cursor: pointer" :style="isfocus?'border-left:1px solid '+theme:''" @click="doclick">
	{{yzmMsg}}
	</div>
</div>
`;

export default{
	template : template,
	props:{
		info:{//里面有router depend 
			default:()=>{return {};}
		},
		infos:{//里面有router depend 
			default:()=>{return {};}
		}
	},
	data(){
		return {
			isfocus:false,
			theme: hlc.config.theme,
			isSending:false,
			sendcount:0,
		}
	},
	computed:{
		yzmMsg(){
			if(this.isSending){
				return '正在发送...';
			}else if(this.sendcount>0){
				return this.sendcount+'秒后重新发送';
			}else{
				return '发送验证码';
			}
		}
	},
	methods:{
		dofocus(){
			this.isfocus = true;
			this.$emit("onFocus",true);
		},
		doblur(){
			this.isfocus = false;
			this.$emit("onFocus",false);
		},
		dochange(){
			this.$emit("onInput",this.$refs.input.value);
		},
		doclick(){
			var router = this.info.router;
			var post ={};
			var need = this.info.need||[];
			for(var i in need){
				let ne = need[i];
				var val ='';
				for(var j in this.infos){
					let inf = this.infos[j];
					if(inf.col==ne){
						if(!inf.value){
							alert('请先输入'+(inf.name||'所需数据'));
							return;
						}
						val = inf.value;
						break;
					}
				}
				
			}
			
			this.sendYzcode(router,post);
				
			
		},
		sendYzcode(router,post){
			if(this.isSending || this.sendcount>0){
				return;
			}
			this.isSending=true;
			var data={};
			hlc.ajax({
				router: router,
				data: post,
				silent: true,
				ok:(res)=>{
					if(res.code==0){
						this.sendcount=60;
						var sto = setInterval(()=>{
							this.sendcount--;
							if(this.sendcount==0){
								clearInterval(sto);
							}
						},1000);
					}else{
						alert('发送失败：'+(res.data||''));
					}
					this.isSending=false;
				}
			});
		}
	}
}
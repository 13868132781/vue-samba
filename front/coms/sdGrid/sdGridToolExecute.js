var template = `
<div style="display:inline-block;">
	<sdButton :showType="jsCtrl.showType" :value="jsCtrl.name" :icon="(jsCtrl.icon||'riqi')" :doing="doing" :options="jsCtrl.btnOptions" @sdClick="doClick" />

</div>
`;
//execute按钮可以有下拉选项，但这只是个参数
//所以下拉项都走的是ajax execute
//并非是向oper那样下拉多功能的集合，要想这样功能的话，
//得用sdGridToolList组件
export default{
	template : template,
	
	props:{
		jsCtrl:{
			default:()=>{
				return {}
			}
		},
		jsCtrls:{
			default:()=>{
				return {}
			}
		},
	},
	data(){
		return {
			doing:0,
		}
	},
	methods:{
		doClick(index){
			if(this.jsCtrl.askSure){
				if(!askSure(this.jsCtrl.askSure)){
					return;
				}
			}
			var router = this.jsCtrl.router;
			var post = hlc.copy(this.jsCtrl.post||{});
			if(typeof index !== 'undefined'){
				post.btnOption = this.jsCtrl.btnOptions[index].id;
			}
			post.execVal={};
			var jsCtrls = this.jsCtrls;
			jsCtrls.map((ho)=>{
				if(ho.type=='input' && ho.inputCol){
					post.execVal[ho.inputCol]=ho.value;
				}
			});
			
			
			this.doing=1;
			hlc.ajax({
				router: router+"@execute",
				post: post,
				silent:true,
				ok:(res)=>{
					//alert(JSON.stringify(res));
					//如果有msg的话，交给msg显示信息，按钮就不显示成功失败了
					//tool上的execute按钮，或许就该全由msg显示，而不该在按钮上显示成功失败
					if(res.msg){
						this.doing=0;
					}else if(res.code==0){
						this.doing=2;;//2 成功 3 失败
					}else{
						this.doing=3;
					}
					if(res.refresh){
						this.$emit('refresh');
					}
				}
			});	
		}
	}
}
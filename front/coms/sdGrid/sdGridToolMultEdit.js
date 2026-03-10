var template = `
<div style="display:inline-block;">
	<sdButton :showType="jsCtrl.showType" :value="jsCtrl.name" :icon="(jsCtrl.icon||'riqi')" @sdClick="doClick" />

</div>
`;

export default{
	template : template,
	
	props:{
		jsCtrl:{
			default:()=>{
				return {}
			}
		},
	},
	
	methods:{
		doClick(){
			var popArgs = hlc.copy(this.jsCtrl);
			popArgs.formType = 'multEdit';
			popArgs.askSure = "确定要修改本页查询出来的 ["+this.jsCtrl.post.fenye.total+"] 条记录么";
			
			hlc.popup.open({
				com:'sdForm',
				name: this.jsCtrl.popTitle,
				width:this.jsCtrl.popWidth||'70%',
				height:this.jsCtrl.popHeight||'70%',
				args: popArgs,
				ok:()=>{
					this.$emit('refresh');
				},
			});
		}
	}
	
}
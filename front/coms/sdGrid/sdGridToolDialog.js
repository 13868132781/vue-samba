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
			
			hlc.popup.open({
				com:'sdDialog',
				name:this.jsCtrl.popTitle,
				width:this.jsCtrl.popWidth||'70%',
				height:this.jsCtrl.popHeight||'70%',
				args:this.jsCtrl,
			});
		}
	}
	
}
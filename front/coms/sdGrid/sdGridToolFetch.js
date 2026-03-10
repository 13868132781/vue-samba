var template = `
<div style="display:inline-block;">
	<sdButton :showType="jsCtrl.showType" :value="jsCtrl.name" :icon="jsCtrl.icon||'riqi'" @sdClick="doClick" />

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
	data(){
		return {
			
		}
	},
	methods:{
		doClick(){
			
			hlc.popup.open({
				com:'sdFetch',
				name:this.jsCtrl.popTitle,
				width:this.jsCtrl.popWidth||'90%',
				height:this.jsCtrl.popHeight||'90%',
				args:this.jsCtrl,
			});	
		}
	}
}
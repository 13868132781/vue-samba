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
			
		}
	}
	
}
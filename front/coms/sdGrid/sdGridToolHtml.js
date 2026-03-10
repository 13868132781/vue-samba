var template = `
<div style="display:inline-block;" v-html="jsCtrl.html">
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
	}
}
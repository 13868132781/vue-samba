var template = `
<div style="display:inline-block;position:relative" v-html="row[jsCtrl.col]">
</div>
`;

export default{
	template : template,
	props:{
		row:{
			default:()=>{
				return {};
			}
		},
		jsCtrl:{
			default:()=>{
				return {};
			}
		}
		
	}
	
}
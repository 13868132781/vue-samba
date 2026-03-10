var template = `
<div style="display:inline-block;position:relative;cursor:pointer">
	<sdIcon type="fujian" size="13" color="#385E0F" />
	<span @click="doClick()" style="color:#385E0F">{{jsCtrl.name}}</span>
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
			window.open(this.jsCtrl.linkUrl);
			
		}
	}
}
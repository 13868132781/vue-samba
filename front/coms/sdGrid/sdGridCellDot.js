var template = `
<div style="display:inline-block; width:10px; height:10px; border-radius:5px; margin-right:2px" :style="'background-color:'+getColor">
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
		row:{
			default:()=>{
				return {}
			}
		},
	},
	computed:{
		getColor(){
			var val = this.row[this.jsCtrl.col];
			if(!this.jsCtrl.dotMap[val]){
				return this.jsCtrl.dotMap['_default_'];
			}
			return this.jsCtrl.dotMap[val];
		}
	}
}
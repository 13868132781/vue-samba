var template = `
<div style="display:inline-block; ">
	<sdIcon type="shougongqianshou" @sdClick="doClick" />
</div>
`;

import sdFetch from "../sdFetch.js";

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
		gridSet:{
			default:()=>{
				return {}
			}
		}
	},
	methods:{
		doClick(){
			var popArgs = hlc.copy(this.jsCtrl);
			
			hlc.popup.open({
				com:"sdUpload",
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
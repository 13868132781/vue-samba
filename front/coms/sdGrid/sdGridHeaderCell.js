var template = `
<div style="display:inline-block; white-space: nowrap; ">
	<span v-if="mytype=='text'">{{jsCtrl.name}}</span>
	<sdGridHeaderCellBatch v-if="mytype=='headBatch'" :jsCtrl="jsCtrl" :gridData="gridData" />
</div>
`;

import sdGridHeaderCellBatch from "./sdGridHeaderCellBatch.js"

export default{
	template : template,
	components:{sdGridHeaderCellBatch},
	props:{
		jsCtrl:{
			default:()=>{
				return {}
			}
		},
		gridData:{
			default:()=>{return {};}
		},
	},
	data(){
		return {
			
		}
	},
	computed:{
		mytype(){
			var type='text';
			if(this.jsCtrl.headBatch){
				type = 'headBatch';
			}
			return type;
		}
		
	},
	methods:{
	}
}
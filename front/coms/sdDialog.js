var template = `
<div style="padding:10px;background-color:#f0f0f0;">
	<sdbody :menu="args" :inDlg="true"/>
</div>
`;

import sdbody from "../index/body.js";
export default{
	template : template, 
	components:{sdbody},
	props:{
		args:{
			default:()=>{return {};},
		}
	},
	data(){
		return {
			
		}
	},
	created(){
		this.args.noTitle=true;
	},
	methods:{
	}
}
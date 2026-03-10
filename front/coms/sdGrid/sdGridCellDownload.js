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
			var url = hlc.entry+"?router="+this.jsCtrl.router+"@download&filePath="+this.row['dl_file']+"&fileName="+this.row['dl_name'];
			window.location.href=url;
			
			/*
			var post = hlc.copy(this.jsCtrl.post);
			post.auditOper+="删除";
			hlc.ajax({
				router: this.jsCtrl.router+"@download",
				post: post,
				//silent:true,
				ok:(res)=>{
					if(res.code){
						alert(JSON.stringify(res));
					}else{
						this.$emit('onOk','');//触发pop的确定按钮
						//this.myData = res.data;
					}
				}
			});	
			
			*/
		}
	}
}
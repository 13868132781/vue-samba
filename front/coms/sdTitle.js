//暂时没用到

var template = `
<div v-if="topshow" style="padding-bottom:10px;overflow:hidden;zoom:1;">
	<div style="float:left;font-size:16px;">
		{{(info.name+(info.title||''))}}
	</div>
	<div style="float:left; margin-left:30px; font-size:12px">
		{{info.search||''}}
	</div>
</div>
`


export default{
	
	template : template,
	props:{
		info:{
			default:()=>{return{
				name:'',
				exname:'',
				search:'',
			}},
		}
		
	},
	
}
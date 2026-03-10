var template = `
<div style="padding:10px;height:100%;background-color:#f0f0f0">
	{{info.value}}&nbsp;
</div>
`;

export default{
	template : template,
	props:{
		info:{
			default:{},
		}
	},
	data(){
		return {
			
		}
	},
	methods:{
	}
}
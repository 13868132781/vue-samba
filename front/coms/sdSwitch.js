var template = `
<div >
	<div v-for="item,index in myForm">
		{{item.name}}
	</div>
</div>
`;

export default{
	template : template,
	props:{
		args:{
			default:{}
		}
	},
	data(){
		return {
			tabList:[],
		}
	},
	mounted(){
		
	},
	methods:{
	
	}
}
var template = `
<div >
	<table>
		<tr>
			<td>角色：</td>
			<td>{{myRole}}</td>
		</tr>
		
		<tr v-for="etho,ethn in myEth">
			<td>{{ethn}}</td>
			<td>
				<table>
				
				</table>
			</td>
		</tr>
	</table>
</div>
`;
export default{
	template : template,
	props:{
		args:{
			default:()=>{
				return {};
			},
		}
	},
	data(){
		return {
			myData:null,
			myRole:'',
			myEth:[],
		}
	},
	created(){
		this.fetchData();
	},
	methods:{
		fetchData(){
			hlc.ajax({
				router: "/sysFailover/failover@gridData",
				post: {},
				silent:true,
				ok:(res)=>{
					//alert(JSON.stringify(res));
					this.myData = res.data;
					this.myRole = res.data.role;
					this.myEth = JSON.parse(res.data.eth);
				}
			});	
			
		}
	}
}
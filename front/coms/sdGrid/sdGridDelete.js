var template = `
<div style="display:inline-block;">
	<sdButton :value="name" icon="shanchu" :doing="doing" @sdClick="doClick" />

</div>
`;
export default{
	template : template,
	
	props:{
		router:{
			default:''
		},
		post:{
			default:()=>{return {};}
		},
		gridData:{
			default:()=>{
				return [];
			}
		},
		fenyeInfo:{
			default:()=>{
				return [];
			}
		}
	},
	data(){
		return {
			name:'批量删除',
			doing:0,
		}
	},
	methods:{
		doClick(index){
			var num = 0;
			if(this.gridData){
				num = this.gridData.length;
			}
			if(this.fenyeInfo && this.fenyeInfo.total){
				num = this.fenyeInfo.total;
			}
			
			if(!confirm('确定要删除查询出来的 '+num+' 条数据么')){
				return;
			}
			if(!confirm('再次询问，确定要删除查询出来的 '+num+' 条数据么')){
				return;
			}
			
			var router = this.router;
			var post = hlc.copy(this.post||{});
			
			this.doing=1;
			hlc.ajax({
				router: router+"@crudDelete",
				post: post,
				silent:true,
				ok:(res)=>{
					//alert(JSON.stringify(res));
					if(res.code==0){
						this.doing=2;;//2 成功 3 失败
					}else{
						this.doing=3;
					}
					//if(res.refresh){
					this.$emit('refresh');
					//}
				}
			});	
		}
	}
}
var template = `
<div >
	{{myData}}
</div>
`;
export default{
	template : template,
	props:{
		args:{
			default:{},
		}
	},
	data(){
		return {
			myData:null,
		}
	},
	created(){
		this.fetchData();
	},
	methods:{
		fetchData(){
			this.$emit('onDoing','1');
			hlc.ajax({
				router: this.args.router+"@fetch",
				post: this.args.post,
				silent:true,
				ok:(res)=>{
					//alert(JSON.stringify(res));
					this.$emit('onDoing','0');
					this.myData = res.data;
				}
			});	
			
		}
	}
}
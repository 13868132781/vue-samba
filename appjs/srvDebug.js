var template = `
<div>
	<div v-html="myData"></div>
	<div style="position:absolute;right:20px;top:10px">
		<sdButton value="刷新" icon="xuanzhuan" @click="doClick" />
	</div>
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
		fetchData(doing){
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
			
		},
		doClick(){
			this.fetchData();
		}
	}
}
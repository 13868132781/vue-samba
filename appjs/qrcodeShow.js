var template = `
<div >
	<div style="text-align:center" style="line-height:0px;">
	<img border="0" id="showqrcode" :src="'data:image/png;base64,'+myData" style="" />
	</div>
	<div style="text-align:center">
		<button @click="fetchData(true)">更换种子</button>
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
		this.fetchData(false);
	},
	methods:{
		fetchData(refresh){
			if(refresh){
				if(!confirm("更换种子，手机APP里的用户将失效，需重新扫描二维码，确定要更换种子么？")){
					return;
				}
			}
			this.$emit('onDoing','1');
			var post = hlc.copy(this.args.post);
			post.refresh = refresh;
			hlc.ajax({
				router: this.args.router+"@fetch",
				post: post,
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
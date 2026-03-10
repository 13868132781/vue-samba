var template = `
<div >
	<div style="text-align:center">
	
	</div>
	<div style="text-align:center;padding:40px">
	密码:<input  v-model="passwd"/>
	</div>
	<div style="text-align:center">
		<button @click="fetchData(true)">登录测试</button>
	</div>
	<div style="text-align:center;padding-top:50px">
		<span :style="'color:'+(myCode?'#f00':'#00f')">{{myData}}</span>
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
			myCode:0,
			myData:null,
			passwd:'',
		}
	},
	created(){
		//this.fetchData(false);
	},
	methods:{
		fetchData(refresh){
			this.$emit('onDoing','1');
			var post = hlc.copy(this.args.post);
			post.refresh = refresh;
			post.pass = this.passwd;
			hlc.ajax({
				router: this.args.router+"@fetch",
				post: post,
				silent:true,
				ok:(res)=>{
					//alert(JSON.stringify(res));
					this.$emit('onDoing','0');
					this.myCode  = res.code;
					this.myData = res.data;
				}
			});	
			
		}
	}
}
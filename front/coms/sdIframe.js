var template = `
<div style="background:#fff;">
	<iframe ref="myIframe" :src="getSrc()" style="width:100%;border:0px;height:85%" />
</div>
`;
export default{
	template : template,
	props:{
		router:{
			default:'',
		},
		post:{
			default:{},
		},
		iframeSrc:{
			default:'',
		},
	},
	data(){
		return {
			myData:null,
			myName:'',
			myIp:'',
			myDomain:'',
			myPass:'',
		}
	},
	created(){
		//this.fetchData(false);
		this.$nextTick(() => {
			var iframe = this.$refs.myIframe;
			iframe.addEventListener('load', ()=>{
				//var iframe = this.$refs.myIframe;
				var iframeDoc = iframe.contentWindow.document;
				iframe.style.height = iframeDoc.body.scrollHeight + 'px';
			});
		});
		
		
		
	},
	methods:{
		getSrc(){
			if(this.iframeSrc.indexOf('http')!=-1){
				return this.iframeSrc;
			}else{
				return '/php'+this.iframeSrc;
			}
		},
		
		fetchData(refresh){
			this.myName= this.myName.trim();
			this.myIp= this.myIp.trim();
			this.myDomain= this.myDomain.trim();
			this.myPass= this.myPass.trim();
			
			if(this.myName==''){
				alert('请输入本机名称');
				return;
			}
			if(this.myIp==''){
				alert('请输入本机IP');
				return;
			}
			if(this.myDomain==''){
				alert('请输入域名');
				return;
			}
			if(this.myPass==''){
				alert('请输入管理员密码');
				return;
			}
			
			if(!confirm('初始化将删除现有系统上所有数据，重新设定所有域控信息')){
				return;
			}
			
			
			
			//this.$emit('onDoing','1');
			var post = {};//hlc.copy(this.args.post);
			post.refresh = refresh;
			post.myName = this.myName;
			post.myIp = this.myIp;
			post.myDomain = this.myDomain;
			post.myPass = this.myPass;
			post.goto = 'adInit';
			post.auditOper = '初始化';
			hlc.ajax({
				router: "/sdLdap/adServer@fetch",
				post: post,
				//silent:true,
				ok:(res)=>{
					//alert(JSON.stringify(res));
					//this.$emit('onDoing','0');
					this.myData = res.data;
				}
			});	
			
		}
	}
}
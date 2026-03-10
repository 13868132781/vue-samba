var template = `
<div style="background:#fff;box-shadow:0px 0 2px 1px #aaa;border-radius:3px">
	
	<div v-if="loginCode" style="text-align:center">
		<div style="text-align:center;padding:40px">
			开发密码：<input  v-model="loginPass"/>
		</div>
		<div style="text-align:center">
			<button @click="loginTo()">打开初始化界面</button>
		</div>
		<div style="color:#f00;padding:40px">{{loginMsg}}.</div>
		<div style="color:#f00;padding:20px">域控初始化将清空系统上现有的所有的数据<br/>非开发或部署人员，请勿操作</div>
	</div>
	
	<div v-if="!loginCode">
		<div style="text-align:center;padding:40px">
		本机名称:<input  v-model="myName" style="margin-right:20px"/>
		本机IP:<input  v-model="myIp" style="margin-right:20px"/>
		域名:<input  v-model="myDomain" style="margin-right:20px"/>
		域控管理员密码:<input  v-model="myPass"/>
		</div>
		<div style="text-align:center">
			<button @click="fetchData(true)">执行初始化</button>
		</div>
		<div style="text-align:left;padding:20px;padding-top:50px">
			<div style="color:#f00">{{(myCode?'执行出错，过程被中断！！！！！':'')}}</div>
			<div v-for="datao in myData" style="margin-bottom:20px">
				<div style="font-weight:bold">cmd# {{datao.cmd}}</div>
				<div :style="datao.code?'color:#f00':''" v-html="datao.msg"></div>
			</div>
		</div>
	</div>
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
	},
	data(){
		return {
			loginCode:1,
			loginPass:'',
			loginMsg:'',
			
			myCode:0,
			myData:null,
			myName:'',
			myIp:'',
			myDomain:'',
			myPass:'',
		}
	},
	created(){
		//this.fetchData(false);
	},
	methods:{
		loginTo(){
			var post = {};//hlc.copy(this.args.post);
			post.loginPass = this.loginPass;
			post.goto = 'adInitReq';
			post.auditOper = '打开初始化界面';
			this.loginMsg='';
			hlc.ajax({
				router: "/sdLdap/adServer@fetch",
				post: post,
				//silent:true,
				ok:(res)=>{
					//alert(JSON.stringify(res));
					//this.$emit('onDoing','0');
					this.loginCode=res.code;
					if(res.code){	
						this.loginMsg="开发密码错误";
					}
				}
			});	
			
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
			post.loginPass = this.loginPass;
			post.refresh = refresh;
			post.myName = this.myName;
			post.myIp = this.myIp;
			post.myDomain = this.myDomain;
			post.myPass = this.myPass;
			post.goto = 'adInitExec';
			post.auditOper = '执行初始化';
			hlc.ajax({
				router: "/sdLdap/adServer@fetch",
				post: post,
				//silent:true,
				ok:(res)=>{
					//alert(JSON.stringify(res));
					//this.$emit('onDoing','0');
					this.myCode = res.code;
					this.myData = res.data;
				}
			});	
			
		}
	}
}
var template = `
<div style="position:fixed ; left:0; right: 0 ;top: 0; bottom: 0;display:flex; justify-content:center; align-items:center; " :style="'background:url('+loginbg+') no-repeat;background-size:100% 100% '">
	<div style="width:500px; background-color:rgba(0,0,0,0.7); border-radius:3px; box-shadow: 0 0 4px 2px #ccc; padding:40px; position:relative;padding-top:120px">
		
		<div style="
			position:absolute; left:-100px;right:-100px;top:20px; 
			padding-top:10px;padding-bottom:10px;margin-bottom:20px;
			text-align:center;font-size:28px;font-weight:bold;color:#666;
			background:linear-gradient(to right,rgba(255,255,255,0),rgba(255,255,255,0.8),rgba(255,255,255,0.8),rgba(255,255,255,0);
			text-shadow:0 0 3px #fff;letter-spacing:2px" 
		:style="'color:'+theme">
			{{title}}
		</div>
		
		
		<sdFormField 
			v-for="item,index  in myInfos"
			:key="index"
			:info="item"
			:infos="myInfos" 
			style="margin-bottom:30px;color:#fff" />
		
		
		<sdFormSubmit @click="submit()" :islogin="islogin" />
		
		
		<div v-if="errMsg" style="padding:10px;text-align:center;color:#f00;font-size:12px">
			{{errMsg}}
		</div>
		
	</div>
</div>
`;

export default{
	template : template,
	data(){
		return {
			title: hlc.config.title,
			theme: hlc.config.theme,
			loginbg: hlc.config.loginbg||'',
			islogin:false,
			errMsg:'',
			
			myInfos:[
				{type:'text',col:'user',value:'',
				holder:'输入用户名',icon:'user',
				name:'用户名',noClear:true, 
				},
				{type:'password',col:'pass',value:'',
				holder:'输入密码',icon:'pass',
				name:'',noClear:true, 
				},
				/*
				{type:'yzcode',col:'code',value:'',holder:'输入验证码(暂时不用)',
					noTitle:true,
					icon:'',
					router:'/_auth/auth@yzcode',
					need:['user','pass'],noClear:true,
				},
				*/
			],
		}
	},
	created(){
		document.onkeydown = (e) => {
			let _key = window.event.keyCode;
			//!this.clickState是防止用户重复点击回车
			if (_key === 13&&!this.clickState) {
				this.submit();
			}
		};
	},
	computed:{
		yzinfo(){
			return {
				args:{
					router:'/_auth/auth@yzcode',
					col:'info',
					name:'用户名密码'
				}
			};
		}
	},
	methods:{
		submit(){
			var valuser = this.myInfos[0].value;
			var valpass = this.myInfos[1].value;
			//var valcode = this.myInfos[2].value;
			if(!valuser){
				alert('请输入用户名');
				return;
			}
			if(!valpass){
				alert('请输入密码');
				return;
			}
			if(this.islogin){
				return;
			}
			this.islogin=true;
			this.errMsg = '';
			hlc.ajax({
				router:"sys/auth/auth@login",
				silent:true,
				post:{
					user: valuser,
					pass: valpass,
					//code: valcode,
				},
				ok:(res)=>{
					if(res.code==0){
						window.location.reload();
						return;
						//这里不return的话，下面this.islogin=false也会执行
						//在跳转过程中，登录按钮会恢复,造成迷惑
					}else{
						this.errMsg = res.data;
					}
					this.islogin=false;
				}
			});
			
		}
	}
}
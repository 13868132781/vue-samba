var template = `
<div style="
	height:100%;
	display:flex;
	flex-direction: row;
	align-items: center;
	color: #fff;
	" :style="'background-color:'+theme">
	
	<div style="margin-left:10px" @click="sysModeChange(true)">
		<img :src="logo" style="height:34px" />
	</div>
	
	<div style="font-size:20px;margin-left:10px;color:#fff;text-shadow:0 0 3px #000;">{{title}}</div>
	
	<div style="flex:1;display:flex;margin-left:20px;">
		<div v-if="sysMode" style="color:#f00;cursor:pointer;text-shadow:0 0 2px #000;" @click="sysModeChange(false)">系统处于开发模式<br/>点此关闭开发模式</div>
	</div>
	
	<div style="display:flex;margin-right:10px;align-items: center;">
		<div v-for="item,index in service" style="margin-right:3px;padding:5px;border:1px solid #ccc;background-color:rgba(255,255,255,0.0);  border-radius:3px;display:flex; align-items:center;cursor:pointer"  @click="menuJumpServ()">
			<sdIcon :type="item.onoff_start?'tongyi':'bohui'" color="#fff" style="margin-right:3px;border-radius:8px" :style="'background-color:'+(item.onoff_start?'#367517':'#f00')"/>
			<span style="color:#fff">{{item.sv_name}}</span>
		</div>
	</div>
	
	<div style="display:flex;margin-right:10px;align-items: center;">
		<div style="position:relative;display:inline-block;cursor:pointer"  @click="menuJumpAlarm()">
			<sdIcon type="gaojing2" color="#fff" size="24"/>
			<div v-if="alarmCount>0" style="position:absolute; right:-3px; top:-3px; border-radius:8px;text-align:center; min-width:14px;font-size:10px;background-color:#f00;padding-left:3px;padding-right:3px;color:#fff;border:1px solid #fff">{{alarmCount}}</div>
		</div>
	</div>
	
	<div style="margin-right:10px;cursor:pointer" @click="menuJumpAdmin()">
		<div style="text-align:right;color:#fff">{{authInfo.sl_acctname}}</div>
		<div style="text-align:right;color:#fff">{{authInfo.sl_acctuser}}</div>
	</div>
	
	<div style="display:flex;margin-right:10px;align-items: center;" title="退出">
		<div style="position:relative;display:inline-block;cursor:pointer"  @click="doLogout()">
			<sdIcon type="logout4" color="#fff" size="20"/>
		</div>
	</div>
	
</div>


`;

var style=`
.test{
	font-size:20px;
}
`;

export default{
	template : template,
	props:{
		value:{
			default:'',
		}
	},
	data(){
		return {
			title: hlc.config.title,
			logo: hlc.config.logo,
			theme: hlc.config.theme,
			sysMode: parseInt(hlc.config.mode),
			
			service:[],
			
			authInfo:{},
			
			runCrons:[],
			
			alarmCount:0,
		}
	},
	created(){
		this.getService();
		this.getSysInfo();
		this.getAlarmCount();
		
		//服务页面启停服务，触发此事件
		hlc.$on('onServiceUpdate',(e)=>{
			this.getService();
		});
		
		hlc.$on('onAlarmCountUpdate',(e)=>{
			this.getAlarmCount();
		});
		
		//每分钟检查一次
		setInterval(()=>{
			this.getService();
			this.getAlarmCount();
		},120*1000);
		
	},
	methods:{
		getService(){
			hlc.ajax({
				router:"sys/service/service@gridData",
				post:{'isPollingRequest': true},
				silent:true,
				ok:(res)=>{
					if(res.code==0){
						this.service = res.data;
					}
				}
			});
		},
		getSysInfo(){
			hlc.ajax({
				router:"sys/auth/auth@getInfo",
				silent:true,
				ok:(res)=>{
					if(res.code==0){
						this.authInfo = res.data;
					}
				}
			});
		},
		
		getAlarmCount(){
			hlc.ajax({
				router:"sys/sysAlarm/sysAlarm@fetch",
				post:{goto:'notRead','isPollingRequest': true},
				silent:true,
				ok:(res)=>{
					if(res.code==0){
						this.alarmCount = res.data;
					}
				}
			});
		},
		
		menuJumpServ(){
			hlc.$emit('onMenuJump','系统服务管理');
		},
		menuJumpAdmin(){
			hlc.$emit('onMenuJump','系统用户管理');
		},
		menuJumpAlarm(){
			hlc.$emit('onMenuJump','系统告警管理');
		},
		doLogout(){
			hlc.ajax({
				router:"sys/auth/auth@logout",
				ok:(res)=>{
					if(res.code==0){
						window.location.reload();
					}else{
						alert(res.data);
					}
				}
			});
		},
		sysModeChange(n){
			
			if(n){
				if(this.sysMode){
					return;
				}
				hlc.popup.open({
					name: "输入开发密码",
					width:'400px',
					height:'auto',
					args: {
						formList:[
							{col:'devpass',type:'password',noTitle:true}
						]
					},
					ok:(back)=>{
						this.doSysMode(n,back.devpass);
					}
				});
				
				/*
				var htpass = prompt("请输入后台密码:", "");
				if(htpass){
					this.doSysMode(n,htpass);
				}
				*/
			}else{
				this.doSysMode(n);
			}
		},
		doSysMode(n,htpass){
			hlc.ajax({
				router:"sys/auth/auth@sysMode",
				post:{mode:n,htpass:htpass},
				ok:(res)=>{
					if(res.code==0){
						window.location.reload();
					}else{
						alert(res.data);
						this.sysModeChange(n);
					}
				}
			});
		},
		
	}
	
}
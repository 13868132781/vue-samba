var template = `
<div style="background-color:#f0f0f0;position:fixed; left:0;right:0;top:0;bottom:0">
	<div v-if="authed=='no'">
		<sdAuth/>
	</div>
	
	<div  v-if="authed=='yes'">
		<div style="position:fixed;top:50px;bottom:0px;left:200px;right:0">
			<sdbody v-if="menu" :menu="menu" :key="menucs" />
		</div>
		
		<div style="position:fixed;left:0;top:50px;bottom:0;width:200px; border-right:0px solid #ccc; box-shadow:0px 0 2px 1px #aaa;">
			<sdmenu :menuList="menuList" @menuclick="menuclick" />
		</div>
		
		<div style="position:fixed; left:0; top:0; height:50px; right:0; box-shadow:0 1px 2px 1px #aaa">
			<sdhead />
		</div>
	</div>

	<sdPopup ref="mySdPopup" />
	<sdCaidan ref="mySdCaidan" />
	<sdLoading />
	<sdNetError />
	<sdReqMsg />
</div>
`;

//hong 

import sdmenu from "./menu.js";
import sdbody from "./body.js";
import sdhead from "./head.js";
import sdAuth from "./auth.js";

export default{
	template : template,
	components:{sdmenu,sdbody,sdhead,sdAuth},
	data(){
		return{
			authed:'',
			
			menuList:[],
			menu:"",
			menucs:0,
		}
	},
	created(){
		//本系统以宽1536为标准设计各种布局尺寸,
		//1536是1920的0.8倍，是1920放大1.25倍的效果
		var zoom = window.screen.width/1536;
		document.body.style.zoom = zoom;
		
		this.getInit(); 
		
		//每分钟检查一次
		setInterval(()=>{
			if(this.authed=='yes'){
				//alert(document.cookie);
				this.checkAuth();
			}
		},120*1000);
	},
	mounted(){
		hlc.popup = this.$refs.mySdPopup;
		hlc.caidan = this.$refs.mySdCaidan;
	},
	methods:{
		getInit(){
			hlc.ajax({
				router:"sys/auth/auth@init",
				silent:true,//不显示loading提示
				ok:(res)=>{
					for(var k in res.data){
						if(k=='menuList'){
							continue;
						}
						if(k=='title'){
							document.title = res.data[k];
						}
						if(k=='icon'){
							var link = document.querySelector("link[rel*='icon']");
							link.href = res.data[k];
						}
						hlc.config[k] = res.data[k];
					}
					if(res.code==0){
						this.menuList = res.data.menuList;
						this.authed='yes';
					}else{
						this.authed='no';//这是唯一切进login的地方
					}
				}
			});
		},
		
		checkAuth(){
			hlc.ajax({
				router:"sys/auth/auth@cronCheck",
				silent:true,//不显示loading提示
				'isPollingRequest': true,
				ok:(res)=>{
					if(res.code==0){
						this.authed='yes';
					}else {
						window.location.reload();
					}
				}
			});
		},
		
		menuclick(menu){//alert(JSON.stringify(menu));
			this.menu = menu;
			this.menucs++;
		}
	}
	
}
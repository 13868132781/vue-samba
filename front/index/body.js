var template = `
<div style="position:absolute; left:0;right:0;top:0;bottom:0;">

	<div v-if="midyInfo" style="position:absolute;top:0; bottom:0; background-color:#fafafa; display:flex; flex-direction:column; box-shadow:0px 0 2px 1px #aaa; overflow:auto;"  :style="midyWrapStyle" class="myscroll">
		<div style="flex:1;position:relative">
			<sdMidy  :router="midyInfo"  @midclick="midclick" :islookForAll="islookForAll" />
		</div>
	</div>
		
	<div v-if="menu.tabList" style="height:40px;background:#fff;box-shadow:0 0px 2px 1px #aaa; position:sticky; left:0px; top:0px; right:0px; display:flex; padding-left:20px;z-index:10;" :style="tabWrapStyle">
		<div v-for="item,index in menu.tabList" @click="tabClick(index,item)" style=" height:100%; margin-left:10px;margin-right:10px; font-size:16px; cursor:pointer; display:flex; align-items:center;position:relative" :style="tabIndex==index?'font-weight:bold;color:'+theme:''">
			{{item.name}}
			<div style="position:absolute;left:0;right:0;bottom:5px;height:2px;" :style="tabIndex==index?'background-color:'+theme:''"></div>
		</div>
	</div>
		
	
	<!--这里设置position:absolute,本来不需要flex，但为了实现中间部分min-height，才使用-->
	<!--如此才能把copyright压到底部-->
	<!--padding-bottom:0px，把位置留给copyright-->
	<div ref="comwrap" style="position:absolute; left:0; right:0; top:0px; bottom:0px; padding:10px;padding-bottom:0px; overflow:auto;display:flex; flex-direction:column;" :style="comWrapStyle"  class="myscroll">
		
		<div v-if="!this.menu.noTitle" style="padding-bottom:10px;overflow:hidden;zoom:1; flex:0 0 30px" >
			<div style="float:left;">
					
				<span v-if="menu.name" style="font-size:14px;color:#888">
					{{menu.name}}
				</span>
				<span v-if="menu.info" style="font-size:12px;color:#888">
					({{menu.info}})
				</span>
					
				<span v-if="midyname"  style="font-size:14px;color:#888"> >> </span>
				<span v-if="midyname" style="font-size:14px;color:#888">
					{{midyname}}
				</span>
					
				<span v-if="tabItem.name"  style="font-size:14px;color:#888"> >> </span>
				<span v-if="tabItem.name" style="font-size:14px;color:#888">
					{{tabItem.name}}
				</span>
				<span v-if="tabItem.info" style="font-size:12px;color:#888">
					({{tabItem.info}})
				</span>
					
			</div>
		</div>
			
		<div v-if="!menu.tabList" style="flex:1">
			<sdPage v-if="!midyInfo||midyid" :myArgs="menu" :key="midycs" 
				:midyid="midyid"  
				@refresh="refresh()"  
				@onLookForAll="doLookForAll" 
				/>
		</div>
			
		<div v-if="menu.tabList"  style="flex:1">
			<div v-for="item,index in menu.tabList">
			<sdPage v-if="(!midyInfo||midyid)&&tabIndex==index" :myArgs="getItem(item)" :key="midycs" 
				:midyid="midyid"  
				@refresh="refresh()" 
				@onLookForAll="doLookForAll" 
				/>
			</div>
		</div>
			
		<div style="flex:0 0 12px;text-align:right; padding-right:560px;">
			<span v-if="!inDlg" style="font-size:12px;color:#888;">copyright © 2009-2025 by softdomain</span>
		</div>
			
	</div>
</div>
`;



export default{
	
	template : template,
	props:{
		menu:{
			default:()=>{
				return {};
			},
		},
		inDlg:{
			default:false,
		}
	},
	data(){
		return {
			theme: hlc.config.theme,
			
			tabItem:(this.menu.tabList?this.menu.tabList[0]:{}),
			tabIndex:0,
			
			midycs:0,
			midyid:'',
			midyname:'',
			midyInfo: this.menu.midy,
			
			comwrapHeight:0,//目前没用到
			
			isMidyOpen:false,//当下真实宽度
			midyWidth: this.menu.midyWidth||200,
			
			islookForAll:false,
		}
	},
	computed:{
		midyWrapStyle(){
			return "width:"+this.midyWidth+"px;";
		},
		tabWrapStyle(){
			var style = "";
			if(this.midyInfo){
				style += "left:"+this.midyWidth+"px;";
			}
			return style;
		},
		comWrapStyle(){
			var style = "";
			if(this.midyInfo){
				style += "left:"+this.midyWidth+"px;";
			}
			if(this.menu.tabList){
				style += ";top:40px";
			}
			return style;
		},
	},
	mounted(){
		this.comwrapHeight = this.$refs.comwrap.offsetHeight;
		if(this.midyInfo){
			this.isMidyOpen = true;	
		}
	},
	methods:{
		doLookForAll(b){
			this.islookForAll = b;
		},
		getItem(item){
			var itemn = {
				router : this.menu.router,
				post : hlc.copy(this.menu.post||{}),
			};
			itemn = hlc.merge(itemn,item);
			//alert(JSON.stringify(itemn));
			return 	itemn;
		},
		
		midclick(item){
			this.midyid = item.id;
			this.midyname = item.name;
			this.midycs++;
		},
		
		tabClick(index,item){
			this.tabIndex = index;
			this.tabItem = item;
			this.midycs++;
		}
	}
}
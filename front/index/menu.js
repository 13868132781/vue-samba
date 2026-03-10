var template=`
<div style="position:absolute;left:0;top:0px;bottom:0;right:0;overflow:auto;padding:3px;background-color:#000;padding-top:10px;padding-bottom:20px" class="myscrollw myscroll_thin">
	<div v-for="item,index in menuList" ref="menuone">
		<div @click="menuclick(item,index)" style="cursor:pointer; padding:5px; border-bottom:1px solid #222;border-radius:3px"  :style="menuid==index?'background-color:'+theme:''" >
			<table style="width:100%" cellspace=0 cellpadding=0>
				<tr>
					<td style="width:20px">
						<sdIcon :type="item.icon||'fuzhi'" size="14" color="#fff" />
					</td>
					<td :style="menuid==index?' font-weight:bold':''"  style="color:#fff">{{item.name}}</td>
					<td style="width:20px">
						<sdIcon :type="icontype(item,index)" size="18" color="#ccc" />
					</td>
				</tr>
			</table>
		</div>
		
		<div v-if="item.kids" style="overflow:hidden; transition:height 300ms" :style="'height:'+(item.openkids||'0')+'px'" ref="menukids">
			<div v-for="item1,index1 in item.kids">
				<div @click="menuclick(item1,index+'-'+index1)" style="cursor:pointer; padding:5px; padding-left:20px;; border-bottom:1px solid #222;border-radius:3px" :style="menuid==(index+'-'+index1)?' background-color:'+theme:''">
					<table style="width:100%" cellspace=0 cellpadding=0>
						<tr>
							<td style="width:20px">
								<sdIcon :type="item1.icon||'fuzhi'" size="14" color="#fff"/>
							</td>
							<td :style="menuid==(index+'-'+index1)?' font-weight:bold':''" style="color:#fff">{{item1.name}}</td>
							
							<td style="width:20px">
								<sdIcon :type="icontype(item1,index+'-'+index1)" size="18" color="#ccc" />
							</td>
							
						</tr>
					</table>
				</div>
			</div>
		</div>
		
	</div>
</div>
`;


export default{
	template : template,
	props:{
		
	},
	data(){
		return {
			menuid:'',
			menuList:[],
			menuJump:{},
			theme: hlc.config.theme,
		}
	},
	created(){
		hlc.ajax({
				router:"sys/auth/auth@getMenu",
				silent:true,//不显示loading提示
				ok:(res)=>{
					this.menuList = res.data;
					this.menuclick(this.menuList[0],0);
				}
		});
		
		//在head.js里触发跳转
		hlc.$on('onMenuJump',(e)=>{
			var menuname = e.detail;
			var one = this.menuJump[menuname];
			if(one){
				this.menuclick(one[0],one[1]);
			}else{
				alert('未找到菜单名：'+menuname);
			}
		});
	},
	mounted(){
		
	},
	methods:{
		icontype(item,index){
			this.menuJump[item.name]=[item,index];
			
			if(!item.kids){
				return 'jiantou_liebiaoxiangyou_o';
			}
			if(item.openkids){
				return 'jiantou_yemian_xiangxia_o';
			}else{
				return 'jiantou_yemian_xiangyou_o';
			}
		},
		menuclick(item,index){
			if(item.kids){
				var ok = this.menuList[index].openkids||0;
				if(ok==0){
					var height = this.$refs['menuone'][0].offsetHeight;
					ok = item.kids.length*height;
				}else{
					ok=0;
				}
				this.menuList[index].openkids=ok;
				
				return;//有子项的话，改项不响应路由
			}
			
			//这里不判断，到sdPage里自行选择
			//if(!item.com && !item.router && !item.tabList){
			//	return;
			//}
			
			this.menuid = index;
			this.$emit("menuclick",item);
		}
		
	},
	
}
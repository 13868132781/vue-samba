var template=`
<div style="position:absolute;left:0;top:0px;bottom:0;right:0;overflow:auto;padding:3px;background-color:#fff" class="myscroll myscroll_thin">
	<div v-for="item,index in menuList">
		<div @click="menuclick(item,index)" style="cursor:pointer; padding:5px; border-bottom:1px solid #eee;" >
			<table style="width:100%" cellspace=0 cellpadding=0>
				<tr>
					<td style="width:20px">
						<sdIcon :type="item.icon||'fuzhi'" size="14" />
					</td>
					<td :style="menuid==index?'color:'+theme+'; font-weight:bold':''">{{item.name}}</td>
					<td style="width:20px">
						<sdIcon :type="icontype(item)" size="18" color="#ccc" />
					</td>
				</tr>
			</table>
		</div>
		
		<div v-if="item.kids" style="padding-left:20px;overflow:hidden; transition:height 300ms" :style="'height:'+(item.openkids||'0')+'px'" ref="menukids">
			<div v-for="item1,index1 in item.kids">
				<div @click="menuclick(item1,index+'-'+index1)" style="cursor:pointer; padding:5px; border-bottom:1px solid #eee" >
					<table style="width:100%" cellspace=0 cellpadding=0>
						<tr>
							<td style="width:20px">
								<sdIcon :type="item1.icon||'fuzhi'" size="14" />
							</td>
							<td :style="menuid==(index+'-'+index1)?'color:'+theme+'; font-weight:bold':''">{{item1.name}}</td>
							
							<td style="width:20px">
								<sdIcon :type="icontype(item1)" size="18" color="#ccc" />
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
		menuList:{
			default:()=>{return {};}
		}
	},
	data(){
		return {
			menuid:'',
			theme: hlc.config.theme,
		}
	},
	methods:{
		icontype(item){
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
					ok = item.kids.length*33;
				}else{
					ok=0;
				}
				this.$set(this.menuList[index],'openkids',ok)
				
				return;//有子项的话，改项不响应路由
			}
			
			if(!item.com && !item.router && !item.tabList){
				return;
			}
			
			this.menuid = index;
			this.$emit("menuclick",item);
		}
		
	},
	mounted(){
		setTimeout(()=>{
			this.menuclick(this.menuList[0],0);
		},100);
		
	}
}
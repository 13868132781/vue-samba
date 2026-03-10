/*
这是个按钮集合，一个按钮，点击下拉，每项，都是单独的type
这个跟sdGridExport概念不同，
sdGridExport只是简单的利用了sdButton的下拉来确定某一个参数值
而且由于sdGridExport有下拉框，所以放在这里，点击是无效的，看以后改进吧
*/
var template = `
<div style="display:inline-block;" :propsUpdate="propsUpdateInit">
	<sdButton :showType="jsCtrl.showType" :value="jsCtrl.name" :icon="jsCtrl.icon||'riqi'" :isList="true" :options="myListOptions" @sdClick="doClick" />

	
	<div v-show="false" >
		<div v-for="item,index in myListOptions">
			
			<sdGridDelete ref="listone" v-if="item.type=='delete'" :router="router" :post="post" :gridSet="gridSet" :gridData="gridData"  :fenyeInfo="fenyeInfo" @refresh="refreshReal()"/>
			
			<sdGridImport ref="listone" v-if="item.type=='import'" :router="router" :post="post" :gridSet="gridSet" :gridData="gridData"  :fenyeInfo="fenyeInfo" />
			
			<sdGridExport ref="listone" v-if="item.type=='export'" :router="router" :post="post" :gridSet="gridSet" :gridData="gridData"  :fenyeInfo="fenyeInfo" exall="true" />
			
			
			<sdGridToolOutCrudMod ref="listone" v-if="item.type=='crudMod'" :jsCtrl="item" />
			
			<sdGridToolDialog ref="listone" v-if="item.type=='dialog'" :jsCtrl="item"  />
			
			<sdGridToolExecute ref="listone" v-if="item.type=='execute'" :jsCtrl="item" :jsCtrls="jsCtrls" />
			
			<sdGridToolOutEdit ref="listone" v-if="item.type=='batch'" :jsCtrl="item" :gridData="gridData"  />
			
			<sdGridToolMultEdit ref="listone" v-if="item.type=='multEdit'" :jsCtrl="item"  :gridData="gridData"  @refresh="$emit('refresh')"/>
			
		</div>
	</div>
</div>
`;

import sdGridDelete from "./sdGridDelete.js"
import sdGridImport from "./sdGridImport.js"
import sdGridExport from "./sdGridExport.js"
import sdGridToolOutCrudMod from "./sdGridToolOutCrudMod.js"
import sdGridToolDialog from "./sdGridToolDialog.js"
import sdGridToolExecute from "./sdGridToolExecute.js"
import sdGridToolOutEdit from "./sdGridToolOutEdit.js"
import sdGridToolMultEdit from "./sdGridToolMultEdit.js"

export default{
	template : template,
	components:{
		sdGridDelete,sdGridImport,sdGridExport,
		sdGridToolOutCrudMod,sdGridToolDialog,
		sdGridToolExecute,sdGridToolOutEdit,sdGridToolMultEdit
	},
	props:{
		router:{
			default:''
		},
		post:{
			default:()=>{return {};}
		},
		gridSet:{
			default:()=>{return {};}
		},
		gridData:{
			default:()=>{return {};}
		},
		fenyeInfo:{
			default:()=>{return {};}
		},
	},
	data(){
		return {
			jsCtrl:{},
			myListOptions:[],
		}
	},
	created(){
		this.jsCtrl={
			name:'更多操作',
			listOptions: hlc.copy(this.gridSet.toolPliangExpands||[]),
		};
		if(this.gridSet.toolPliangImportEnable){
			this.jsCtrl.listOptions.unshift({
				name:'批量导入',
				type:'import',
				icon:'daoru'
			});
		}
		if(this.gridSet.toolPliangExportEnable){
			this.jsCtrl.listOptions.unshift({
				name:'批量导出',
				type:'export',
				icon:'daochu'
			});
		}
		if(this.gridSet.toolPliangDeleteEnable){
			this.jsCtrl.listOptions.unshift({
				name:'批量删除',
				type:'delete',
				icon:'shanchu'
			});
		}
	},
	computed:{
		propsUpdateInit(){
			this.myListOptions = hlc.copy(this.jsCtrl.listOptions);
			for(var i in this.myListOptions){
				let itemjsCtrl = this.myListOptions[i];
				var myjsCtrl = {
					router : this.router,
					post : hlc.copy(this.post),
				};
				//合并myjsCtrl和jsCtrl
				myjsCtrl = hlc.merge(myjsCtrl,this.jsCtrl);
				//合并myjsCtrl和jsCtrl
				myjsCtrl = hlc.merge(myjsCtrl,itemjsCtrl);
				
				var titleName = myjsCtrl.popTitle||myjsCtrl.name;
				myjsCtrl.popTitle = titleName;
				myjsCtrl.post.fenye = this.fenyeInfo;
				//console.log(JSON.stringify(myjsCtrl));
				this.myListOptions[i] = myjsCtrl;
			};
		}
	},
	methods:{
		
		doClick(index){
			this.$refs.listone[index].doClick();
			
		},
		
	}
	
}
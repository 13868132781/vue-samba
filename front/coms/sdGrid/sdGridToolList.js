/*
这是个按钮集合，一个按钮，点击下拉，每项，都是单独的type
这个跟sdGridExport概念不同，
sdGridExport只是简单的利用了sdButton的下拉来确定某一个参数值
*/
var template = `
<div style="display:inline-block;" :propsUpdate="propsUpdateInit">
	<sdButton :showType="jsCtrl.showType" :value="jsCtrl.name" :icon="jsCtrl.icon||'riqi'" :isList="true" :options="myListOptions" @sdClick="doClick" />

	<div v-show="false" >
		<div v-for="item,index in myListOptions">
			
			<sdGridDelete ref="listone" v-if="item.type=='delete'" :router="myjsCtrl.router" :post="myjsCtrl.post" :gridSet="gridSet" :gridData="gridData"  :fenyeInfo="myjsCtrl.post.fenye" @refresh="$emit('refresh')"/>
			
			<sdGridImport ref="listone" v-if="item.type=='import'" :router="myjsCtrl.router" :post="myjsCtrl.post" :gridSet="gridSet" :gridData="gridData"  :fenyeInfo="myjsCtrl.post.fenye" />
			
			<sdGridExport ref="listone" v-if="item.type=='export'" :router="myjsCtrl.router" :post="myjsCtrl.post" :gridSet="gridSet" :gridData="gridData"  :fenyeInfo="myjsCtrl.post.fenye" exall="true" />
			
			
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
		jsCtrl:{
			default:()=>{
				return {}
			}
		},
		jsCtrls:{
			default:()=>{
				return {}
			}
		},
		gridSet:{
			default:()=>{
				return {}
			}
		},
		gridData:{
			default:()=>{
				return {}
			}
		},
	},
	data(){
		return {
			myjsCtrl:{},
			myListOptions:[],//this.jsCtrl.listOptions,
		}
	},
	created(){
		
	},
	computed: {
		propsUpdateInit() {
			
			this.myjsCtrl=hlc.copy(this.jsCtrl);
			
			if(this.myjsCtrl.toolImportEnable){
				this.myjsCtrl.listOptions.unshift({
					name:'批量导入',
					type:'import',
					icon:'daoru'
				});
			}
			if(this.myjsCtrl.toolExportEnable){
				this.myjsCtrl.listOptions.unshift({
					name:'批量导出',
					type:'export',
					icon:'daochu'
				});
			}
			if(this.myjsCtrl.toolDeleteEnable){
				this.myjsCtrl.listOptions.unshift({
					name:'批量删除',
					type:'delete',
					icon:'shanchu'
				});
			}
			
			this.myListOptions = hlc.copy(this.myjsCtrl.listOptions);
			for(var i in this.myListOptions){
				let itemjsCtrl = this.myListOptions[i];
				let myjsCtrl = hlc.copy(this.myjsCtrl);
				delete myjsCtrl.listOptions;
				//合并myjsCtrl和jsCtrl
				myjsCtrl = hlc.merge(myjsCtrl,itemjsCtrl);
				
				var titleName = itemjsCtrl.popTitle||itemjsCtrl.name||this.myjsCtrl.popTitle;
				myjsCtrl.popTitle = titleName;
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
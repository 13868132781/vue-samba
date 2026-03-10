/*
:jsCtrl是合并后的项，已经是新建的了
:jsCtrly 是原始的项，修改完内容，会影响jsCtrls，用于input
:jsCtrls 所以项目，用于提起其他组件的值，用户execute提交
*/

var template = `
<div style="display:inline-block;position:relative" :propsUpdate="propsUpdateInit">
	
	<sdGridToolOutCrudMod v-if="myjsCtrl.type=='crudMod'" :jsCtrl="myjsCtrl" />
	
	<sdGridToolOutEdit v-if="myjsCtrl.type=='batch'" :jsCtrl="myjsCtrl" :gridData="gridData" />
	
	<sdGridToolDialog v-if="myjsCtrl.type=='dialog'" :jsCtrl="myjsCtrl" />
	
	<sdGridToolInput v-if="myjsCtrl.type=='input'" :jsCtrl="myjsCtrl" :jsCtrly="jsCtrl" />
	
	<sdGridToolUpload v-if="myjsCtrl.type=='upload'" :jsCtrl="myjsCtrl" />
	
	<sdGridToolDownload v-if="myjsCtrl.type=='download'" :jsCtrl="myjsCtrl" />
	
	<sdGridToolExecute v-if="myjsCtrl.type=='execute'" :jsCtrl="myjsCtrl" :jsCtrls="jsCtrls" @refresh="$emit('refresh')" />
	
	<sdGridToolHtml v-if="myjsCtrl.type=='html'" :jsCtrl="myjsCtrl" />
	
	<sdGridToolLink v-if="myjsCtrl.type=='link'" :jsCtrl="myjsCtrl" />
	
	<sdGridToolFetch v-if="myjsCtrl.type=='fetch'" :jsCtrl="myjsCtrl" />
	
	<sdGridToolMultEdit v-if="myjsCtrl.type=='multEdit'" :jsCtrl="myjsCtrl"  :gridData="gridData"  @refresh="$emit('refresh')"/>
	
	<sdGridToolList v-if="myjsCtrl.type=='list'" :jsCtrl="myjsCtrl" :jsCtrls="jsCtrls" :gridData="gridData" @refresh="$emit('refresh')"/>
	
</div>
`;

import sdGridToolOutCrudMod from "./sdGridToolOutCrudMod.js"
import sdGridToolOutEdit from "./sdGridToolOutEdit.js"

import sdGridToolDialog from "./sdGridToolDialog.js"
import sdGridToolExecute from "./sdGridToolExecute.js"
import sdGridToolLink from "./sdGridToolLink.js"
import sdGridToolHtml from "./sdGridToolHtml.js"
import sdGridToolList from "./sdGridToolList.js"
import sdGridToolInput from "./sdGridToolInput.js"
import sdGridToolUpload from "./sdGridToolUpload.js"
import sdGridToolDownload from "./sdGridToolDownload.js"
import sdGridToolFetch from "./sdGridToolFetch.js" 
import sdGridToolMultEdit from "./sdGridToolMultEdit.js" 

export default{
	template : template,
	components:{
		sdGridToolOutCrudMod,sdGridToolOutEdit,
		sdGridToolDialog,sdGridToolExecute,
		sdGridToolLink,sdGridToolHtml,sdGridToolList,
		sdGridToolInput,sdGridToolUpload,sdGridToolDownload,
		sdGridToolFetch,sdGridToolMultEdit
	},
	props:{
		gridInfo:{
			default:()=>{return {};}
		},
		router:{
			default:''
		},
		post:{
			default:()=>{return {};}
		},
		jsCtrl:{
			default:()=>{return {};}
		},
		jsCtrls:{
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
			myjsCtrl:{},
			
			oldInfo: hlc.copy(this.info),
			myRouter: this.router,
			myPost: hlc.copy(this.post),
		}
	},
	created(){
		
	},
	computed:{
		propsUpdateInit(){
			//这段本来放created，但post等数据更新时，执行不到
			//所以放到computed里,
			//computed里同步修改数据，不会死循环，异步修改可能死循环
			
			//把jsCtrl置为初始数据，把router post放进jsCtrl里
			this.myjsCtrl = {
				router : this.router,
				post : hlc.copy(this.post),
			};
			
			
			//合并myjsCtrl和jsCtrl
			this.myjsCtrl = hlc.merge(this.myjsCtrl,this.jsCtrl);
			
			this.myjsCtrl.popTitle = this.myjsCtrl.popTitle||this.myjsCtrl.name;
			
			this.myjsCtrl.post.fenye = this.fenyeInfo;
				
		}
	},
	methods:{
	}
}
var template = `
<td v-if="gridSet.operEnable" 
				style="
					width:30px;
					cursor:pointer;"
				align="middle"
				@click="doClick"
>
<div v-if="operExpands.length>0" style="height:100%; width:100%;text-align:center; position:relative">
	<sdIcon type="gengduo"/>
	<!--这里得用v-show，否则点击后组件消失，不能回传refresh事件-->
	<div v-show="isoptionOpen" 
		style="
			position:absolute;z-index:100;
			right:20px; 
			background-color:#fff; 
			box-shadow:0 0 2px 1px #aaa;"
		:style="operStyle"
	>
	
		<div v-for="item,index in operExpands" style="padding:5px;
		padding-left:10px;padding-right:10px;word-break:keep-all; white-space:nowrap;text-align:left"
		@mouseenter="mouseenter(index)" 
		@mouseleave="mouseleave(index)"
		:style="'background-color:'+(isoptionHover==index?'#eee':'#fff')"
		 @click="itemClick(getJsCtrl(item),index)" 
		>
			<sdIcon :type="item.icon||'shougongqianshou'" size="13"/>
			<span style="cursor:pointer" >{{item.name}}</span>
		</div>
		
		
		<div v-show="false">
		<div v-for="item,index in operExpands">
			
			<!--如果子组件根div设置了display:inline-block，而使用子组件时设置了@事件-->
			<!--此时在子组件上用v-show，可能会失效-->
			<!--可用 style="display:none"代替v-show="false"-->
			
			<sdGridCellEdit :ref="'listone_'+index" v-if="item.type=='edit'" :jsCtrl="getJsCtrl(item)" :row="row" :gridSet="gridSet" @refresh="$emit('refresh')"/>
			
			<sdGridCellDialog :ref="'listone_'+index" v-if="item.type=='dialog'" :jsCtrl="getJsCtrl(item)" :row="row" :gridSet="gridSet" />
			
			<sdGridCellExecute :ref="'listone_'+index" v-if="item.type=='execute'" :jsCtrl="getJsCtrl(item)" :row="row" :gridSet="gridSet"/>
		
			<sdGridCellLink :ref="'listone_'+index" v-if="item.type=='link'" :jsCtrl="getJsCtrl(item)" :row="row" :gridSet="gridSet"/>
			
			<sdGridCellFetch :ref="'listone_'+index" v-if="item.type=='fetch'" :jsCtrl="getJsCtrl(item)" :row="row" :gridSet="gridSet" @refresh="$emit('refresh')"/>
			
		</div>
		</div>
		
	</div>
	
</div>

</td>
`;

import sdGridCellEdit from "./sdGridCellEdit.js"
import sdGridCellDialog from "./sdGridCellDialog.js"
import sdGridCellLink from "./sdGridCellLink.js"
import sdGridCellExecute from "./sdGridCellExecute.js"
import sdGridCellFetch from "./sdGridCellFetch.js"

export default{
	template : template,
	components:{
		sdGridCellEdit,sdGridCellDialog,
		sdGridCellExecute,sdGridCellLink,sdGridCellFetch
	},
	props:{
		router:{
			default:''
		},
		post:{
			default:()=>{return {};}
		},
		row:{
			default:()=>{return {};}
		},
		rowindex:{
			default:0,
		},
		gridSet:{
			default:()=>{return {};}
		},
		gridData:{
			default:()=>{return [];}
		},
	},
	data(){
		return {
			isoptionOpen:false,
			isoptionHover:-1,
			
			operExpands:[],
			
		}
	},
	created(){
		//alert(JSON.stringify(this.gridSet.operExpands));
		this.operExpands=hlc.copy(this.gridSet.operExpands||[]);
		
		var operDelEnable = this.gridSet.operDelEnable;
		if('_operDelEnable_' in this.row){
			operDelEnable = this.row['_operDelEnable_'];
		}
		if(operDelEnable){
			this.operExpands.unshift({
				name:'删除',
				type:'del',
				icon:'shanchu'
			});
		}
		
		var operModEnable = this.gridSet.operModEnable;
		if('_operModEnable_' in this.row){
			operModEnable = this.row['_operModEnable_'];
		}
		if(operModEnable){
			this.operExpands.unshift({
				name:'修改',
				type:'mod',
				icon:'bianji'
			});
		}
		
	},
	computed:{
		operStyle(){
			var maxindex = this.gridData.length;
			var operl = this.operExpands.length;
			var rowindex = this.rowindex;
			if( maxindex>operl && maxindex-rowindex>=operl){
				return 'top:10px';
			}else{
				return 'bottom:10px';
			}
		},
		
	},
	methods:{
		doClick(finishcb){
			if(this.operExpands && this.operExpands.length>0){
				this.doOptionOpen();
				return;
			}	
		},
		doOptionOpen(){
			if(this.isoptionOpen){
				this.doOptionClose();
				return;
			}
			this.isoptionOpen = true;
			setTimeout(()=>{//延迟一下，避免点出列表时触发
				document.addEventListener('click',this.doOptionClose);
			},100);
		},
		doOptionClose(){
			document.removeEventListener('click',this.doOptionClose);
			this.isoptionOpen = false;
			this.isoptionHover = -1;
		},
		
		mouseenter(index){
			this.isoptionHover=index;
		},
		mouseleave(index){
			this.isoptionHover=-1;
		},
		
		getJsCtrl(jsCtrl){
			var myjsCtrl = {
				router : this.router,
				post : hlc.copy(this.post),
			};
			
			//合并myjsCtrl和jsCtrl
			myjsCtrl = hlc.merge(myjsCtrl,jsCtrl);
			
			
			var titleName = myjsCtrl.popTitle||myjsCtrl.name;
			if(this.gridSet.colName){
				titleName+='('+this.row[this.gridSet.colName]+')';
			}
			if(this.gridSet.colNafy){
				titleName+='('+this.row[this.gridSet.colNafy]+')';
			}
			myjsCtrl.popTitle = titleName;
			
			myjsCtrl.post.key = this.row[this.gridSet.colKey];
			myjsCtrl.post.col = myjsCtrl.col;
			myjsCtrl.post.val = this.row[myjsCtrl.col];
			
			return myjsCtrl;
			
		},
		
		
		
		
		
		
		itemClick(jsCtrl,i){
			if(jsCtrl.type=='mod'){
				var popArgs={
					formType : 'crudMod',
					router: jsCtrl.router,
					post : jsCtrl.post,
				};
				
				if(this.gridSet.toolModInfo){
					popArgs = hlc.merge(popArgs,this.gridSet.toolModInfo);
				}
				
				hlc.popup.open({
					com:'sdForm',
					name: jsCtrl.popTitle,
					width:'70%',
					height:'80%',
					bgColor:'#f8f8f8',
					btnEnable:true,
					btnOkEnable:true,
					btnCloseEnable:true,
					args:popArgs,
					ok:()=>{		
						this.$emit('refresh');
					},
				});
				
				
			}else if(jsCtrl.type=='del'){
				var hintMsg = "确定要删除该条目么？？";
				if(this.gridSet.operDelHintMsg){
					hintMsg+=this.gridSet.operDelHintMsg;
				}
				if(!confirm(hintMsg)){
					return;
				}
				var router = jsCtrl.router;
				var post = jsCtrl.post;
				hlc.ajax({
					router: router+"@crudDel",
					post: post,
					ok:(res)=>{
						if(res.code==0){
							this.$emit('refresh');
						}else{
							
						}
					}
				});	
				
				
			}else{
				this.$refs['listone_'+i][0].doClick();
			}
		}
		
	}
}
/*
传给子组件的jsCtrl\myRouter\myPost都是新生成的
子组件修改的数据，会反应到row里，所以row还是原始的对象
*/
var template = `
<td :propsUpdate="propsUpdateInit" 
	style="" 
	:align="myjsCtrl.align"
	:style="myTdStyle" :title="myOverShow">
	<div style="display:inline-block;vertical-align:middle;" :style="myTdInStyle" @click="cellClick()">
		
		<sdGridCellTree v-if="row._tree_&&row._tree_.col == myjsCtrl.col" :jsCtrl="myjsCtrl" :row="row" :rowindex="rowindex" :gridData="gridData" />
		
		<sdGridCellDot v-if="myjsCtrl.dotMap" :jsCtrl="myjsCtrl" :row="row"/>
		
		<sdGridCellText v-if="realtype=='text'" :jsCtrl="myjsCtrl" :row="row"/>
		
		<sdGridCellLink v-if="realtype=='link'" :jsCtrl="myjsCtrl" :row="row"/>
		
		
		<sdGridCellHtml v-if="realtype=='html'" :jsCtrl="myjsCtrl" :row="row"/>
		
		<sdGridCellTable v-if="realtype=='table'" :jsCtrl="myjsCtrl" :row="row"/>
		
		<sdGridCellOrder v-if="realtype=='order'" :jsCtrl="myjsCtrl" :row="row" :gridData="gridData" :gridSet="gridSet" @refresh="$emit('refresh')" /> 
		
		<sdGridCellOnoff v-if="realtype=='onoff'" :jsCtrl="myjsCtrl" :row="row" :rowindex="rowindex" :gridSet="gridSet"  @refresh="$emit('refresh')"/>
		
		
		<sdGridCellInput v-if="realtype=='input'" :jsCtrl="myjsCtrl" :row="row"/>
		
		<sdGridCellEdit v-if="realtype=='edit'" :jsCtrl="myjsCtrl" :row="row" :gridSet="gridSet" @refresh="$emit('refresh')" />
		
		<sdGridCellDialog v-if="realtype=='dialog'" :jsCtrl="myjsCtrl" :row="row" :gridSet="gridSet" />
		
		<sdGridCellExecute v-if="realtype=='execute'" :jsCtrl="myjsCtrl" :row="row" :gridSet="gridSet" @refresh="$emit('refresh')"/>

		<sdGridCellState v-if="realtype=='state'" :jsCtrl="myjsCtrl" :row="row" :gridSet="gridSet"/>
		
		<sdGridCellRadio v-if="realtype=='radio'" :jsCtrl="myjsCtrl" :row="row" :gridSet="gridSet" @refresh="$emit('refresh')"/>
		
		<sdGridCellFetch v-if="realtype=='fetch'" :jsCtrl="myjsCtrl" :row="row" :gridSet="gridSet" @refresh="$emit('refresh')"/>

		<sdGridCellUpload v-if="realtype=='upload'" :jsCtrl="myjsCtrl" :row="row" :gridSet="gridSet" @refresh="$emit('refresh')"/>

		<sdGridCellDownload v-if="realtype=='download'" :jsCtrl="myjsCtrl" :row="row" :gridSet="gridSet" @refresh="$emit('refresh')"/>

		
	</div>
</td>
`;

//display:inline-block;会造成元素间有空格，
//可设置front-size:0 来消除，但子元素要记得重新设置front-size

import sdGridCellTree from "./sdGridCellTree.js"
import sdGridCellDot from "./sdGridCellDot.js"
import sdGridCellText from "./sdGridCellText.js"
import sdGridCellOrder from "./sdGridCellOrder.js"
import sdGridCellOnoff from "./sdGridCellOnoff.js"
import sdGridCellInput from "./sdGridCellInput.js"
import sdGridCellEdit from "./sdGridCellEdit.js"
import sdGridCellDialog from "./sdGridCellDialog.js"
import sdGridCellExecute from "./sdGridCellExecute.js"
import sdGridCellHtml from "./sdGridCellHtml.js"
import sdGridCellLink from "./sdGridCellLink.js"
import sdGridCellState from "./sdGridCellState.js"
import sdGridCellRadio from "./sdGridCellRadio.js"
import sdGridCellFetch from "./sdGridCellFetch.js"
import sdGridCellUpload from "./sdGridCellUpload.js"
import sdGridCellDownload from "./sdGridCellDownload.js"
import sdGridCellTable from "./sdGridCellTable.js"



export default{
	template : template,
	components:{
		sdGridCellDot,sdGridCellTree,sdGridCellText,
		sdGridCellOrder,sdGridCellOnoff,sdGridCellInput,
		sdGridCellEdit,sdGridCellDialog,sdGridCellExecute,
		sdGridCellHtml,sdGridCellLink,sdGridCellState,
		sdGridCellRadio,sdGridCellFetch,
		sdGridCellUpload,sdGridCellDownload,
		sdGridCellTable
	},
	props:{
		router:{
			default:''
		},
		post:{
			default:()=>{return {};}
		},
		jsCtrl:{
			default:()=>{return {};}
		},
		row:{
			default:()=>{return {};}
		},
		rowindex:{
			default:0,
		},
		tdindex:{
			default:0
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
			theme: hlc.config.theme,
			myjsCtrl: {},//传给子组件的jsCtrl
			oldValue:'',
			//myShow:true,
			//myRowspan:1,
			
		}
	},
	created(){
		//console.log(">>>>>>>>>cell created>>>>>>");
	},
	computed:{
		propsUpdateInit(){
			//这段本来放created，但row和gridata等数据更新时，执行不到
			//所以放到computed里,
			//computed里同步修改数据，不会死循环，异步修改可能死循环
			//注意：用JSON.stringify对比，会忽略function类型元素
			
			//把jsCtrl置为初始数据
			this.myjsCtrl = {
				router : this.router,
				post : hlc.copy(this.post),
			};
			
			//合并myjsCtrl和jsCtrl
			this.myjsCtrl = hlc.merge(this.myjsCtrl,this.jsCtrl);
			
			
			//合并myjsCtrl和特列jsCtrl
			var coldata = this.row[this.myjsCtrl.col];
			//有可能是数组，给table或fetch用
			if(coldata && !Array.isArray(coldata) && typeof(coldata)=='object'){
				this.row[this.myjsCtrl.col]=coldata.value;
				this.myjsCtrl = hlc.merge(this.myjsCtrl,coldata);
			}else{
				this.row[this.myjsCtrl.col]=coldata;
			}
			
			
			var titleName = this.myjsCtrl.popTitle||this.myjsCtrl.name;
			if(this.gridSet.colName){
				titleName+='('+this.row[this.gridSet.colName]+')';
			}
			if(this.gridSet.colNafy){
				titleName+='('+this.row[this.gridSet.colNafy]+')';
			}
			this.myjsCtrl.popTitle = titleName;
			
			this.myjsCtrl.post.key = this.row[this.gridSet.colKey];
			this.myjsCtrl.post.col = this.myjsCtrl.col;
			this.myjsCtrl.post.val = this.row[this.myjsCtrl.col];
			
			/*
			列合并，已实现，没大用，注释掉
			要用的话，
			data里加上：myShow:true,myRowspan:1,
			td属性里加上: v-if="myShow" rowspan="myRowspan"
			if(this.myjsCtrl.rowspan){
				var lastIndex = this.rowindex-1;
				if(lastIndex>=0){
					var lastval = this.gridData[lastIndex-1][this.myjsCtrl.col];
					if(coldata==lastval){
						this.myShow=false;
					}
				}
				if(this.myShow==true){
					var myRowspan=1;
					for(var yy = this.rowindex+1;yy<this.gridData.length;yy++){
						if(this.gridData[yy][this.myjsCtrl.col]!=coldata){
							break;
						}
						myRowspan++;
					}
					this.myRowspan = myRowspan;
				}
			}
			*/
		},
		
		realtype(){
			var type = 'text';
			if(this.myjsCtrl.type){
				type = this.myjsCtrl.type;
			}
			return type;
		},
		myTdStyle(){
			var style='';
			if(this.myjsCtrl.tdStyle){
				style+=';'+this.myjsCtrl.tdStyle;
			}
			//td设置了padding=7，这样树图标和上下边框就会隔开很大空白，也会把单元格撑得很高
			if(this.row._tree_&&this.row._tree_.col == this.myjsCtrl.col){
				style+=';padding:0px;padding-left:7px;';
			}

			return style;
		},
		myTdInStyle(){
			var style='';
			
			if(this.myjsCtrl.tdInStyle){
				style+=';'+this.myjsCtrl.tdInStyle;
			}
			
			if(this.myjsCtrl.width && this.myjsCtrl.width.indexOf('%')==-1){
				style+='width:'+this.myjsCtrl.width+';';
			}
			
			if(this.myjsCtrl.ellipsis){//超出省略
				style+='overflow: hidden;text-overflow: ellipsis;white-space: nowrap;';
			}
			if(this.myjsCtrl.wordNoBreak){//强制不换行
				style+=';white-space: nowrap;';
			}
			if(this.myjsCtrl.wordBreak){//强制换行
				style+=';word-wrap: break-word;;word-break:break-all;';
			}

			return style;
			
		},
		myOverShow(){
			if(this.myjsCtrl.overShow){
				if(typeof(this.myjsCtrl.overShow)==='string'){
					return this.row[this.myjsCtrl.overShow];
				}else{
					return this.row[this.myjsCtrl.col];
				}
			}
			//也支持在jsCtrl里设置overShowStr
			if(this.myjsCtrl.overShowText){
				return this.myjsCtrl.overShowText;
			}
			return '';
		}
	},
	methods:{
		cellClick(){
			if(this.myjsCtrl.showInDlg){
				var col = this.myjsCtrl.col;
				if(typeof(this.myjsCtrl.showInDlg)==='string'){
					col = this.myjsCtrl.showInDlg;
				}
				hlc.popup.open({
					name: this.myjsCtrl.name,
					width:'70%',
					height:'70%',
					text: this.row[col],
				});
			}
		},
	}
	
}
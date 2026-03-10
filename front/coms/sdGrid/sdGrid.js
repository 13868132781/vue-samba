var template = `
<div style="padding:0px">
<div style=";background-color:#fff;padding:10px;box-shadow:0 0 2px 1px #ccc;border-radius:3px">

	<div v-if="gridSet.toolEnable" style="margin-bottom:10px; margin-top:5px; display:flex; align-items:center">
		
		<div v-if="gridSet.toolRefreshEnable" style="margin-right:10px">
			<sdButton @sdClick="refresh()" value="刷新" icon="xuanzhuan"/>
		</div>
		
		<div v-if="post.unitId" style="margin-right:10px">
			<input type="checkbox" style="height:18px;width:18px" value="yes" v-model="doUnitId" title="在当前机构下筛选或搜索"/>
		</div>
		
		<div v-if="gridSet.toolFilterEnable" style="margin-right:10px">
			<sdButton @sdClick="filter()" value="筛选" icon="guolv1"/>
		</div>
		<div v-if="!filterInfo && gridSet.toolSearchColumn" style="margin-right:10px;width:200px">
			<sdSearch @onSubmit="search"/>
		</div>
		
		<div style="flex:1;margin-right:10px;font-size:12px; display:flex;">
			<div v-if="filterInfo" style="padding-left:5px;padding-right:5px;margin-right:10px ;overflow: hidden; word-break: break-all; position:relative;border:1px solid #ccc; border-radius:3px;box-shadow:0 0 2px 1px #ccc;font-size:12px; display:flex;align-items:center">
				<div style="font-size:12px;padding:4px;">{{filterInfo}}</div>
				<div style="">
					<sdIcon type="cha" size="14" style="margin-left:5px; cursor:pointer" :color="theme" @sdClick="noFilter()"/> 
				</div>
			</div>
		</div>
		
		<div v-for="item,index in (gridSet.toolExpands||[])" :key="'toolEx_'+index" style="margin-left:10px">
			<sdGridTool :jsCtrl="item" :jsCtrls="gridSet.toolExpands" :router="router" :post="myPost" :gridData="gridData" :fenyeInfo="fenyeInfo" @refresh="refreshReal()" />
		</div>
		
		<div v-if="gridSet.toolImportEnable" style="margin-left:10px">
			<sdGridImport :router="router" :post="myPost" :gridSet="gridSet" :gridData="gridData"  :fenyeInfo="fenyeInfo" @refresh="refreshReal()"/>
		</div>
		
		<div v-if="gridSet.toolDeleteEnable" style="margin-left:10px">
			<sdGridDelete :router="router" :post="myPost" :gridSet="gridSet" :gridData="gridData"  :fenyeInfo="fenyeInfo" @refresh="refreshReal()"/>
		</div>
		
		<div v-if="gridSet.toolExportEnable" style="margin-left:10px">
			<sdGridExport :router="router" :post="myPost" :gridSet="gridSet" :gridData="gridData"  :fenyeInfo="fenyeInfo" />
		</div>
		
		<div v-if="gridSet.toolAddEnable" style="margin-left:10px">
			<sdButton @sdClick="btn_add()" value="新增" icon="tianjia"/>
		</div>
		
	</div>

	<div style="box-shadow:0 0 2px 1px #eee;" >
	<table class="sdGridTable" cellspacing="0" cellpadding="7" ref="gridTable" @mouseleave="mouseenter(-1)" >
		<!--给tr加上position:sticky;top:-10px会在滚动时粘在顶部，目前未应用-->
		<tr style="background:linear-gradient(#f6f6f6 0%,#eee 10%,#eee 50%,#eee 90%,#f6f6f6 100%)"> 
			
			<td v-if="gridSet.rowSelectEnable" align="center" 
				style="width:30px;" :rowspan="header2.length>0?'2':''" >
				<input type="checkbox" @change="doCheckBox" />
			</td>
			
			<td v-for="tdd,index in header1" :key="'header1_'+index" style=" font-weight:bold" :align="tdd.align||''" :style="'width:'+(tdd.width||'')" :colspan="tdd.colspan" :rowspan="!tdd.colspan&&header2.length>0?'2':''">
				<sdGridHeaderCell :jsCtrl="tdd" :gridData="gridData" />
			</td>
			<td v-if="gridSet.operEnable" style="width:1px;white-space: nowrap; font-weight:bold" align="middle" :rowspan="header2.length>0?'2':''">
				操作
			</td>
		</tr>
		
		
		<tr v-if="header2.length>0" style="background-color:#f0f0f0"> 
			<td v-for="tdd,index in header2" :key="'header2_'+index" style="background-color:#f0f0f0; font-weight:bold" :align="tdd.align||''" :style="'width:'+(tdd.width||'')">
				<sdGridHeaderCell :jsCtrl="tdd" :gridData="gridData" />
			</td>
		</tr>
		
		<tr v-for="trd,tri in gridData" :key="'nr_tr_'+tri" 
			@mouseenter="mouseenter(tri)" 
			v-show="!trd._tree_||trd._tree_.show" 
			:style="trd._select_? 'background-color:#ccc': (mouseindex==tri?'background-color:#f6f6f6':'')"> 
			
			<td v-if="gridSet.rowSelectEnable" align="center" 
				style="width:30px;" >
				<input type="checkbox" v-model="trd._select_" /> 
			</td>
			
			<sdGridCell v-for="tdd,tdi in gridSet.columns"  :key="'nr_td_'+tri+'_'+tdi"  
				:jsCtrl="tdd" 
				:row="trd" 
				:rowindex="tri" 
				:tdindex="tdi" 
				:gridSet="gridSet"
				:gridData="gridData" 
				:router="router" :post="myPost"  
				@refresh="refreshKeep()"
			/> 
			
			
			<sdGridOper 
				:row="trd" 
				:rowindex="tri" 
				:gridSet="gridSet"
				:gridData="gridData" 
				:router="router" :post="myPost" 
				@refresh="refreshKeep()"
				/>
			
		</tr>
		
		<tr v-if="gridCount<=0">
			<td colspan="20" align="center" valign="middle" 
			style="padding:10px;color:#888;"
			>{{gridMsg[gridCount]}}</td>
		</tr>
		
	</table>
	</div>
	
	
	<sdGridFenye v-if="gridSet.fenyeEnable" :fenyeInfo="fenyeInfo" @getData="getData()" />
	
	<div v-if="!gridSet.fenyeEnable" style="height:40px;padding:10px">
	{{'共'+(this.gridData?this.gridData.length:0)+'条'}}
	</div>
	
</div>
</div>
`;

import sdGridTool from "./sdGridTool.js";
import sdGridCell from "./sdGridCell.js";
import sdGridFenye from "./sdGridFenye.js";
import sdGridHeaderCell from "./sdGridHeaderCell.js";
import sdGridOper from "./sdGridOper.js";
import sdGridExport from "./sdGridExport.js";
import sdGridDelete from "./sdGridDelete.js";
import sdGridImport from "./sdGridImport.js";

export default{
	template : template,
	components:{sdGridCell,sdGridTool,sdGridFenye,sdGridHeaderCell,sdGridOper,sdGridExport,sdGridDelete,sdGridImport},
	props:{
		router:{
			default:'',
		},
		post:{
			default:()=>{
				return {//给ajax的data参数
					key:'',//主键id
					col:'',//单元格字段名
					val:'',//单元格字段值（onoff是修改后的值）
					row:{},//整个一行的数据
					goto:'',
					unitId:'',//机构id
					search:'',//搜索
					filter:'',//过滤
					checked:[],//选中行列表
					btnOption:'',//按钮下拉选项id
					formVal:{},//表单提交
					execVal:{},//执行数据
					fenye:{},//分页信息
					keyList:[],//
				};
			},
		},
	},
	data(){
		return {
			
			myPost:{},
			
			doUnitId:false,
			
			mouseindex:-1,
			
			theme: hlc.config.theme,
			
			fenyeInfo:{
				now:0,
				num: 20,
				total:0,
			},
			
			filterInfo:'',
			
			gridData:[],
			
			gridSet:{
				columns:[
					{col:'id',name:'ID'}
				],
				
				colKey:'',
				colName:'',
				colNafy:'',
				
				rowSelectEnable:false,
				
				toolEnable:true,
				toolAddEnable : true,
				toolExportEnable : false,
				toolRefreshEnable: true,
				toolFilterEnable: false,
				toolDeleteEnable: false,
				toolImportEnable: false,
				toolSearchColumn: null,
				toolExpands:[],
				
				operEnable :true ,
				operModEnable: true,
				toolModInfo: null,
				operDelEnable:true,
				operDelHintMsg:'',
				operExpands:[],
				
				fenyeEnable:true,
				fenyeNum:20,//默认20 
				
				treeInfo:null,
			},
			
			gridCount:-2,//-2 正在加载 -1 数据渲染中 >-1 行数
			gridMsg:{
				'-2':'正在加载',
				'-1':'数据渲染中',
				'0':'无数据',
			},
			
			header1:[],
			header2:[],
			
			/*
			row 里面的使用变量
			_select_ _execute_ _operEnable_
			*/
			
			timemcro:0,
			
		}
	},
	created(){
		this.myPost=hlc.copy(this.post);
		this.getSet();
	},
	
	computed:{
		
	},
	updated(){
		if(this.gridCount==-1){
			this.gridCount = this.gridData.length;
		}
		if(this.timemcro){
			//alert((Date.now()-this.timemcro));
			//console.log('load time: '+(Date.now()-this.timemcro));
			this.timemcro = 0;
		}
	},
	methods:{
		getSet(){
			if(!this.router){
				return;
			}
			if(!hlc.config.mode){//非开发模式，从缓存里取
				if(hlc.cache[this.router+"@gridSet"]){
					let data = hlc.cache[this.router+"@gridSet"];
					this.getSetSuccess(data);
					return;
				}
			}
			hlc.ajax({
				router: this.router+"@gridSet",
				post: {},
				ok:(res)=>{
					if(res.code==0){
						if(!hlc.config.mode){//非开发模式，缓存gridset
							hlc.cache[this.router+"@gridSet"]=hlc.copy(res.data);
						}
						this.getSetSuccess(res.data);
					}
				}
			}); 
		},
		getSetSuccess(data){
			//alert(JSON.stringify(res.data));
			this.gridSet = hlc.merge(this.gridSet,(data||{}),1);
						
			for(var i=0;i<this.gridSet.toolExpands.length;i++){
				if(!this.gridSet.toolExpands[i].router){
					this.gridSet.toolExpands[i].router=this.router;
				}
			}
				
			this.disHeader();
			if(this.gridSet.fenyeNum){
				this.fenyeInfo.num = this.gridSet.fenyeNum;
			}
			this.fenyeInfo.now = 1;
			this.getTotal();
			this.getData();
		},
		getData(keepold){
			if(!this.router){
				return;
			}
			//这相当于重设this.myPost，会触发子组件依赖于post的watch和computed
			//this.myPost = hlc.copy(this.myPost);
			//this.gridData = []; 
			var post = hlc.copy(this.myPost);
			//this.myPost里不包含fenye信息，每次获取数据时，临时加上去的
			if(this.gridSet.fenyeEnable){
				post.fenye= this.fenyeInfo;
			}
			if((post.filter || post.search)&& !this.doUnitId){//全局搜索或筛选
				delete post.unitId;
			}
			//树形表格，search转为treeSearch
			if( post.search && this.gridSet.treeInfo){
				post.treeSearch = post.search;
				delete post.search;
			}
			hlc.ajax({
				router: this.router+"@gridData",
				post: post,
				ok:(res)=>{
					if(res.code==0){
						if(!keepold){
							this.gridData = [];
							this.gridCount= -2;
						}
						setTimeout(()=>{//让页面滚动到顶部再赋值
							//alert(JSON.stringify(res.data));
							//这个-1是呈现不出来的，因为合并到下面的数据渲染了
							this.timemcro = Date.now();
							this.gridCount = -1;
							//this.gridData = res.data;
							
							this.gridData = res.data.slice(0,20);
							
							var dataList=[];
							if(res.data.length>20){
								var t = 0;
								while(1){
									var start = 20+t*5;
									var newD= res.data.slice(start,start+5);
									dataList.push(newD);
									if(newD.length<5){
										break;
									}
									t++;
								}
								this.addDataDG(dataList);
							}
							
						},1);
					}
				}
			});
			
		},
		
		addDataDG(dataList){
			setTimeout(()=>{
				this.gridData = this.gridData.concat(dataList[0]);
				dataList.shift();
				if(dataList.length>0){
					this.addDataDG(dataList);
				}
			},10);
		},
		
		getTotal(){
			if(!this.router){
				return;
			}
			if(!this.gridSet.fenyeEnable){
				return;
			}	
			
			var post = hlc.copy(this.myPost);
			if((post.filter || post.search)&& !this.doUnitId){//全局搜索或筛选
				delete post.unitId;
			}
			hlc.ajax({
				router: this.router+"@gridTotal",
				post: post,
				ok:(res)=>{
					if(res.code==0){
						this.fenyeInfo.total = res.data;
					}
				}
			});
		},
		
		
		disHeader(){
			var header1=[];
			var header2=[];
			var start=0;
			var num=0;
			this.gridSet.columns.map((one,index)=>{
				if(num>0){
					header2.push(one);
					num--;
					return;
				}
				
				if(one.colspan){
					start = index;
					num = one.colspan.num||1;
					header1.push({
						name: one.colspan.name,
						colspan: one.colspan.num,
						align:'center'
					});
					header2.push(one);
					num--;
				}else{
					header1.push(one);
				}
			});
			this.header1 = header1;
			this.header2 = header2;
		},
		
		
		refresh(){//刷新，滚动到顶部
			this.getData();
		},
		refreshKeep(){//刷新，保持滚动条位置
			this.getData(true);
		},
		refreshReal(){//修改查询条件后刷新
			this.fenyeInfo.now = 1;//把分页数置为开始
			this.getTotal();
			this.getData();
		},
		
		btn_add(){
			var popArgs={
				router: this.router,
				post: hlc.copy(this.myPost),
				formType: 'crudAdd'
			};
			hlc.popup.open({
				com:'sdForm',
				name:'新增',
				width:'70%',
				height:'80%',
				bgColor:'#f8f8f8',
				btnEnable:true,
				btnOkEnable:true,
				btnCloseEnable:true,
				args:popArgs,
				ok:()=>{
					this.refreshKeep();
				},
			});
			
		},
		search(val){
			this.doWhere('search',val);
		},
		filter(){
			//return;
			var popArgs={
				router: this.router,
				post: hlc.copy(this.myPost),
			};
			hlc.popup.open({
				com:'sdFilter',
				name:'筛选',
				width:'70%',
				height:'70%',
				bgColor:'#f8f8f8',
				args:popArgs,
				btnEnable:true,
				btnOkEnable:true,
				btnCloseEnable:true,
				ok:(res)=>{
					if(!res){
						this.doWhere('filter','');
						return;
					}
					//alert(JSON.stringify(res));
					var str='';
					for(var k in res){
						let re = res[k];
						if(str!=''){
							str+=', ';
						}
						str+=re.name+re.op+re.show;
					}
					this.doWhere('filter',str,res);
				}
			});
		},
		noFilter(){
			this.doWhere('filter','');	
		},
		doWhere(lei,val,res){
			if(lei=='search'){
				this.filterInfo='';
				this.myPost.filter = null;
				this.myPost.search = val;
			}else{
				if(val){
					this.myPost.search = '';
				}
				this.filterInfo=val;//用于显示在页面上
				this.myPost.filter = res;
			}
			if((this.myPost.filter || this.myPost.search) && !this.doUnitId ){
				this.$emit('onLookForAll',true);
			}else{
				this.$emit('onLookForAll',false);
			}
			this.refreshReal();
		},
		
		mouseenter(i){
			this.mouseindex = i;
		},
		
		doCheckBox(e){
			var b=e.target.checked;
			for(var i in this.gridData){ 
				this.gridData[i]._select_=b;
			}
		},
		
	},
	
}
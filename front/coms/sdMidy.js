var template = `
<div style="padding:5px">
	<div style="margin-bottom:5px">
		<sdSearch @onSubmit="search" doShadow=""/>
	</div>
	<table  style="width:100%;border-collapse:collapse;">
		<tr v-for="trd,tri in gridData"
			@mouseenter="mouseenter(tri)" 
			@mouseleave="mouseenter(-1)" 
			v-show="!trd._tree_||trd._tree_.show" 
			style="" 
			:style="mouseindex==tri?'background-color:#f0f0f0':''" 
			
			>
			
			<sdTreeCell v-for="tdd,tdi in header" :key="tdi"  
			:header="tdd" :row="trd" 
			:rowindex="tri" :gridData="gridData" :idNow="idNow" 
			@itemClick="myclick"
			/> 
			
		</tr>
	</table>
</div>
`;

import sdTreeCell from "./sdTreeCell.js";

export default{
	template : template,
	components:{sdTreeCell},
	props:{
		router:{
			default:'',
		},
		dftid:{
			default:'',
		},
		//dbkey:{//要剔除掉的key
		//	default:'',
		//},
	},
	data(){
		return {
			header:[
				{col:'name'}
			],
			gridData:[],
			
			mouseindex:-1,
			idNow: this.dftid||'',
		}
	},
	created(){
		this.getData();
	},
	methods:{
		search(val){
			hlc.ajax({
				router: this.router+"@treeData",
				post: {treeSearch:val},
				ok:(res)=>{
					if(res.code==0){
						//alert(JSON.stringify(res.data));
						this.gridData = res.data;
					}
				}
			}); 
		},
		getData(){
			hlc.ajax({
				router: this.router+"@treeData",
				post: {},
				ok:(res)=>{
					if(res.code==0){
						//alert(JSON.stringify(res.data));
						this.gridData = res.data;
						if(this.gridData.length>0){
							var dftitem = {
								id: this.gridData[0].id,
								name: this.gridData[0][this.header[0].col],
							};
							this.myclick(dftitem);
						}
					}
				}
			}); 
		},
		mouseenter(i){
			this.mouseindex = i;
		},
		myclick(item){
			this.idNow = item.id;
			this.$emit('midclick',item);
		},
		
	}
}
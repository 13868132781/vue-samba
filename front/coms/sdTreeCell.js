var template = `
<td style="" @click="$emit('itemClick',{id:row.id,name:row[header.col]})">
	<div :style="shenglue" :title="showtitle" style="font-size:0px;vertical-align:middle; ">
		<div v-if="row._tree_&&row._tree_.col == header.col" style="display:inline-block;font-size:0px;vertical-align:middle;">
			<img v-for="tp in treepre" :src="'/static/image/tree/pre'+tp+'.gif'" style="vertical-align:middle; width:20px; height:20px" />
			
			<img @click.stop="treeopen(treeZdtu)" style="vertical-align:middle; width:20px; height:20px" :src="'/static/image/tree/'+treeZdtu+'.gif'">
				
		</div>
		<div 
			style="
				cursor:pointer;
				display:inline-block; 
				font-size:13px;vertical-align:middle;" :style="idNow==row.id?'font-weight:bold;color:'+theme:''"
			>{{row[header.col]}}<span v-if="row['_tree_']['isSearch']" >({{row['_tree_']['isSearch']}})</span></div>
	</div>
</td>
`;

//display:inline-block;会造成元素间有空格，
//可设置front-size:0 来消除，但子元素要记得重新设置front-size

export default{
	template : template,
	props:{
		header:{
			default:()=>{return {};}
		},
		row:{
			default:()=>{return {};}
		},
		rowindex:{
			default:0,
		},
		gridData:{
			default:()=>{return [];}
		},
		idNow:{
			default:-1,
		}
	},
	data(){
		return {
			theme: hlc.config.theme,
			jsoper: this.header.jsoper||{},
			
		}
	},
	computed:{
		treepre(){
			let prelist={};
			for(var i=0; i<this.row._tree_.depth; i++){
				if(i in this.row._tree_.fislast){
					prelist["_"+i]='empty';
				}else{
					prelist["_"+i]='line';
				}
			}
			return prelist;
		},
		treeStatus(){
			var status='close';
			if(this.row._tree_.open){
				status='open';
			}
			return status;
		},
		
		treeZdtu(){
			var status = this.treeStatus;
				
			let zdtu = 'box'+status;
			if(this.row._tree_.islast){
				zdtu = 'box'+status+'bottom';
			}
			if(this.row._tree_.isleaf){
				zdtu = 'join';
				if(this.row._tree_.islast){
					zdtu = 'joinbottom';
				}
			}
			return zdtu;
		},
		treeFdtu(){
			var status = this.treeStatus;
			
			var fdtu = 'page';
			if(!this.row._tree_.isleaf){
				fdtu = 'folder'+status;
			}
			return fdtu;
		},
		treeShow(){
			return this.row._tree_.show ;
		},
		
		realtype(){
			var type = 'text';
			if(this.jsoper.type){
				type = this.jsoper.type;
			}
			return type;
		},
		shenglue(){
			var cellStyle = '';
			if(this.jsoper.cellStyle){
				cellStyle = this.jsoper.cellStyle;
			}
			if(this.jsoper.shenglue){
				return cellStyle+';overflow: hidden;text-overflow: ellipsis;white-space: nowrap;';
			}
		},
		showtitle(){
			if(this.jsoper.shenglue){
				return this.row[this.header.col];
			}
		}
	},
	methods:{
		treeopen(tu){
			var mydepth = this.row._tree_.depth;
			if(tu.indexOf('close')!=-1){
				this.row._tree_.open=true;
				for(var i=this.rowindex+1;i< this.gridData.length;i++){
					let depth = this.gridData[i]._tree_.depth;
					if(mydepth < depth){
						if(mydepth+1==depth){
							this.gridData[i]._tree_.show=true;
						}else{
							this.gridData[i]._tree_.show=false;
						}
					}else{
						break;
					}
				}
				
			}else if(tu.indexOf('open')!=-1){
				this.row._tree_.open=false;
				for(var i=this.rowindex+1;i< this.gridData.length;i++){
					let depth = this.gridData[i]._tree_.depth;
					
					if(mydepth < depth){
						this.gridData[i]._tree_.open=false;
						this.gridData[i]._tree_.show=false;
					}else{
						break;
					}
				}
			}
		},
	}
	
}
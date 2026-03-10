var template = `
<div style="display:inline-block;font-size:0px;vertical-align:middle;">
	<img v-for="tp in treepre" :src="'/static/image/tree/pre'+tp+'.gif'" style="vertical-align:middle; width:28px; height:28px" />
			
	<img @click="treeopen(treeZdtu)" style="vertical-align:middle; width:28px; height:28px" :src="'/static/image/tree/'+treeZdtu+'.gif'">
			
	<img v-if="1==2" style="vertical-align:middle; width:20px; height:20px" :src="'/static/image/tree/'+treeFdtu+'.gif'">	
</div>
`;

export default{
	template : template,
	
	props:{
		jsCtrl:{
			default:()=>{
				return {}
			}
		},
		row:{
			default:()=>{
				return {}
			}
		},
		rowindex:{
			default:0,
		},
		gridData:{
			default:()=>{
				return {}
			}
		},
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
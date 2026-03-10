var template = `
<div style="display:inline-block; ">
	<sdButton :showType="jsCtrl.showType" :value="jsCtrl.nameShow||jsCtrl.name" :icon="jsCtrl.icon||'tongbu'" :doing="doing" @sdClick="doClick" />
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
		gridSet:{
			default:()=>{
				return {}
			}
		}
	},
	data(){
		return {
			doing:0,
		}
	},
	mounted(){
		//给批量执行用的
		if(!this.row._batchList_){
			this.row._batchList_={};
		}
		if(this.jsCtrl.col){
			this.row._batchList_[this.jsCtrl.col]=this;
		}
	},
	methods:{
		doClick(finishcb){
			if(this.jsCtrl.askSure){
				if(!confirm(this.jsCtrl.askSure)){
					return;
				}
			}
			
			this.jsCtrl.post.execVal={};
			var columns = this.gridSet.columns;
			columns.map((ho)=>{
				if(ho.type=='input'){
					var col = ho.inputCol;
					var val = this.row[col];
					this.jsCtrl.post.execVal[col]=val;
				}
				
			});
			
			
			
			this.doing=1;
			hlc.ajax({
				router: this.jsCtrl.router+"@execute",
				post: this.jsCtrl.post,
				silent:true,
				ok:(res)=>{
					//alert(JSON.stringify(res));
					if(res.code==0){
						this.doing=2;;//2 成功 3 失败
					}else{
						this.doing=3;
					}
					if(res.refresh){
						this.$emit('refresh');
					}
					if(res.data){//把返回数据添加到row上
						for(var p in res.data){
							this.row[p] = res.data[p];
						}
					}
					if(finishcb){finishcb();}
				}
			});	
		}
	}
}
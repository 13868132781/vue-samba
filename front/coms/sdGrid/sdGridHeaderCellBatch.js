var template = `
<div style="display:inline-block; white-space: nowrap; cursor:pointer;color:#00f;" @click="doClick">
	{{jsCtrl.name}}
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
		gridData:{
			default:()=>{return {};}
		},
	},
	data(){
		return {
			doing:0,
			
			execRefs:[],
			reflength:0,
		}
	},
	methods:{
		doClick(){
			if(this.doing!=0){
				alert('上次运行尚未结束');
				return;
			}
			var gd = this.gridData;
			for(var i in gd){
				var col = this.jsCtrl.col;
				if(!gd[i]._batchList_){
					continue;
				}
				var ref = gd[i]._batchList_[col];
				if( this.jsCtrl.headBatch.batchAll || (ref && gd[i]._select_)){
					this.execRefs.push(ref);
				}
			}
			if(this.execRefs.length==0){
				alert('请选择行');
				return ;
			}
			
			if(this.jsCtrl.jsBefore){
				this.jsCtrl.post = this.jsCtrl.post||{};
				var jsBefore = null;
				eval("jsBefore="+this.jsCtrl.jsBefore);
				var b = jsBefore(this.jsCtrl);
				if(!b){
					return;
				}
			}
			
			
			this.reflength = this.execRefs.length;
			this.doing=1;
			for(var i=0;i<4;i++){
				this.doNext(true);
			}
			
			
		},
		doNext(first){
			var ref=this.execRefs.shift();
			if(ref){
				ref.doClick(this.doNext);
			}
			
			if(!first){
				this.reflength--;
			}
			if(this.reflength==0){
				this.doing=0;
			}
		}
	}
	
}
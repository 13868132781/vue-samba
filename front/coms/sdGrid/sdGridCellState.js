var template = `
<div style="display:inline-block; " :class="doing==1?'sdRotation':''" >
	<sdIcon :type="myType" :color="myColor" @sdClick="doClick" />
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
			options:[
				{icon:'closel',color:'#f00'},
				{icon:'okl',color:'#00f'},
			],
		}
	},
	computed:{
		myType(){
			var val = this.row[this.jsCtrl.col];
			val = parseInt(val);//state只接受0 1两种值
			var icon = '';
			if(this.options[val]){
				icon = this.options[val].icon;
			}
			if(this.doing==1){
				icon = 'loading';
			}
			return icon;
		},
		myColor(){
			var val = this.row[this.jsCtrl.col];
			val = parseInt(val);
			var color='';
			if(this.options[val]){
				color = this.options[val].color;
			}
			if(this.doing==1){
				color = '#444';
			}
			return color;
		}
	},
	mounted(){
		//给批量执行用的
		if(!this.row._batchList_){
			this.row._batchList_={};
		}
		this.row._batchList_[this.jsCtrl.col]=this;
	},
	methods:{
		doClick(finishcb){
			if(this.jsCtrl.askSure){
				if(!confirm(this.jsCtrl.askSure)){
					return;
				}
			}
			if(this.jsCtrl.disable){
				return;
			}
			
			this.doing=1;
			hlc.ajax({
				router: this.jsCtrl.router+"@state",
				post: this.jsCtrl.post,
				silent:true,
				ok:(res)=>{
					//alert(JSON.stringify(res));
					this.doing=0;
					this.row[this.jsCtrl.col] = res.code;
					
					if(finishcb){finishcb();}
				}
			});	
		}
	}
}
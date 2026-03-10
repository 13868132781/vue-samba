var template = `
<div>
	<sdIcon type="jiantou_xiangshang" :color="theme" @sdClick="myorder(-1)" style="cursor:pointer" />
	<sdIcon type="jiantou_xiangxia" :color="theme" @sdClick="myorder(1)" style="cursor:pointer" />
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
		gridSet:{
			default:()=>{
				return {}
			}
		},
	},
	data(){
		return {
			theme: hlc.config.theme,
		}
	},
	methods:{
		myorder(n){
			if(!this.jsCtrl.router){
				return;
			}
			this.jsCtrl.post.move = n;
			hlc.ajax({
				router: this.jsCtrl.router+"@order",
				post: this.jsCtrl.post,
				ok:(res)=>{
					if(res.code==0){
						this.$emit('refresh');
					}
				}
			}); 
		},
		
		
		myOrderSwip(n){
			var index = this.rowindex;
			var other = index+n;
			if(other < 0 || other > this.gridData.length-1){
				return;
			}
			
			var data =  this.gridData[index];
			var datao = this.gridData[other];
			this.gridData[index]=datao;
			this.gridData[other]=data;
		},
	}
}
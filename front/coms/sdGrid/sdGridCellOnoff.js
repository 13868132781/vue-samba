var template = `
<div  
			style="
				width:24px;
				border-radius:12px;
				box-shadow:0 0 2px 1px #ccc;
				padding:2px; 
				display:flex;transition: all 0.5s ease;" 
			:style="getStaus?'background-color:'+theme+';justify-content: flex-end':'background-color:#fff;justify-content: flex-start'"
			@click="doOnoff(parseInt(row[jsCtrl.col]))">
			<div 
				style="
					height:10px; 
					width:10px; 
					border-radius:5px; 
					background-color:#fff;
					box-shadow: 0 0 2px 1px #aaa;"
				></div>
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
		},
	},
	data(){
		return {
			theme: hlc.config.theme,
			onoffMap : this.jsCtrl.onoffMap||[0,1],
			myStatus: false,
		}
	},
	computed:{
		getStaus(){
			var zhi = this.row[this.jsCtrl.col];
			if(this.onoffMap[0]+"" === zhi+""){
				this.myStatus = false;
				return false;
			}else if(this.onoffMap[1]+"" === zhi+""){
				this.myStatus = true;
				return true;
			}
			this.myStatus = false;
			return false;
		}
	},
	methods:{
		doOnoff(){
			var zhi = "";
			var zhinot = "";
			if(this.myStatus){
				zhi = this.onoffMap[0];
				zhinot = this.onoffMap[1];
			}else{
				zhi = this.onoffMap[1];
				zhinot = this.onoffMap[0];
			}
			
			this.row[this.jsCtrl.col]=zhi;
			
			
			this.jsCtrl.post.val= zhi;
			//用于审计时确定是启用还是禁用
			this.jsCtrl.post.onoffStatus = !this.myStatus;
			
			hlc.ajax({
				router: this.jsCtrl.router+'@onoff',
				post: this.jsCtrl.post,
				ok:(res)=>{
					if(res.code!=0){//失败复位
						this.row[this.jsCtrl.col]=zhinot;
					}else{
						if(res.refresh){
							this.$emit('refresh');
						}else if(this.jsCtrl.refresh){
							this.$emit('refresh');
						}
					}
				}
				
			});
			
		}
		
	}
}
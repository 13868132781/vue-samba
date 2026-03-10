var template = `
<div style="border-radius:12px;border:1px solid #00f;padding:1px;display:inline-block;cursor:pointer" :style="style1" @click="doClick">
	<div style="width:12px; height:12px;border-radius:8px;background-color:#fff; " :style="style2"></div>
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
		}
	},
	computed:{
		style1(){
			var value = parseInt(this.row[this.jsCtrl.col]);
			if(value){
				return 'border-color:'+this.theme;
			}else{
				return 'border-color:#ddd';
			}
		},
		style2(){
			var value = parseInt(this.row[this.jsCtrl.col]);
			if(value){
				return 'box-shadow: inset 0 0 5px 3px '+this.theme;
			}else{
				return 'box-shadow: inset 0 0 4px 2px #ddd';
			}
		}
	},
	methods:{
		doClick(){
			if(this.value){
				return;
			}
			
			hlc.ajax({
				router: this.jsCtrl.router+'@radio',
				post: this.jsCtrl.post,
				ok:(res)=>{
					if(res.code==0){
						this.$emit('refresh');
					}
				}
				
			});
			
		}
	}
}
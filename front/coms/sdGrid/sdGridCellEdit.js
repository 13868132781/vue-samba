var template = `
<div style="display:inline-block;cursor:pointer ">
	<sdIcon :type="jsCtrl.icon||'shougongqianshou'" @sdClick="doClick" />
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
	methods:{
		doClick(){
			
			var popArgs = hlc.copy(this.jsCtrl);
			popArgs.formType = 'edit';
			
			hlc.popup.open({
				com:'sdForm',
				name: this.jsCtrl.popTitle,
				width:this.jsCtrl.popWidth||'70%',
				height:this.jsCtrl.popHeight||'70%',
				bgColor:'#f8f8f8',
				args: popArgs,
				ok:()=>{		
					this.$emit('refresh');
				},
			});
		}
	}
}
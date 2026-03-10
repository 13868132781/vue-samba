var template = `
<div style="display:inline-block; cursor:pointer">
	<sdIcon type="riqi" @sdClick="doClick" style="cursor:pointer" />
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
	methods:{
		doClick(){
			
			var popArgs = hlc.copy(this.jsCtrl);
			
			if(!popArgs.post.keyList){
				popArgs.post.keyList=[];
			}
			popArgs.post.keyList.push(popArgs.post.key);
			delete popArgs.post.key;//这个key得给内窗用，所以得删除掉，避免混乱
			
			hlc.popup.open({
				com:'sdDialog',
				name:this.jsCtrl.popTitle,
				width:this.jsCtrl.popWidth||'70%',
				height:this.jsCtrl.popHeight||'70%',
				args:popArgs,
			});
		}
	}
}
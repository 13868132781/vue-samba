var template = `
<div style="display:inline-block; cursor:pointer" @click="doClick">
	<sdIcon v-if="myShowType=='all'||myShowType=='icon'" :type="jsCtrl.icon||'shougongqianshou'" />
	<span v-if="myShowType=='all'||myShowType=='text'">{{row[jsCtrl.col]}}</span>
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
	computed:{
		myShowType(){
			var st='icon';
			if(this.jsCtrl.showType){
				st = this.jsCtrl.showType;
			}
			return st;
		}
	},
	
	
	methods:{
		doClick(){
			var popArgs = hlc.copy(this.jsCtrl);
			if(popArgs.showColVal && typeof(popArgs.showColVal)==='string'){
				//传的数据，由popArgs.showColVal指定的字段名里的数据
				popArgs.post.val = this.row[popArgs.showColVal];
			}
			
			hlc.popup.open({
				com:"sdFetch",
				name: this.jsCtrl.popTitle,
				width:this.jsCtrl.popWidth||'70%',
				height:this.jsCtrl.popHeight||'70%',
				args: popArgs,
			});
		}
	}
}
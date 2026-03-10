/*
值给execute类型用的
*/
var template = `
<div>
	{{jsCtrl.name}}:
	<input v-if="jsCtrl.inputType=='text'" v-model="jsCtrly.value" style="width:100%;height:100%" :style="'width:'+jsCtrl.width" />
	<select v-if="jsCtrl.inputType=='select'" v-model="jsCtrly.value" style="width:100%;height:100%"  :style="'width:'+jsCtrl.width">
		<option v-for="opt,ind in jsCtrl.inputOptions" :value="ind">{{opt}}</option>
	</select>
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
		jsCtrly:{
			default:()=>{
				return {}
			}
		},
	},
	
}


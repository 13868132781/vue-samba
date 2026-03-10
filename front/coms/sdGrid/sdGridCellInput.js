var template = `
<div>
	<input v-if="jsCtrl.inputType=='text'" v-model="row[jsCtrl.col]" style="width:100%;height:100%" />
	<select v-if="jsCtrl.inputType=='select'" v-model="row[jsCtrl.col]" style="width:100%;height:100%">
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
		row:{
			default:()=>{
				return {}
			}
		},
	}
	
}


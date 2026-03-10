var template = `
<div style="">
	<input ref="input" type="text" :placeholder="info.holder" :value="info.value" @input="doInput" style="width:100%;border:0px;padding:10px;outline:none; cursor:pointer" @focus="dofocus" @blur="doblur" @click="doClick" readonly/>
</div>
`;

import tablePop from './_tablePop.js';

export default{
	template : template,
	props:{
		info:{
			default:{},
		}
	},
	data(){
		return {
			xzname:''
		}
	},
	methods:{
		dofocus(){
			this.$emit("onFocus",true);
		},
		doblur(){
			this.$emit("onFocus",false);
		},
		doInput(){
			//this.$emit("onInput",this.$refs.input.value);
		},
		doClick(){
			hlc.popup.open({
				com:tablePop,
				name:'表格数据',
				width:'60%',
				height:'60%',
				bgColor:'#fff',
				btnEnable:true,
				btnOkEnable:true,
				btnCloseEnable:true,
				args: this.info,
				ok:(res)=>{
					this.info.value=res; 
				},
			});
		}
	}
}
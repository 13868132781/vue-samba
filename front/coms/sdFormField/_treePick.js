var template = `
<div style="">
	<input ref="input" type="text" :placeholder="info.holder" :value="info.xsname" @input="doInput" style="width:100%;border:0px;padding:10px;outline:none; cursor:pointer" @focus="dofocus" @blur="doblur" @click="doClick" readonly/>
</div>
`;

import treePickPop from './_treePickPop.js';

export default{
	template : template,
	props:{
		info:{
			default:{},
		}
	},
	data(){
		return {
			//xsname:this.info.xsname||'',
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
				com:treePickPop,
				name:'选择',
				width:'500px',
				height:'70%',
				bgColor:'#fff',
				btnEnable:true,
				btnOkEnable:true,
				btnCloseEnable:true,
				args: this.info,
				ok:(res)=>{
					this.info.value=res.id;
					this.info.xsname=res.name;
					this.$emit("onInput",res.id);
				},
			});
		}
	}
}
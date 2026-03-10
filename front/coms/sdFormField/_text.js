var template = `
<div style="">
	<input ref="input" :type="info.ispass?'password':'text'" :placeholder="info.holder" v-model="info.value" @input="dochange" style="width:100%;border:0px;padding:10px;outline:none;" @focus="dofocus" @blur="doblur" />
</div>
`;

export default{
	template : template,
	props:{
		info:{
			default:{},
		}
	},
	data(){
		return {
			
		}
	},
	methods:{
		dofocus(){
			this.$emit("onFocus",true);
		},
		doblur(){
			this.$emit("onFocus",false);
		},
		dochange(){
			this.$emit("onInput",this.$refs.input.value);
		}
	}
}
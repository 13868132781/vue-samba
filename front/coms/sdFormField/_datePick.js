var template = `
<div style="">
	<input ref="input" type="text" :placeholder="info.holder" :value="info.value" @input="doInput" style="width:100%;border:0px;padding:10px;outline:none; cursor:pointer" @focus="dofocus" @blur="doblur" @click="doClick" readonly/>
</div>
`;

import datePickPop from './_datePickPop.js';

export default{
	template : template,
	props:{
		info:{
			default:{},
		}
	},
	data(){
		return {
			test:'',
		}
	},
	created(){
		//alert(this.info.value);
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
				com:datePickPop,
				name:'选择',
				width:'800px',
				height:'auto',
				bgColor:'#fff',
				btnEnable:true,
				btnOkEnable:true,
				btnCloseEnable:true,
				args: this.info,
				ok:(res)=>{
					//直接复制，响应不到input里去，只能用set
					//this.info里必须有value这个成员，否则这么设置也无法响应到input里
					this.info.value = res.value;
					this.$emit("onInput",res.value);
				},
			});
		}
	}
}
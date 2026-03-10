var template = `
<div style="">
	<select ref="input" :placeholder="info.holder" v-model="info.value" @change="dochange" style="width:100%; border:0px; padding:10px; outline:none; background-color:#fff" @focus="dofocus" @blur="doblur" >
		<option value=''></option>
		<option v-for="item,index in (info.options||{})" :value="index" >{{item}}</option>
	</select>
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
	created(){
		if(!hlc.True(this.info.value)){
			this.info.value='';
		}
		
	},
	computed:{
	},
	methods:{
		/*
		getKey(index){
			if(this.info.ask && !hlc.True(this.info.value)){
				this.info.value=index;
			}
			return index;
		},
		*/
		
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
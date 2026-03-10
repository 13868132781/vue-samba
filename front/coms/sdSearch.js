var template = `
<div  style="border-radius:3px;border:1px solid #fff; overflow:hidden; background-color:#fff; position:relative" :style="doStyle">
	<input ref="input"  @input="doInput" style="border:0px;padding:6px;outline:none;font-size:12px" @focus="doFocus" @blur="doBlur"	placeholder="搜索"/>
	<div v-if="hasVal" style="position:absolute;cursor:pointer;right:30px;top:5px">
		<sdIcon type="closel" :style="'color:'+(isFocus?theme:'#aaa')" @click="doClear()" />
	</div>
	<div style="position:absolute;cursor:pointer;right:5px;top:5px">
		<sdIcon type="sousuo" :style="'color:'+(isFocus?theme:'#aaa')" @click="doSubmit()" />
	</div>
</div>
`;

export default{
	template : template,
	props:{
		value:{
			default:""
		},
		doShadow:{
			default:true,
		}
	},
	data(){
		return {
			oldfunc:null,
			isFocus:false,
			theme: hlc.config.theme,
			hasVal:false,
		}
	},
	mounted(){
		
	},
	computed:{
		doStyle(){
			var b = '';
			if(this.doShadow){
				b+="box-shadow:0 0 2px 1px #aaa;";
			}
			if(this.isFocus){
				b += 'border-color:'+this.theme+';';
			}else{
				if(this.doShadow){
					b += 'border-color:#fff;';
				}else{
					b += 'border-color:#aaa;';
				}
			}
			
			return b;
		},
	},
	methods:{
		doFocus(){
			//this.$emit("onFocus",true);
			this.isFocus=true;
			this.oldfunc = document.onkeydown;
			document.onkeydown = (e) => {
				let _key = window.event.keyCode;
				//!this.clickState是防止用户重复点击回车
				if (_key === 13&&!this.clickState) {
					this.doSubmit();
				}
			};
		},
		doBlur(){
			//this.$emit("onFocus",false);
			this.isFocus=false;
			document.onkeydown = this.oldfunc;
		},
		doInput(){
			if(this.$refs.input.value){
				this.hasVal=true;
			}else{
				this.hasVal=false;
			}
			//this.$emit("onInput",this.$refs.input.value);
		},
		doSubmit(){
			this.$refs.input.focus();
			//alert(this.$refs.input.value);
			this.$emit("onSubmit",this.$refs.input.value);
		},
		doClear(){
			this.$refs.input.value='';
			this.hasVal=false;
			this.$emit("onSubmit",this.$refs.input.value);
		}
		
	}
}
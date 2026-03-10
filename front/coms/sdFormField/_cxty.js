var template = `
<div style="display:flex;;align-items:center;padding:10px">
	<div style="flex:1">
		长度:<input type="text"  @focus="dofocus"  @blur="doblur" v-model="len" style="width:40px; border:1px solid #ccc; border-radius:3px; outline:none;" @input="doChange" />
	</div>
	<div style="flex:1;">
		<input v-model="low" type="checkbox" style="vertical-align:middle;" @change="doChange"/>小写
	</div>
	<div style="flex:1">
		<input v-model="big" type="checkbox" style="vertical-align:middle;" @change="doChange" />大写
	</div>
	<div style="flex:1">
		<input v-model="num" type="checkbox" style="vertical-align:middle;" @change="doChange"/>数字
	</div>
	<div style="flex:1">
		<input v-model="tes" type="checkbox" style="vertical-align:middle;" @change="doChange"/>特殊字符
	</div>
	<div style="flex:1">
		<input v-model="xze" type="checkbox" style="vertical-align:middle;" @change="doChange"/>允许少一个
	</div>
</div>
`;

export default{
	template : template,
	props:{
		info:{
			default:()=>{
				return {};
			}
		}
		
	},
	data(){
		return{
			len:'',
			low:0,
			big:0,
			num:0,
			tes:0,
			xze:0,
			
		}
	},
	created(){
		var vals = (this.info.value||'').split('_');
		for(var t=0;t<vals.length;t++){
			var val = vals[t];
			if(val==''){
				continue;
			}
			if(t==0){
				this.len=val;
			}else{
				this[val]=1;
			}
		}
	},
	methods:{
		dofocus(){
			this.isfocus=true;
			this.$emit("onFocus",true);
		},
		doblur(){
			this.isfocus=false;
			this.$emit("onFocus",false);
		},
		doChange(){
			var val = [this.len];
			if(this.low){
				val.push('low');
			}
			if(this.big){
				val.push('big');
			}
			if(this.num){
				val.push('num');
			}
			if(this.tes){
				val.push('tes');
			}
			if(this.xze){
				val.push('xze');
			}
			
			var value = val.join('_');
			this.info.value = value;
			
			
			this.$emit("onInput", value);
		}
	}
}
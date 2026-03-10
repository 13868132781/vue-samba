/*
多选，横向排列
值:选中的多个id以&连接，未选中的，不保存
*/
var template = `
<div style="display:flex;;align-items:center;padding:10px">
	
	<div style="flex:1;" v-for="v,k in info.options">
		<input type="checkbox" style="vertical-align:middle;" @change="doChange(k)" :checked="myvals[k]?'checked':''"/>{{v}}
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
			myvals:{},
			
		}
	},
	created(){
		var vals = (this.info.value||'').split('&');
		for(var t=0;t<vals.length;t++){
			var val = vals[t];
			if(val==''){
				continue;
			}
			if(!this.info.options[val]){
				continue;//不存在于options的值，清除掉
			}
			this.myvals[val] = true;
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
		doChange(k){
			if(this.myvals[k]){
				this.myvals[k]=false;
			}else{
				this.myvals[k]=true;
			}
			
			var val=[];
			for(var i in this.myvals){
				if(this.myvals[i]){
					val.push(i);
				}
			}
			//alert(val);
			var value = val.join('&');
			this.info.value = value;
			
			
			this.$emit("onInput", value);
		}
	}
}
var template = `
<div style="">
	<input ref="input" type="text" :placeholder="info.holder" :value="info.value" @input="dochange" style="width:100%;border:0px;padding:10px;outline:none;" @focus="dofocus" @blur="doblur" @click="doClick" />
	<div v-if="listshow" style="position:fixed;left:0; right:0; top: 0; bottom:0;z-index:100000; background-color:rgba(0 0 0 0.01)" @click="closelist">
		<div style="position:fixed;background-color:#fff; border-radius:3px;box-shadow:0 0 3px 2px #888;max-height:200px;overflow:auto" class="myscroll" :style="listStyle()" @click="listClick">
			<div v-for="item,index in (info.options||[])" style="padding:5px;">
			{{item}}
			</div>
		
		</div>
	</div>
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
			listshow:false,
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
			//alert(hlc.getKeys(this.$refs.input));
			
			this.$emit("onInput",this.$refs.input.value);
		},
		doClick(){
			this.listshow=!this.listshow;
			
			
			//alert(shangmian+":"+xiamian);
			//var toppx =this.getTop(e);
			//alert(toppx);
		},
		closelist(){
			this.listshow=false;
		},
		listClick(){
			
		},
		listStyle(){
			var listheight=200;
			var e = this.$refs.input;
			var rect =e.getBoundingClientRect();
			var left = rect.left;
			var right = hlc.cWidth-rect.right;
			var ttop = rect.top;
			var bottom = hlc.cHeight-rect.bottom;
			
			var dotop = rect.top+rect.height;
			var dobottom = hlc.cHeight-rect.top;
			if(bottom<200){
				return "left:"+left+"px;right:"+right+"px;bottom:"+dobottom+"px;height:200px";
			}else{
				return "left:"+left+"px;right:"+right+"px;top:"+dotop+"px;height:200px";
			}
		}
	}
}
/*
<!--用一个input来实现聚焦和失焦-->
input不能是hdden，不能display:none,也不能放到display:none的div里
而且设置长宽为0px.依然能看到一小块
只能把边框设置为空
*/
var template = `
<div style="display:flex;flex-direction: row; align-items: center;height:100%;padding:8px">
	<div v-for="v,k in info.options" style="margin-left:10px;display:flex;flex-direction: row; align-items: center;height:100%;cursor:pointer" @mousedown="doMouseDown" @click="doClick(k)">
		<div style="border-radius:12px;border:1px solid #00f;padding:1px;display:inline-block;cursor:pointer" :style="style1(k)">
			<div style="width:12px; height:12px;border-radius:8px;background-color:#fff; " :style="style2(k)"></div>
		</div>

		<span style="margin-left:3px">{{v}}</span>
	</div>
	<input ref="myinput" type="text" style="width:0px;outline:none;height:0px;lineHeight:0px;border: 0px solid #fff" @focus="dofocus" @blur="doblur"/>
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
			theme: hlc.config.theme,
			isMyClick:false,
			myvalue: this.info.value,
			//info.value改变，在filter里不能触发跟新，不知道什么原因
			//所以这里用了个myvalue
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
		style1(value){
			//避免出现''==0的情况。
			if(this.myvalue+"" === value+""){
				return 'border-color:'+this.theme;
			}else{
				return 'border-color:#ddd';
			}
		},
		style2(value){
			if(this.myvalue+"" === value+""){
				return 'box-shadow: inset 0 0 5px 3px '+this.theme;
			}else{
				return 'box-shadow: inset 0 0 4px 2px #ddd';
			}
		},
		doMouseDown(){
			this.isMyClick=true;
		},
		dofocus(){
			this.$emit("onFocus",true);
		},
		doblur(){
			if(!this.isMyClick){
				this.$emit("onFocus",false);
			}
		},
		//点击时，在执行doClick之前input就执行了doblur;
		//所以多次点击时，焦点会闪烁
		//为此引入变量isMyClick
		//执行顺序是：doMouseDown---doblur---doClick
		doClick(k){
			this.isMyClick=false;
			this.$refs.myinput.focus();
			//this.dofocus();
			this.info.value=k;
			this.myvalue = k;
		}
	}
}
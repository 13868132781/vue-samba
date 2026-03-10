var template = `
<span :style="coStyle">{{row[jsCtrl.col]}}</span>
`;

export default{
	template : template,
	
	props:{
		jsCtrl:{
			default:()=>{
				return {}
			}
		},
		row:{
			default:()=>{
				return {}
			}
		},
	},
	computed:{
		myStyle(){//暂未用到，以后若改成包裹在div里，再用到
			var style='';
			
			if(this.jsCtrl.tdInInStyle){
				style+=';'+this.jsCtrl.tdInInStyle;
			}
			
			if(this.jsCtrl.ellipsis){//超出省略
				style+='width:100%;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;';
			}
			if(this.jsCtrl.wordNoBreak){//强制不换行
				style+=';white-space: nowrap;';
			}
			if(this.jsCtrl.wordBreak){//强制换行
				style+=';word-wrap: break-word;;word-break:break-all;';
			}
			return style;
		},
		coStyle(){
			if(this.jsCtrl.color){
				return 'color:'+this.jsCtrl.color;
			}
		},
		getColor(){
			var val = this.row[this.jsCtrl.col];
			if(!this.jsCtrl.dotMap[val]){
				return this.jsCtrl.dotMap['_default_'];
			}
			return this.jsCtrl.dotMap[val];
		}
	}
}
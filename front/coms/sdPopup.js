var template = `
<div>
<!--之前用TransitionGroup，实现淡入淡出，但好像打开没效果，关闭时又由于是全屏淡出，导致中间窗口谈出时，看起来很慢，所以现在改成div，去除淡入淡出效果-->
<div name="hlczoom" tag="div">
	<div v-for="popo,index in popList" :key="index" style="position:fixed; left:0; right:0; top:0; bottom:0; background-color:rgba(0,0,0,0.2); display:flex; justify-content:center; align-items:center">
		
		<div 
		style="
			border-radius:5px; 
			overflow:hidden;
			background-color:#fff; 
			display:flex; 
			flex-direction: column; 
			box-shadow:0 0 5px 3px #888;" 
		:style="wrapStyle(popo)">
			<div style="border-bottom:1px solid #ccc; padding:5px;background-color:#fff; position:relative">
					<sdIcon type="riqi" />
					<span style="margin-left:3px;font-size:16px;">{{popo.name}}</span>
				<div @click="close()" style="position:absolute;right:7px;top:7px;;cursor:pointer">
					<sdIcon type="bohui" />
				</div>
			</div>
			<div style="flex:1;position:relative;display:flex; flex-direction: column;overflow:hidden;">
				<div ref="popupscroll"  style="flex:1;overflow:auto;background-color:#f0f0f0;" :style="bodyStyle(popo)" class="myscroll">
					
					<component v-if="popo.com" ref="refcom" :is="popo.com" :args="popo.args" @onScrollTop="doScrollTop(index,$event)" @onOk="ok(popo,index)" @onClose="close"/>
					
					<div v-if="!popo.com" style="padding:10px">
						<pre v-if="popo.text" style=" white-space: pre-wrap;word-wrap: break-word;;word-break:break-all;">{{popo.text}}</pre>
						<div v-if="popo.html" v-html="popo.html"></div>
					</div>
					
					
				</div>
			</div>
			<div v-if="popo.btnEnable|| popo.com=='sdForm' || popo.com=='sdFilter'" style="height:30px;border-top:1px solid #ccc;display:flex;background-color:#fff">
				<div v-if="popo.btnOkEnable|| popo.com=='sdForm' || popo.com=='sdFilter'"  @click="ok(popo,index)" style="flex:1;display:flex;justify-content:center; align-items:center;cursor:pointer;border-right:1px solid #ccc; font-size:16px">确定</div>
				<div v-if="popo.btnCloseEnable|| popo.com=='sdForm' || popo.com=='sdFilter'" @click="close()" style="flex:1;display:flex;justify-content:center; align-items:center;cursor:pointer; font-size:16px">取消</div>
			</div>
		</div>
		
	</div>
</div>
</div>
`; 

const { reactive, shallowRef, markRaw } = Vue;

export default{
	template : template,
		
	data(){
		return {
			popList:[]
		}
		
	},
	methods:{
		open(opt){
			if(opt.args && opt.args.formList && !opt.com){
				opt.com = 'sdFormEasy';
				opt.btnEnable=true;
				opt.btnOkEnable=true;
				opt.btnCloseEnable=true;
			}
			if(opt.com && typeof opt.com =='object'){
				opt.com = shallowRef(opt.com);
			}
			this.popList.push(opt);
		},
		close(){
			this.popList.pop();
		},
		ok(popo,index){
			if(!this.$refs['refcom'][index].ok){
				this.close();
				return;
			}
			this.$refs['refcom'][index].ok((res)=>{
				//this.popList.pop();
				//this.$emit('ok',res);
				if(popo.ok){
					var back = popo.ok(res);
					if(back){//父组件有反馈，就反馈给框内组件，且不关闭
						return back;
					}else{
						this.close();
					}
				}else{
					this.close();
				}
			});
		},
		wrapStyle(popo){
			var b = 'width:'+popo.width+';height:';
			var height = popo.height;
			if(height=='auto'){
				var max = hlc.cHeight*0.9;
				height="auto;max-height:"+max+"px";
				
			}
			b+=height;
			if(popo.bgColor){
				b+=";background-color:"+popo.bgColor;
			}
			return b;
		},
		bodyStyle(popo){
			var bgColor = "#fff";
			if(popo.bgColor){
				bgColor = popo.bgColor;
			}else if(popo.com=='sdDialog'){
				bgColor = "#f0f0f0";
			}
			return "background-color:"+bgColor;
		},
		doScrollTop(index,num){ 
			//this.$refs.popupscroll[index].scrollTop=num;//没动画
			this.$refs.popupscroll[index].scrollTo({top:num,behavior:'smooth'});
		}
	}
}
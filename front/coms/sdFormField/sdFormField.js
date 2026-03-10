var template = `
<div style="padding:0px;padding-right:0px">
		
	<div style=";display:flex;flex:1;outline:1px solid #666; border-radius:3px; overflow:hidden; background:#fff" :style="isFocus?'outline:2px solid '+theme:''">
		
		<div style="display:flex;align-items: center;">
			<div style="padding-left:10px;padding-right:10px" >
				<sdIcon :type="getIcon()" :color="isFocus?theme:''" :style="isFocus?'font-weight:bold':''" />
			</div>
		</div>
		
		<!--用个div来做边框-->
		<div style="width:0px; overflow:hidden; background:#f00; border-right:1px solid #666; border-left:1px solid #fff" :style="isFocus?'border-left:1px solid '+theme+';border-right:1px solid '+theme:''"></div>
			
		<div style="flex:1;position:relative;">	
			<component ref="inputref" :is="getCom()"
					:info="info" 
					:infos="infos" 
					@onFocus="doFocus" 
					@onInput="doinput"
					/>
		</div>
			
		<div v-if="!info.noClear" style="border-left:1px solid #666;cursor:pointer;display:flex;align-items: center;"  :style="this.info.type=='show'?'background-color:#f0f0f0':''" @click="doClear">
			<div style="padding-left:10px;padding-right:10px">
				<sdIcon type="closel" size='12' color='#666' style="" />
			</div>
		</div>
		
	</div>
</div>
`;

import _show from "./_show.js";
import _text from "./_text.js";
import _password from "./_password.js";
import _yzcode from "./_yzcode.js";
import _select from "./_select.js";
import _datePick from "./_datePick.js";
import _cxty from "./_cxty.js";
import _treePick from "./_treePick.js";
import _table from "./_table.js";
import _radio from "./_radio.js";
import _selectm from "./_selectm.js";
import _selectms from "./_selectms.js";

export default{
	template : template,
	props:{
		info:{
			default:()=>{return {};},
		},
		infos:{
			default:()=>{return {};},
		}
		
	},
	data(){
		return {
			theme: hlc.config.theme,
			
			isFocus: this.info.focus,
			
			icons:{
				'text':'shougongqianshou',
				'password':'mima1',
				'yzcode':'yanzhengma',
				'select':'zhedie-zhankai',
				'ip':'',
				'phone':'',
				'mail':'',
				'datePick':'riqi',
				'user': 'Account',//这不是组件，只是个key-val，给外部定义时用的
				'_default_':'shougongqianshou',
			},
			
			//老数据，有些地方用到
			valueOld : this.info.value
		}
	},
	created(){
		
	},
	computed:{
		
	},
	methods:{
		getCom(){
			var coms={_show,_text,_password,_yzcode,_select,_datePick,_cxty,_treePick,_table,_radio,_selectm,_selectms};
			
			if(!this.info.type){
				this.info.type = 'text';
			}
			
			var com = coms['_'+this.info.type];
			
			return com;
		},
		getIcon(){
			
			if(this.info.icon && this.icons[this.info.icon]){
				return this.icons[this.info.icon];
			}
			
			if(this.icons[this.info.type]){
				return this.icons[this.info.type];
			}
			
			return this.icons['_default_'];
		},
		doinput(val){
			//this.$emit("update:value",val);
			//虽然内层传了val来，但目前还是直接用info.value来响应
			if(this.info.value){//触发了input，value也未必有值，如中文输入时
				//this.info.value可能是数字
				if(typeof(this.info.value)=='string'){
					//去空格的动作，放在了hlc.valid.check里
					//this.info.value = this.info.value.trim();//不允许输入空格
				}
			}
			//只有在已经有错误的情况下，才会逐改逐验
			if(this.info.errMsg){
				hlc.valid.check(this.infos,this.info);
			}
			/*
			if(this.info.relate){
				for(var sy in this.info.relate){
					if(sy=='!' && this.info.value!=this.valueOld){
						for(var k in this.info.relate[sy]){
							this.infos.map((ino)=>{
								if(ino.col==k){
									var shuxin = this.info.relate[sy][k];
									for(var sx in shuxin){
										ino[sx] = shuxin[sx];
									}
								}
							});
						}
					}	
				}
			}
			*/
		},
		doFocus(b){
			this.isFocus = b;
		},
		doClear(){
			if(this.info.type=='show'){
				return;
			}
			this.info.value='';
			if(this.info.xsname){//显示信息，如treePick
				this.info.xsname='';
			}
			
			if(this.$refs.inputref.clear){
				this.$refs.inputref.clear();
			}
		}
		
	}
}
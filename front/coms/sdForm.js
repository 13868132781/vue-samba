var template = `
<div style="padding:20px;" ref="formwrap">
	<table style="width:100%;" cellpadding=0 cellspacing=0>
		<tbody v-for="item,index in myForm" ref="formone">
			<tr v-if="!item.noTitle">
				<td></td>
				<td style="padding-top:5px">
					<div style="margin-top:0px; word-break:keep-all; white-space: nowrap; overflow:hidden;">
			
						<span v-if="item.ask" style="color:#f00;margin-left:1px;font-size:10px">✽</span>
						
						<span v-if="item.errMsg" style="margin-left:10px; font-size:13px;color:#f00"><sdIcon type="yujing" size="12" style="margin-right:3px;color:#f00" />{{item.errMsg}}</span>
						
						<span v-if="item.hint" style="margin-left:10px; font-size:12px;color:#888"><sdIcon type="bangzhu" size="12" style="margin-right:3px" />{{item.hint}}</span>
						
						&nbsp
					</div>
				</td>
			</tr>
			<tr>
				<td style="width:1px; white-space: nowrap;font-weight:bold;" align="right" valign="middle">{{item.name}}：</td>
				<td>
					<sdFormField :info="item" :infos="myForm"/>
				</td>
			</tr>
		</tbody>
	</table>
</div>
`;

export default{
	template : template,
	props:{
		args:{
			default:()=>{return {};}
		}
	},
	data(){
		return {
			myForm:[],
			
			apiSet:{},
			apiSave:{},
			
		}
	},
	mounted(){
		//formType:crudAdd、crudMod、edit、multEdit
		this.apiSet = this.args.formType+'Set';
		this.apiSave = this.args.formType+'Save';
		
		this.getForm();
	},
	methods:{
		getForm(){
			var post = hlc.copy(this.args.post||{});
			hlc.ajax({
				router: this.args.router+"@"+this.apiSet,
				post: post,
				ok:(res)=>{
					if(res.code==0){
						//vue2里没有value成员的话，通过set设置value，也无法反应到input里去
						//而且必须在赋值this.myForm前设置，否则子组件都初始化了，input已经生成
						//vue3好像不需要
						for(var i in res.data){
							if(!('value' in res.data[i])){
								res.data[i].value='';
							}
						}
						this.myForm = res.data;
						//给每项添加hint
						hlc.valid.hint(this.myForm);
					}
				}
			});
		},
		ok(closef){
			//验证表单
			
			
			//检查每项，若有错误，写道myForm里，并返回null
			//否则，返回一个数据的键值对列表
			var back = hlc.valid.check(this.myForm);
			//alert(JSON.stringify(back));
			if(!back){
				this.myForm = JSON.parse(JSON.stringify(this.myForm));
				this.gotoErrPos();
				return;
			}
			
			var post = hlc.copy(this.args.post||{});
			
			post.formVal = back;
			
			if(this.args.askSure){
				if(!confirm(this.args.askSure)){
					return;
				};
			}
			
			hlc.ajax({
				router: this.args.router+"@"+this.apiSave,
				post: post,
				ok:(res)=>{
					if(res.code==0){
						if(closef){
							closef();
						}
						//if(res.data){//若传了数据回来，直接alert
						//	alert(res.data);
						//}
						if(res.js){
							eval(res.js);
						}
					}else{
						var back = res.data;
						this.myForm.map((mf)=>{
							var col = mf.col;
							if(back[col]){
								mf.errMsg = back[col]+'！';
							}
						});
						this.myForm = JSON.parse(JSON.stringify(this.myForm));
						this.gotoErrPos();
					}
				}
			});
		},
		
		gotoErrPos(){
			var errindex=0;
			for(var i in this.myForm){
				if(this.myForm[i].errMsg){
					errindex = i;
					break;
				}
			}
			//滚动到第一个出错的元素上
			var offset = this.$refs.formone[errindex].offsetTop +20;
			//alert(offset);
			this.$emit('onScrollTop',offset);
				
		}
		
	}
}
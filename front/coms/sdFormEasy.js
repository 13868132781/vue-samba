var template = `
<div style="padding:20px;" ref="formwrap">
	<table style="width:100%;" cellpadding=0 cellspacing=0>
		<tbody v-for="item,index in myForm" ref="formone">
			<tr v-if="!item.noTitle">
				<td></td>
				<td style="padding-top:5px">
					<div style="margin-bottom:0px; word-break:keep-all; white-space: nowrap; overflow:hidden;">
			
						<span v-if="item.ask" style="color:#f00;margin-left:1px;font-size:10px">✽</span>
						
						<span v-if="item.errMsg" style="margin-left:10px; font-size:13px;color:#f00"><sdIcon type="yujing" size="12" style="margin-right:3px;color:#f00" />{{item.errMsg}}</span>
						
						<span v-if="item.hint" style="margin-left:10px; font-size:12px;color:#888"><sdIcon type="bangzhu" size="12" style="margin-right:3px" />{{item.hint}}</span>
						
						&nbsp
					</div>
				</td>
			</tr>
			<tr>
				<td style="width:1px; white-space: nowrap;font-weight:bold;" align="right" valign="middle">{{item.name?item.name+'：':''}}</td>
				<td>
					<sdFormField :info="item" :infos="myForm"/>
				</td>
			</tr>
		</tbody>
	</table>
</div>
`;

/*
不是由后台php提供表单格式，结果也不是上传到php
而是由调用sdpopup的地方提供表单，结果也是返回给调用者
*/

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
			
		}
	},
	mounted(){
		
		this.getForm();
	},
	methods:{
		getForm(){
			this.myForm = this.args.formList;
			//给每项添加hint
			hlc.valid.hint(this.myForm);
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
			
			if(closef){
				closef(back);
			}
			
		},
		
		gotoErrPos(){
			var errindex=0;
			for(var i in this.myForm){
				if(this.myForm[i].errMsg){
					errindex = i;
					break;
				}
			}
			//alert(errindex);
			//滚动到第一个出错的元素上
			var offset = this.$refs.formone[errindex].offsetTop +20;
			//alert(offset);
			this.$emit('onScrollTop',offset);
				
		}
		
	}
}
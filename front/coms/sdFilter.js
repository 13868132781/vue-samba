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
				<td style="width:1px"></td>
			</tr>
			<tr>
				<td style="width:1px; white-space: nowrap;font-weight:bold;" align="right" valign="middle">{{item.name}}：</td>
				<td>
					<sdFormField :info="item" :infos="myForm"/>
				</td>
				<td style="width:1px">
					<div style="width:120px;height:38px;margin-left:5px">
						<select ref="opSelect" style="width:100%;height:100%;border:1px solid #888;border-radius:3px;outline:none" >
							<option v-for="opv,opk in getOps(item)" :value="opk" :selected="item.op==opk">{{opv}}</option>
						</select>
					</div>
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
			ops:{
				'=': '等于此值',
				like: '包含此值',
				likeStart: '以此值开头',
				likeEnd: '以此值结尾',
				'!=': '不等于此值',
				notLike: '不包含此值',
				notLikeStart: '不以此值开头',
				notLikeEnd: '不以此值结尾',
				'>': '大于',
				'>=': '大于等于', 
				'<': '小于',
				'<=': '小于等于',
				'dateIn': '日期范围',
			}
		}
	},
	mounted(){
		this.getForm();
	},
	methods:{
		getForm(){
			if(!this.args.router){
				return;
			}
			hlc.ajax({
				router: this.args.router+"@filterSet",
				data: this.args.post,
				ok:(res)=>{
					if(res.code==0){
						//没有value成员的话，通过set设置value，也无法反应到input里去
						//而且必须在赋值this.myForm前设置，否则子组件都初始化了，input已经生成
						//通过下面this.myForm.map里设置，好像也不行
						for(var i in res.data){
							if(!('value' in res.data[i])){
								res.data[i].value='';
							}
						}
						
						this.myForm = res.data;
						var filter={};
						//获取上次筛选的条件
						if(this.args.post && this.args.post.filter){
							filter = this.args.post.filter;
						}
						
						this.myForm.map((mf)=>{
							mf.op = '=';
							if(mf.type=='datePick'){
								mf.op='dateIn';
							}
							if(filter[mf.col]){
								mf.value = filter[mf.col].value;
								mf.op = filter[mf.col].op;
							}
						});
						
						hlc.valid.hint(this.myForm);
					}
				}
			});
			
		},
		getOps(item){
			if(item.type=='select'){
				return {'=':this.ops['=']};
			}else{
				return this.ops;
			}
		},
		
		ok(closef){
			var back={};
			this.myForm.map((mf,i)=>{
				if(hlc.True(mf.value)){
					var show = mf.value;
					if(mf.options){
						show = mf.options[show];
					}
					back[mf.col] = {
						op:this.$refs.opSelect[i].value,
						name: mf.name,
						value: mf.value,
						type: mf.type,
						show: show,//用于显示在页面的值
					}
				}
			});
			if(JSON.stringify(back)=='{}'){
				back=null;
			}
			
			if(closef){
				closef(back);
			}
			
		}
		
	}
}
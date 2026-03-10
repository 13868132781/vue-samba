/*
这是tool栏的一个自定义修改按钮，
修改的是另一张表，对应另一张表的单行edit操作
所有的router，post, key等信息，得在添加按钮时设定
比如，你在用户页面，想添加个按钮，去修改ad服务器的信息
info.post是页面的post和php设定的post的集合
*/
var template = `
<div style="display:inline-block;">
	<sdButton :showType="jsCtrl.showType" :value="jsCtrl.name" :icon="(jsCtrl.icon||'shougongqianshou')" @sdClick="doClick" />

</div>
`;

export default{
	template : template,
	
	props:{
		jsCtrl:{
			default:()=>{
				return {}
			}
		},
	},
	
	methods:{
		doClick(){
			
			if(this.jsCtrl.jsBefore){
				this.jsCtrl.post = this.jsCtrl.post||{};
				var jsBefore = null;
				eval("jsBefore="+this.jsCtrl.jsBefore);
				var b = jsBefore(this.jsCtrl);
				if(!b){
					return;
				}
			}
			
			var router = this.jsCtrl.router;
			var post = hlc.copy(this.jsCtrl.post||{});
			var popArgs={
				router : router,
				post:  post,
				formType : 'edit',
			};
			
			hlc.popup.open({
				com:'sdForm',
				name: this.jsCtrl.popTitle,
				width:this.jsCtrl.popWidth||'70%',
				height:this.jsCtrl.popHeight||'70%',
				args: popArgs,
			});
		}
	}
	
}
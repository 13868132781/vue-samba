var template = `
<div style="padding:10px;">
	
	<div>
		<input ref="sdfile" type="file" @change="inputchange($event)" style="border:1px solid #666;width:100%"/>
	</div>
	<div style="padding-top:20px">
		<button @click="doUpload()">上传文件</button>
		<button @click="doDelete()" v-if="args.post.val" style="margin-left:20px">删除现有文件</button>
	</div>
	
	
	<div v-if="doing==1" style="
	position:absolute; left:0;right:0;top:0;bottom:0; 
	display:flex; justify-content:center; align-items:center;">
		<div style="display:inline-block; " class="sdRotation" >
			<sdIcon type="loading" size="30"  />
		</div>
	</div>
	
	
</div>
`;
export default{
	template : template,
	props:{
		args:{
			default:{},
		}
	},
	data(){
		return {
			doing:0,
			fileobj:null,
		}
	},
	methods:{
		doDoing(d){
			this.doing = d;
		},
		inputchange(e){
			this.fileobj = e.currentTarget.files[0];
		},
		doUpload(){
			var form = new FormData();
			form.append( "sdfile" , this.fileobj);
			if(this.args.post){
				for(var k in this.args.post){
					form.append( k , this.args.post[k]);
				}
			}
			hlc.ajax({
				router: this.args.router+"@uploadAdd",
				post: {},
				upload:form,
				//silent:true,
				ok:(res)=>{
					if(res.code){
						//alert(JSON.stringify(res));
					}else{
						this.$emit('onOk','');//触发pop的确定按钮
						//this.myData = res.data;
					}
				}
			});	
			
		},
		doDelete(){
			if(!confirm("确定要删除旧文件么")){
				return;
			}
			
			var post = hlc.copy(this.args.post);
			post.auditOper+="删除";
			hlc.ajax({
				router: this.args.router+"@uploadDel",
				post: post,
				//silent:true,
				ok:(res)=>{
					if(res.code){
						//alert(res.);
					}else{
						this.$emit('onOk','');//触发pop的确定按钮
						//this.myData = res.data;
					}
				}
			});	
		},
		ok(func){//给pop用的
			if(func){
				func();
			}
		}
	}
}
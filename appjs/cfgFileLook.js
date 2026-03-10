var template = `
<div >
	<div style="border-bottom:1px solid #888;padding:5px;margin-bottom:5px">
		<table>
			<tr>
				<td v-for="tl in tabList" @click="tabClick(tl.id)" :style="tl.id==tabName?'color:#00f;font-weight:bold':''" style="padding-left:10px;padding-right:10px;cursor:pointer">{{tl.name}}</td>
			</tr>
		</table>
		
	</div>

	<div>
		<table>
			<tr v-for="md in myData" :style="md.color">
				<td>{{md.left}}</td>
				<td v-if="md.right">{{md.right}}</td>
			</tr>
		</table>
	</div>
	
	<div style="position:absolute;right:30px;top:50px">
		<button @click="dowload()">下载</button>
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
			tabName:'1',
			tabList:[
				{name:'启动配置',id:'1'},
				{name:'运行配置',id:'2'},
				{name:'启动冲突',id:'3'},
				{name:'运行冲突',id:'4'},
				{name:'启运冲突',id:'5'},
			],
			
			myData:null,
		}
	},
	created(){
		this.fetchData();
	},
	methods:{
		tabClick(id){
			this.tabName = id;
			this.fetchData();
		},
		fetchData(){
			this.args.post.fileNum = this.tabName;
			this.$emit('onDoing','1');
			hlc.ajax({
				router: this.args.router+"@fetch",
				post: this.args.post,
				silent:true,
				ok:(res)=>{
					//alert(JSON.stringify(res));
					this.$emit('onDoing','0');
					this.myData = res.data;
				}
			});	
			
		},
		dowload(){
			
			
			var post = hlc.copy(this.args.post);
			post.fileNum = this.tabName;
			post.goto='cfgxz';
			this.$emit('onDoing','1');
			hlc.ajax({
				router: this.args.router+"@download",
				post: post,
				silent:true,
				ok:(res)=>{
					this.$emit('onDoing','0');
					if(res.code==0){
						window.location.href=hlc.entry+'?router='+this.args.router+"@download&filePath="+res.data[0]+"&fileName="+res.data[1];
					}
				}
			});	
			
		}
	}
}
var template = `
<div style="padding:10px;" ref="winwrap">
	<div style="border-bottom:1px solid #888;padding:5px;margin-bottom:5px">
		<table>
			<tr>
				<td v-for="tl in tabList" @click="tabClick(tl.id)" :style="tl.id==tabName?'color:#00f;font-weight:bold':''" style="padding-left:10px;padding-right:10px;cursor:pointer">{{tl.name}}</td>
			</tr>
		</table>
		
	</div>
	
	
	<div v-if="tabName=='create'">
		<table>
			<tr v-for="fo in myFormIndex">
				<td>{{myForm[fo].name}}:</td>
				<td><input style="width:200px" ref="plcjValue" />
					<input type="checkbox" ref="plcjMore"  />添加后缀数值</td>
			</tr>
			<tr>
				<td>批量范围:</td>
				<td>起始数值:<input style="width:100px" value="0" ref="plcjStart" />
					结束数值:<input style="width:100px" value="10" ref="plcjStop" /></td>
			</tr>
		</table>
		<div style="text-align:right">
			<button @click="plcjMake()">生成数据</button>
			<button @click="myImport()">执行创建</button>
		</div>
	</div>
	
	<div v-if="tabName=='import'">
	
		<div>格式示例：</div>
		<div style="box-shadow:0 0 2px 1px #eee;margin:10px" >
		<table class="sdGridTable" cellspacing="0" cellpadding="7">
			<tr>
				<td v-for="fo in myFormIndex">{{myForm[fo].name}}</td>
			</tr>
			<tr>
				<td v-for="fo in myFormIndex">
					<span v-if="!myForm[fo].options">xxx</span>
					<select v-if="myForm[fo].options">
						<option v-for="foo,ind in myForm[fo].options">{{foo}}</option>
					</select>
				</td>
			</tr>
		</table>
		</div>
		
		<div>
			请选择csv文件:
		
			<input ref="sdfile" type="file" @change="inputchange($event)" style="border:1px solid #ccc"/>

			<button @click="doUpload()">导入文件</button>
		</div>
	
	</div>
	
	
	<div style="box-shadow:0 0 2px 1px #eee;margin:10px" >
	<table class="sdGridTable" cellspacing="0" cellpadding="7">
		<tr v-for="mrs,index in myResult" ref="winItemOne"> 
			<td style="width:30px">{{index}}</td>
			<td v-for="mr in mrs">{{mr}}</td>
		</tr>
	</table>
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
			tabName:'import',
			tabList:[
				{name:'导入CSV',id:'import'},
				{name:'批量创建',id:'create'},
			],
			
			doing:0,
			fileobj:null,
			
			myForm:[],
			myFormIndex:[],
			myResult:[],
			
			myImportIndex:-1,//当前导入的索引号
			
		}
	},
	mounted(){
		this.getForm();
	},
	methods:{
		doDoing(d){
			this.doing = d;
		},
		getForm(){
			hlc.ajax({
				router: this.args.router+"@crudAddSet",
				post: {},
				ok:(res)=>{
					if(res.code==0){
						this.myForm = res.data;
						hlc.valid.hint(this.myForm);
						this.myFormIndex = [];
						for(var i in this.myForm){
							if(this.myForm[i].import){
								this.myFormIndex.push(i);
							}
						}
					}
				}
			});
		},
		
		tabClick(id){
			this.tabName = id;
		},
		
		plcjMake(){
			this.myResult=[];
			
			var plcjVals = this.$refs.plcjValue;
			var start = this.$refs.plcjStart.value;
			var stop = this.$refs.plcjStop.value;
			for(var i=start;i<=stop;i++){
				var resulto=[];
				for(var j =0;j<plcjVals.length;j++){
					var val = plcjVals[j].value;
					if(this.$refs.plcjMore[j].checked){
						val+=i;
					}
					resulto.push(val);
				}
				this.myResult.push(resulto);
			}
			
			//alert(this.$refs.plcjStop.value);
		},
		
		
		
		inputchange(e){
			this.fileobj = e.currentTarget.files[0];
		},
		doUpload(){
			if(!this.fileobj){
				alert("请选择文件");
				return;
			}
			this.myResult=[];
			var reader = new FileReader();//新建一个FileReader
            reader.onload = (evt)=>{ //读取完文件之后会回来这里
				var fileString = evt.target.result; // 读取文件内容
                //alert(fileString);
				var lines = fileString.split('\n');
                for (var i = 1; i < lines.length; i++) {
					if(lines[i].trim()==''){
						continue;
					}
                    var linea=this.csvLine(lines[i]);
					var resulto = [];
					for(var l=0;l<linea.length;l++){
						resulto.push(linea[l].replace(/^"|"$/gm,''));
					}
					this.myResult.push(resulto);
                }
				this.myImport();
            }
			reader.readAsText(this.fileobj, "gb2312");//读取文件 
		},
		csvLine(str){//分解一行csv数据,数据里有双引号的没处理
			var lines=[];
			var one =''; //一个数据
			var dl = 0;  //是否有左侧双引号
			for (let i = 0; i < str.length; i++) { 
				let s = str[i];
				if(s=='"'){
					if(dl){
						if(str[i+1]=='"'){//下一个还是双引号，表示没有结束
							one+=s;
							i++;
						}else{//表示双引号结束
							dl=0;
						}
					}else{//没有左侧双引号
						if(one==''){//没有数据，即双引号在暑假开通
							dl=1;
						}else{//在数据中间
							one+=s;
						}
					}
					continue;
				}
				if(s==','){
					if(dl){
						one+=s;
					}else{
						lines.push(one);
						one='';
					}
					continue;
				}
				one+=s;
			}
			lines.push(one);
			return lines;
		},
		
		
		myImport(){
			var post={};
			post.excelVals = this.myResult;
			
			hlc.ajax({
				router: this.args.router+"@crudAddImport",
				post: post,
				ok:(res)=>{	
					var data = res.data;
					for(let w=0;w<this.myResult.length;w++){
						if(data[w].code==0){
							this.myResult[w].push('成功');
							this.myResult[w].push('');
						}else{
							this.myResult[w].push('失败');
							this.myResult[w].push(data[w].data);
						}
					}	
				}
			});	
			
		},
		
		//并发ajax开导入
		myImportAjax(){
			this.myImportIndex=-1;
			for(var i=0;i<10;i++){
				this.myImportIndex++;
				this.myImportOne();
			}
		},
		
		
		myImportOne(){
			let w = this.myImportIndex;
			if(w >= this.myResult.length ){
				return;
			}
			let linea = this.myResult[w];
			
			var t=0;
			var back={};
			for(var i=0;i<this.myForm.length;i++){
				var mf = this.myForm[i];
				if(mf.import){
					back[mf.col] = linea[t];
					t++;
				}else if(mf.importValue){
					back[mf.col] = mf.importValue;
				}else if(mf.value){
					back[mf.col] = mf.value;
				}
			}
				
			//alert(JSON.stringify(back));
				
			var post={};
			post.formVal = back;
			post.isImport = true;
				
			hlc.ajax({
				router: this.args.router+"@crudAddSave",
				post: post,
				ok:(res)=>{
					if(res.code==0){
						linea.push('成功');
						linea.push('');
					}else{
						var errs = res.data;
						linea.push('失败');
						linea.push(JSON.stringify(errs));
					}
					
					//隔20个刷新一下滚动条
					if(w%20==0){
						var offset = this.$refs.winItemOne[w].offsetTop - this.$refs.winwrap.offsetTop;
						//alert(offset);
						this.$emit('onScrollTop',offset);
					}
					
					this.myImportIndex++;
					this.myImportOne();
						
				}
			});	
			
		}
		
		
		
	
	}
}
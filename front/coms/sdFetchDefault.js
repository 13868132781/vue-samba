var template = `
<div>
<div v-if="typeof myData != 'object'" style="white-space: pre-wrap;" v-html="myData"></div>
<table v-if="typeof myData == 'object'" style="width:100%" cellpadding="3">
	<tr v-if="!myHeaderhidden">
		<td v-for="h in myHeader" style="word-wrap: break-word; word-break:break-all;" :style="h.tdStyle">
		{{h.name}}
		</td>
	</tr>
	<tr v-for="md in myData">
		<td v-for="h in myHeader" style="word-wrap: break-word; word-break:break-all;" :style="h.tdStyle">
		<div v-if="!h.type||h.type=='text'" :style="h.inStyle">{{md[h.col]}}</div>
		<div v-if="h.type=='html'" :style="h.inStyle"  v-html="md[h.col]"></div>
		<div v-if="h.type=='fetch'" :style="h.inStyle"  @click="doTdClick(h,md)" style="cursor:pointer">打开</div>
		</td>
	</tr>
</table>
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
			myHeaderhidden:false,
			myHeader:[],
			myData:null,
			
		}
	},
	created(){
		this.fetchData();
	},
	methods:{
		fetchData(){
			if(this.args.showColVal){
				this.fetchDataSuccess(this.args.post.val);
				return ;
			}
			
			this.$emit('onDoing','1');
			hlc.ajax({
				router: this.args.router+"@fetch",
				post: this.args.post,
				silent:true,
				ok:(res)=>{
					//alert(JSON.stringify(res));
					this.$emit('onDoing','0');
					
					this.fetchDataSuccess(res.data);
				}
			});	
			
		},
		
		fetchDataSuccess(data){
			if(data===null||typeof data != 'object'){
				this.myData = data;
				return;
			}
			
			if(data.length>0){
				if(data[0]['_is_gridSet_']){
					this.myHeader = data[0]['columns'];
					data.shift(); 
				}else{
					var myHeader=[];
					for(var col in data[0]){
						myHeader.push({
							col:col,
							name:col,
							type:'text',
						});
					}
					if(Array.isArray(data[0])){
						this.myHeaderhidden = true;
					}
					this.myHeader = myHeader;
				}
			}		
			this.myData = data;
		},
		
		doTdClick(h,line){
			var popArgs = hlc.copy(h);
			popArgs.post = {};
			popArgs.post.val = line[h.col];
			popArgs.post.key = line.key;//一行里的key
			
			hlc.popup.open({
				com:"sdFetch",
				name: h.popTitle||h.name,
				width: h.popWidth||'70%',
				height: h.popHeight||'70%',
				args: popArgs,
			});
			
		}
	}
}
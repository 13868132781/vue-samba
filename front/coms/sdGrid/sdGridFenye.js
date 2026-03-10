var template = `
	<div style="padding:10px">
		<div  style="overflow:hidden;zoom:1;">
			<div style="float:left">共{{fenyeInfo.total}}条</div>
			
			
			<div  v-if="Math.ceil(this.fenyeInfo.total/this.fenyeInfo.num)>1" style="float:right; background-color:#f8f8f8; text-align:center; border-radius:5px;box-shadow:0 0 2px 1px #eee;border:1px solid #ccc;margin:2px; display:flex;">
				<div style="display:flex;padding-left:3px;padding-right:3px">
					<div v-for="item,num in pagenum" @click="pageclick(item)" style="float:left; width:36px; height:36px;display:flex; align-items:center;justify-content:center" >
						
						<div style="width:30px;height:30px;border-radius:3px;cursor:pointer; display:flex; align-items:center; justify-content:center" :style="(item==fenyeInfo.now?'color:#fff;background-color:'+theme+';':'color:#666;background-color:#eee')">
						<sdIcon v-if="item=='<'" type="zuobian" size='14' style="color:inherit; font-weight:bold"/>
						<sdIcon v-if="item=='>'" type="youbian" size='14' style="color:inherit; font-weight:bold"/>
						<span v-if="item!='<'&&item!='>'" style="color:inherit;">{{item}}</span>
						</div>
					</div>
				</div>
				
				<div style="border-radius:5px; background-color:rgba(0,0,0,0.0); display:inline-block;width:80px; height:36px; padding-left:10px; padding-right:10px;box-shadow:0px 0px 2px 1px #bbb">
					<select ref="selectys"  @change="dochange" style="height:100%;width:100%; outline:none; border:0px solid #ddd; background-color:rgba(0,0,0,0); ">
						<option v-for="index in Math.ceil(this.fenyeInfo.total/this.fenyeInfo.num)" :selected="fenyeInfo.now==index?true:false" :value="index">{{index}}</option>
					</select>
				</div>
			</div>
			
			
			
		</div>
	</div>
`;

export default{
	template : template,
	props:{
		gridSet:{
			default:()=>{
				return {};
			}
		},
		fenyeInfo:{
			default:()=>{
				return {};
			}
		},
		
		
	},
	data(){
		return {
			theme: hlc.config.theme,
			
			fyStart:0,
			fyEnd:0,
			fyMax:0,
			fyOffSet:0,
		}
	},
	computed:{
		pagenum(){
			var tnum = Math.ceil(this.fenyeInfo.total/this.fenyeInfo.num);
			var now = this.fenyeInfo.now ;
			var start = now-4;
			var end = now+4;
			if(start<1){
				end-=(start-1);
				start = 1;
			}
			if(end>tnum){
				start -= (end-tnum);
				if(start<1){
					start = 1;
				}
				end = tnum;
			}
			
			start += this.fyOffSet;
			end += this.fyOffSet
			this.fyStart = start;
			this.fyEnd = end;
			this.fyMax = tnum;
			
			var list=['<'];
			for(var i = start;i<=end;i++){
				list.push(i);
			}
			list.push('>');
			
			return list;
		},
	},
	methods:{
		pageclick(n){
			if(n=='<'){
				if(this.fyStart>1){
					this.fyOffSet-=1;
				}
			}else if(n=='>'){
				if(this.fyEnd<this.fyMax){
					this.fyOffSet+=1;
				}
			}else{
				this.fyOffSet=0;
				this.fenyeInfo.now = n;
				this.$emit('getData');
			}
		},
		dochange(){
			this.$refs.selectys.blur();
			var n = parseInt(this.$refs.selectys.value);
			if(!n){
				return;
			}
			this.fyOffSet=0;
			this.fenyeInfo.now = n;
			this.$emit('getData');
		}
	}
}
var template = `
<div>
	<div style="padding:5px;display:flex;align-items:center;padding-left:10px;padding-right:10px">
		
		<div v-if="dateType=='2'" style="display:innerblock;cursor:pointer"  @click="setArea()">
			<input type="checkbox" :checked="boxCheck?'':'checked'" />单天
		</div>
		
		<div style="flex:1;padding-left:40px">
			选定时间：{{word}}
		</div>
		
		<div style="padding:5px;border:1px solid #ccc; background-color:#f0f0f0;cursor:pointer; width:60px;text-align:center" @click="doClear()">清空</div>
		
		
	</div>
	<div>
		<datePickPopOne ref="dateref1" :args="dateData1" @onInput="myInput()" />
	</div>
	<div v-show="dateType>0" style="position:relative">
		<datePickPopOne ref="dateref2" :args="dateData2" @onInput="myInput()"/>
		<div v-if="!boxCheck" style="position:absolute; left:0px; right:0px; top:0px; bottom:0px;background-color:#f0f0f0"></div>
	</div>
</div>
`;

import datePickPopOne from './_datePickPopOne.js';

export default{
	template : template, 
	components:{datePickPopOne},
	props:{
		args:{
			default:()=>{return {};},
		}
	},
	data(){
		return {
			dateData1:'',
			dateData2:'',
			
			word:this.args.value,
			
			dateType: 0,//0单天 1区域 2 可选
			boxCheck: false,//是单天还是区域
			
		}
	},
	mounted(){
		this.dateType = this.args.dateType||0;
		if(this.dateType>0){
			this.boxCheck=true;
		}
		var val = this.args.value;
		if(val){
			if(val.indexOf('~')==-1){
				this.boxCheck=false;
			}
			var vals=val.split("~");
			this.$refs.dateref1.setDate(vals[0]);
			if(vals.length>1 && vals[1]){
				this.$refs.dateref2.setDate(vals[1]);
			}	
		}
		
	},
	computed:{
		

	},
	methods:{
		doClear(){
			this.$refs.dateref1.setDate('');
			this.$refs.dateref2.setDate('');
			this.myInput();
		},
		
		setArea(){
			this.boxCheck = !this.boxCheck;
			this.myInput();
		},
		
		
		myInput(){
			var word = '';
			var date1 = this.$refs.dateref1.getDate();
			var date2 = this.$refs.dateref2.getDate();
			if(this.dateType==0 || (this.dateType==2 && !this.boxCheck)){
				word = date1;
			}else if(date1||date2){
				word = date1+'~'+date2;
			}
			this.word = word;
		},

		ok(f){
			var word = '';
			var date1 = this.$refs.dateref1.getDate();
			var date2 = this.$refs.dateref2.getDate();
			if(this.dateType==0 || (this.dateType==2 && !this.boxCheck)){
				word = date1;
			}else if(date1||date2){
				word = date1+'~'+date2;
			}
			if(f){
				f({value:word});
			}
		}
	}
}
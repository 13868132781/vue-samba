var template = `
<div>
<div style="background-color:#f8f8f8;display:flex;height:220px">
	<div style="flex:1;height:100%;position:relative">
		<div style="border:1px solid #ccc; position:absolute; left:10px; right:5px; top:0px; bottom:10px;background-color:#fff">
			<div style="width:20%; float:left; text-align:center; padding-top:5px; padding-bottom:5px; background-color:#f8f8f8;font-weight:bold">年</div>
			<div v-for="year in areaYear" style="width:20%; float:left; text-align:center; padding-top:5px; padding-bottom:5px; cursor:pointer" :style="year==xzyear?'background-color:#90EE90':''" @click="onClick('year',year)">
			{{year}}
			</div>
		</div>
		<div style="position:absolute;left:10px;bottom:10px;padding:5px;border:1px solid #ccc;background-color:#f0f0f0;cursor:pointer" @click="yearMove(-15)">前移</div>
		<div style="position:absolute;right:5px;bottom:10px;padding:5px;border:1px solid #ccc;background-color:#f0f0f0;cursor:pointer" @click="yearMove(15)">后移</div>
	</div>
	<div style="flex:1;height:100%;position:relative">
		<div style="border:1px solid #ccc; position:absolute; left:0px; right:5px; top:0px; bottom:10px;background-color:#fff">
			<div style="width:20%; float:left; text-align:center; padding-top:5px; padding-bottom:5px; background-color:#f8f8f8;font-weight:bold">月</div>
			<div v-for="month in areaMonth" style="width:20%; float:left; text-align:center; padding-top:5px; padding-bottom:5px; cursor:pointer" :style="month==xzmonth?'background-color:#90EE90':''" @click="onClick('month',month)">
			{{month}}
			</div>
		</div>
	</div>
	<div style="flex:1;height:100%;position:relative">
		<div style="border:1px solid #ccc; position:absolute; left:0px; right:10px; top:0px; bottom:10px;background-color:#fff">
			<div style="width:20%; float:left; text-align:center; padding-top:5px; padding-bottom:5px;background-color:#f8f8f8;font-weight:bold">日</div>
			<div v-for="day in areaDay" style="width:20%; float:left; text-align:center; padding-top:5px; padding-bottom:5px; cursor:pointer" :style="day==xzday?'background-color:#90EE90':''" @click="onClick('day',day)">
			{{day}}
			</div>
		</div>
	</div>
</div>
</div>
`;

export default{
	template : template, 
	props:{
		value:{
			default:()=>{return '';},
		}
	},
	data(){
		return {
			xzyear : '',
			xzmonth: '',
			xzday: '',
			nyear: (new Date()).getFullYear(),
			allday: 31,
		}
	},
	created(){
		//this.setDate();
	},
	computed:{
		
		areaYear(){
			var back=[];
			var nyear = this.nyear;
			for(var i=nyear+14;i>nyear-15;i--){
				back.push(i);
			}
			return back;
		},
		areaMonth(){
			var back=[];
			for(var i=1;i<=12;i++){
				let mi = i;
				if(mi<10){
					mi = '0'+mi;
				}
				back.push(mi);
			}
			return back;
		},
		areaDay(){
			var back=[];
			for(var i=1;i<=this.allday;i++){
				let mi = i;
				if(mi<10){
					mi = '0'+mi;
				}
				back.push(mi);
			}
			return back;
		},
	},
	methods:{
		setDate(val){
			if(val){
				var vals=val.split("-");
				this.xzyear = vals[0];
				this.xzmonth = vals[1];
				this.xzday = vals[2];
			}else{
				this.xzyear='';
				this.xzmonth='';
				this.xzday='';
			}
		},
		
		getDate(){
			var word = '';
			if(this.xzyear && this.xzmonth && this.xzday){
				word = this.xzyear+"-"+this.xzmonth+"-"+this.xzday;
			}
			return word;
		},
		yearMove(n){
			var offset = this.xzyear - this.nyear;
			this.nyear = this.nyear+n;
			this.xzyear = this.nyear+offset;
		},
		onClick(type,val){
			if(type=='year'){
				this.xzyear=val;
			}else if(type=='month'){
				this.xzmonth=val;
			}else{
				this.xzday=val;
			}
			if(!this.xzyear){
				this.xzyear = (new Date()).getFullYear();
			}
			if(!this.xzmonth){
				this.xzmonth='01';
			}
			if(!this.xzday){
				this.xzday='01';
			}
			
			this.jsdays();
			this.$emit("onInput",'');
		},
		jsdays(){
			this.allday = new Date(this.xzyear, this.xzmonth, 0).getDate();
			if(this.xzday>this.allday){
				this.xzday = this.allday;
			}
		},
	}
}
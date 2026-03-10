var template = `
<div style="display:flex;">
	<div style="flex:1;">
		<card v-show="showCPU">
			<div  style="display:flex;">
				<div ref="ybp0" style="flex:1;" :style="'height:'+ybpheight+'px'"></div>
				<div ref="ybp1" style="flex:1;" :style="'height:'+ybpheight+'px'"></div>
				<div ref="ybp2" style="flex:1;" :style="'height:'+ybpheight+'px'"></div>
			</div>
		</card>
		<card v-for="datao,i in myData.left" :key="'left_'+i">
			<mybiao :info="datao" />			
		</card>
	</div>
	
	
	<div style="flex:1;">
		<card v-for="datao,i in myData.right"  :key="'right_'+i">
			<mybiao :info="datao" />
		</card>
	</div>
</div>
`;

import card from "./card.js";
import mybiao from "./mybiao.js";

export default{
	template : template,
	components:{card,mybiao},
	props:{
		show:{
			default:false,
		}
	},
	data(){
		return {
			myData:{},
			cpuMemDisk:false,
			myShow:false,
			ybpheight:0,
			
			STO:null,
		}
	},
	created(){
		//this.$emit('onTopShow',false);由menu控制
		this.getData();
	},
	mounted(){
		
		
		/*
		if(this.$refs && this.$refs.ybp0){
			this.ybpheight = this.$refs.ybp0.offsetWidth;
			this.$nextTick(()=>{
				var cpuMemDisk = [['cpu',0,0],['内存',0,0],['硬盘',0,0]];
				for(var i in cpuMemDisk){
					this.myDraw(i,cpuMemDisk[i]);
				}
			});
			this.getCPU();
		}
		*/
	},
	computed:{
		showCPU(){
			if(!this.cpuMemDisk){
				return false;
			}
			this.$nextTick(()=>{
				this.ybpheight = this.$refs.ybp0.offsetWidth;
				this.$nextTick(()=>{
					var cpuMemDisk = this.cpuMemDisk;
					for(var i in cpuMemDisk){
						this.myDraw(i,cpuMemDisk[i]);
					}
				});
			});
			return true;
		}
	},
	beforeUnmount(){
		if(this.STO){
			clearTimeout(this.STO);
		}
	},
	methods:{
		getData(){
			hlc.ajax({
				router:"/main/main@fetch",
				post:{goto:'sysStaus','auditOper':'获取统计信息'},
				ok:(res)=>{
					if(res.code==0){
						this.myData = res.data;
						if(!this.cpuMemDisk ){
							this.cpuMemDisk = [['cpu',0,0],['内存',0,0],['硬盘',0,0]];
						}
						this.getCPU();
					}
				}
			});
		},
		
		getCPU(){
			hlc.ajax({
				router:"/main/main@fetch",
				post:{
					'goto': 'sysResouse',
					'auditOper': '获取系统资源',
					'isCronRequest': true,
				},
				silent:true,
				ok:(res)=>{
					if(res.code==0){
						this.cpuMemDisk = res.data;
					}
					//定时去后台获取CPU
					//这里不能做定时，否则超时退出功能会失效
					this.STO=setTimeout(()=>{this.getCPU();},60*1000);
				}
			});
			
		},
		
		myDraw(i,data){
			var name=data[0];
			var rate=data[1];
			var total=data[2]||'';
			var mydom = this.$refs['ybp'+i];
			
			var myChart = echarts.init(mydom);
			var option = {
				title:{
					text:name+total,
					bottom:0,
					left:'center',
					textStyle:{
						fontSize:12,
						width:'100%',
						fontWeight:'normal',
						align:'right'
					}
					
				},
				tooltip : {
					formatter: "{b} : {c}%"
				},
				toolbox: {
					feature: {
						//restore: {},
					   // saveAsImage: {}
					}
				},
				series: [
					{
						name: '业务指标',
						type: 'gauge',
						axisLine: {
							lineStyle: { 
								width: 10 // 这个是修改宽度的属性
							}
						},
						splitLine:{
							//show:false,
							length:10
						},
						axisLabel:{
							//show:false
							distance:0,
							fontSize:6
						},
						splitNumber:5,
						pointer:{
							length:'60%',
							width:4
						},
						itemStyle:{
							color:'#00f'
						},
						detail: {
							formatter:'{value}%',
							fontSize:12
						},
						data: [{value: rate, name: ''}]
					}
				]
			};

			myChart.setOption(option, true);
		}
		
	}
}
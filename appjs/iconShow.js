var template = `
<div  >
	<div v-for="data,index in myData" style="display:flex">
		<div @click="doClick(index)" style="padding:5px;margin:5px;cursor:pointer" :style="'color:'+(index==dataIndex?'#00f':'')">{{index}}</div>
	</div>
	<div v-if="dataIndex" style="width:100%;display:flex;flex-direction: row;flex-wrap:wrap;">
		<div v-for="icono in myData[dataIndex].glyphs" style="display:flex;flex-direction: column;jusify-items:center;margin:10px;width:100px;height:100px; over-flow:hidden">
			<div style="text-align:center">
			<div style="margin:10px; display:inline-block; background:#fff; box-shadow:0 0 2px 1px #666">
				<sdIcon :type="icono.font_class" size="50" /> 
			</div>
			</div>
			<div style="text-align:center;word-break: break-all;">{{icono.font_class}}</div>
		</div>
	</div>
</div>
`;
export default{
	template : template,
	props:{
		args:{
			default:()=>{
				return {};
			},
		}
	},
	data(){
		return {
			dataIndex:'',
			myData:{},
		}
	},
	created(){
		this.fetchData();
	},
	methods:{
		fetchData(){
			fetch('/front/coms/sdIcon/sdIcon.json')
			.then((response) => response.json())
			.then((json) => {
				this.iconList = json;
				for(var i in json){
					let name = json[i];
					fetch('/front/coms/sdIcon/'+name+'/iconfont.json')
					.then((response) => response.json())
					.then((json) => {
						if(!this.dataIndex){
							this.dataIndex = name;
						}
						this.myData[name] = json;
						
					});
				}
			});
		},
		doClick(index){
			this.dataIndex = index;
		}
	}
}
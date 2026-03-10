var template = `
<div>
<div  style="position:fixed;right:10px;top:55px;  z-index:99999999999999"  >
<TransitionGroup name="hlczoom" tag="div">
	<div v-for="item,key in info" :key="key" style="padding:10px; margin:5px; border-radius:3px; cursor:pointer; color:#fff;" @click="doClick(key)" :style="'background-color:'+(item.code==0?'#385E0F':'#f00')">
	{{item.msg}}
	</div>
</TransitionGroup>
</div>
</div>
`;


export default{
	template : template, 
	data(){
		return {
			code:0,
			msg:'',
			info:{},
		}
	},
	created(){
		hlc.$on('onSdReqMsg',(e)=>{//alert(JSON.stringify(e.detail));
			let key = Date.now(); 
			
			this.info[key] =e.detail ;
			if(e.detail.code==0){
				setTimeout(()=>{
					delete this.info[key];
				},3000);
			}
		});
		
	},
	methods:{
		doClick(key){
			delete this.info[key];
		}
	}
	
}
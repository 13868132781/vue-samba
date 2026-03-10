var template = `
<div>
<div v-if="show" :title="show" style="position:fixed;right:10px;top:55px;z-index:99999999999999">
	<sdIcon type="yujing" size="30" color="#f00" />
</div>
</div>
`;


export default{
	template : template, 
	data(){
		return {
			show:false,
		}
	},
	created(){
		hlc.$on('onSdNetError',(b)=>{
			this.show=b.detail;			
		});
		
	}
	
}
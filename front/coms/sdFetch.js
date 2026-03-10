var template = `
<div style="padding:0px;">
	
	<component :is="'sd_page_'+(args.zdyCom||'fetchDefault')" :args="args" @onDoing="doDoing" />
	
	
	<div v-if="doing==1" style="
	position:absolute; left:0;right:0;top:0;bottom:0; 
	display:flex; justify-content:center; align-items:center;">
		<div style="display:inline-block; " class="sdRotation" >
			<sdIcon type="loading" size="30"  />
		</div>
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
			doing:0,
		}
	},
	methods:{
		doDoing(d){
			this.doing = d;
		}
	}
}
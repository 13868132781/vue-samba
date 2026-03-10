var template = `
<div>
	<sdGrid v-if="myArgs.router && !myArgs.com && !myArgs.tabList" :router="getRouter()" :post="getPost()" @onLookForAll="$emit('onLookForAll',$event)" />
	
	<component v-if="myArgs.com && !myArgs.tabList" :is="'sd_page_'+myArgs.com"  :router="getRouter()" :post="getPost()" />
	
	
	<sdIframe v-if="myArgs.iframeSrc && !myArgs.com && !myArgs.tabList" :iframeSrc="myArgs.iframeSrc" :router="getRouter()" :post="getPost()" @onLookForAll="$emit('onLookForAll',$event)" />
	
	
</div>
`;


export default{
	template : template, 
	props:{
		myArgs:{//三种：router com
			default:()=>{
				return {};
			}
		},
		midyid:{
			default:'-1',
		}
	},
	data(){
		return {
		}
	},
	created(){
		//alert(JSON.stringify(this.myArgs));
	},
	methods:{
		getRouter(){
			var router = this.myArgs.router;
			return router;
		},
		getPost(){
			var mypost = hlc.copy(this.myArgs.post||{});
			if(this.midyid!=''){
				mypost.unitId =this.midyid;
			}
			return mypost;
		},
	}
}
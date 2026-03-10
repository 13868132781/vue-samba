var template = `
<span :class="makeclass" :style="makestyle" @click="myclick"></span>
`;

export default{
	template : template,
	props:{
		type:{
			default:'',
		},
		size:{
			default:16
		},
		color:{
			default:'',
		},
		family:{//这个目前没用
			default:'dftIcon',
		}
	},
	computed:{
		makestyle(){
			return 'font-size:'+this.size+'px;color:'+this.color;
		},
		makeclass(){
			var family = 'dftIcon';
			var prex = 'dftIcon-';
			var subx = this.type;			
			if(subx.indexOf('@')!=-1){
				var subxs = subx.split('@');
				family = subxs[0];
				prex = subxs[0];
				subx = subxs[1];
			}
			return family+" "+prex+subx;
		}
	},
	methods:{
		myclick(){
			this.$emit('sdClick');
		}
		
	}
}
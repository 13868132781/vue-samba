var template = `
<div style="display:inline-block;position:relative ">
	<sdButtony :value="value" :icon="icon" :doing="doing" :isOption="isOption" @sdClick="doClick" />
	
	<div v-if="isoptionOpen" style="position:absolute;left:0px;top:30px; background-color:#fff; box-shadow:0 0 2px 1px #aaa; min-width:100%;">
		<div v-for="item,index in options" style="padding:5px; word-break:keep-all; white-space:nowrap;cursor:pointer;"
		@mouseenter="optionmouseenter(index)" 
		@mouseleave="optionmouseleave(index)"
		:style="'background-color:'+(isoptionHover==index?'#eee':'#fff')"
		 @click="itemClick(item,index)"
		>
			<sdIcon :type="item.icon||'shougongqianshou'" size="13" style="margin-right:3px"/>
			<span>{{item.name}}</span>
		</div>
	</div>
	
</div>
`;


import sdButtony from "./sdButtony.js"

export default{
	template : template,
	components:{sdButtony},
	props:{
		showType:{
			default:'',
		},
		value:{
			default:'',
		},
		icon:{
			default:'',
		},
		isList:{
			default:false,
		},
		doing:{
			default:0,//0初始 1加载中 2成功 3失败
		},
		options:{
			default:()=>{
				return [];
			},
		}
	},
	data(){
		return {
			
			isoptionOpen:false,
			isoptionHover:-1,
		}
	},
	computed:{
		isOption(){
			if(this.options && this.options.length>0){
				return true;
			}
			return false;
		}
	},
	methods:{
		doClick(){
			if(this.options && this.options.length>0){
				this.doOptionOpen();
				return;
			}
			this.$emit('sdClick');
		},
		doOptionOpen(){
			if(this.isoptionOpen){
				this.doOptionClose();
				return;
			}
			this.isoptionOpen = true;
			setTimeout(()=>{//延迟一下，避免点出列表时触发
				document.addEventListener('click',this.doOptionClose);
			},100);
		},
		doOptionClose(){
			document.removeEventListener('click',this.doOptionClose);
			this.isoptionOpen = false;
			this.isoptionHover = -1;
		},
		optionmouseenter(index){
			this.isoptionHover=index;
		},
		optionmouseleave(index){
			this.isoptionHover=-1;
		},
		
		
		
		itemClick(item,index){
			this.$emit('sdClick',index);
		},
	}
}
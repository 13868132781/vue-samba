var template = `
<div 
	style="
		display:inline-block; 
		padding:4px;
		padding-left:6px; 
		padding-right:6px;
		outline:1px solid #aaa; 
		border-radius:3px;
		cursor:pointer;
		position:relative" 
	:style="'background-color:'+bgcolor"
	@click="myclick"  
	@mouseenter="mouseenter" 
	@mouseleave="mouseleave">
	
	<div style="display:flex;align-items:center">
		<sdIcon v-if="myShowType=='all'||myShowType=='icon'" :type="icon" size="13" style="margin-right:4px" />
		<div style="word-break:keep-all; white-space:nowrap;">{{value}}</div>
		<sdIcon v-if="isOption" type="chevron-right-double-copy" size="13" style="margin-left:4px"  />
	</div>
	
	<div v-if="myDong" 
		style="
			position:absolute; left:0;right:0;top:0;bottom:0;
			border-radius:3px;
			padding:8px;
			background-color:#eee;
			text-align:center;
			display:flex; justify-content:center ;align-items:center;" 
			:style="'background-color:'+inBgColor" 
			>
		<div v-if="myDong>1">
			{{inText}}
		</div>
			
		<div v-if="myDong==1" style="width:100%;overflow:hidden">
			<img style="width:100px;" src="/static/image/loading.gif" />
		</div>
	</div>
	
	
	
</div>
`;

export default{
	template : template,
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
		isOption:{
			default:false,
		}
	},
	watch:{
		doing:{
			handler:function(n,o){
				this.myDong= n;
			},
			immediate:true,
		}
	},
	data(){
		return {
			myDong:0,
			
			bgcolor:"#eee",
			
		}
	},
	computed:{
		inBgColor(){
			var color=[
				"#eee",
				"#00C957",
				"#FF8000",
			]
			return color[this.myDong-1];
		},
		inText(){
			var texts=[
				'','成功','失败'
			];
			return texts[this.myDong-1];
		},
		myShowType(){
			var st='all';
			if(this.showType){
				st = this.showType;
			}
			return st;
		}
		
	},
	methods:{
		myclick(){
			if(this.myDong==1){//还在加载
				return;
			}
			this.myDong=0;
			
			this.bgcolor="#bbb";
			setTimeout(()=>{
				this.bgcolor="#eee";
			},100);
			
			
			this.$emit('sdClick');
			
		},
		mouseenter(){
			this.bgcolor="#ddd";
		},
		mouseleave(){
			this.bgcolor="#eee";
		},
		
		
	}
}
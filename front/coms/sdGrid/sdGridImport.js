var template = `
<div style="display:inline-block;">
	<sdButton :value="name" :icon="(info.icon||'daoru')" @sdClick="doClick" />

</div>
`;

export default{
	template : template,
	
	props:{
		router:{
			default:''
		},
		post:{
			default:()=>{return {};}
		},
		gridData:{
			default:()=>{
				return [];
			}
		},
		fenyeInfo:{
			default:()=>{
				return [];
			}
		}
	},
	data(){
		return {
			name:'批量导入',
			info: {},
		}
	},
	created(){
		this.info = {
			router : this.router,
			post : hlc.copy(this.post)
			
		};
		
	},
	methods:{
		doClick(){
			var name = this.name;
			
			hlc.popup.open({
				com:'sdImport',
				name:name,
				width:'90%',
				height:'90%',
				args:this.info,
			});
		}
	}
	
}
var template = `
<div style="display:inline-block;">
	<sdButton value="导出" icon="daochu" :options="options" @sdClick="doClick" />
</div>`;

export default{
	template : template,
	props:{
		router:{
			default:''
		},
		post:{
			default:()=>{return {};}
		},
		gridSet:{
			default:()=>{
				return {};
			}
		},
		gridData:{
			default:()=>{
				return [];
			}
		},
		fenyeInfo:{
			default:()=>{
				return {};
			}
		}
	},	
	data(){
		return {
			options:[
				{'id':'all','name':'全部'},
				{'id':'dang','name':'当前页'},
			]
		}
	},
	methods:{
		doClick(index){
			var post = hlc.copy(this.post);
			post.btnOption = this.options[index].id;
			if(this.gridSet.fenyeEnable){
				post.fenye= this.fenyeInfo;
			}
			hlc.ajax({
				router: this.router+"@export",
				post: post,
				ok:(res)=>{
					if(res.code==0){
						//alert(res.data);
						window.location.href=hlc.entry+'?router='+this.router+"@exportDownload&filename="+res.data;
					}
				}
			});
		}
	}
}
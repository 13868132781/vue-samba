/*
table是以表格形式展示单元格数据，没有grid的复杂功能
data=[
	[1,2,3],
	["a","b","c"],
	{"A":"one","B":"two","c":"three"}, //键值对时，键会丢弃，只显示值
]
*/
var template = `
<div style="display:inline-block;position:relative">
<table style="background-color: transparent;">
	<tr v-for="line in row[jsCtrl.col]" style="background-color: transparent;">
		<td v-for="cellv,cellk in line"  style="background-color: transparent; padding:5px" ><div v-html="getHtml(cellv)"></div>{{getText(cellv)}}</td>
	</tr>
</table>
</div>
`;

export default{
	template : template,
	props:{
		row:{
			default:()=>{
				return {};
			}
		},
		jsCtrl:{
			default:()=>{
				return {};
			}
		}
		
	},
	
	data(){
		return {
			theme: hlc.config.theme, 
		}
	},
	computed:{
		
	},
	methods:{
		getHtml(val){
			if(val && typeof(val)=='object'){
				if(val.type=='html'){
					return val.value;
				}
			}
			return '';
		},
		getText(val){
			if(val && typeof(val)=='object'){
				if(val.type=='text'){
					return val.value;
				}
			}else{
				return val;
			}
			return '';
		}
		
	}
	
	
}
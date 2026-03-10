var template = `
<div style="">
	<div style="font-size:16px;padding:10px">{{info.title}}</div>
	<table style="width:100%">
		<tr v-for="trd,tri in info.list" style="">
			<td v-for="tdd,tdi in info.head" :align="tdd.align" style="padding:10px; padding-top:10px; padding-bottom:10px; border-top:1px solid #eee">
				{{trd[tdi]}}
			</td>
		</tr>
	</table>
</div>
`;

export default{
	template : template,
	props:{
		info:{
			title:'',
			list:''
		}
	}
}
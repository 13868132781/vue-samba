var template = `
<div style="padding:10px;background-color:#fff;">
	<div style="box-shadow:0 0 2px 1px #eee" >
		<table class="sdGridTable" cellspacing="0" cellpadding="7">
			<tr style="background-color:#f0f0f0">
				<td style="width:30px"></td>
				<td v-for="hed,index in headers">
					{{hed}}
				</td>
				<td style="width:30px"></td>
			</tr>
			<tr v-for="trv,tri in tdata">
				<td style="width:30px">{{tri+1}}</td>
				<td v-for="hed,index in headers">
					<input v-model="trv[index]" style="padding:3px;outline:none;width:100%"/>
				</td>
				<td style="width:30px" align="center">
					<sdIcon type="bohui" @click="doClose(tri)" />
				</td>
			</tr>
		</table>
	</div>
	<div >
		<div style="float:right">
		<button @click="doClick" style="margin:10px;">添加行</button>
		</div>
	</div>
</div>
`;


export default{
	template : template, 
	props:{
		args:{
			default:()=>{return {};},
		}
	},
	created(){
		
	},
	data(){
		return {
			headers : this.args.headers,
			tdata: JSON.parse(this.args.value||'[]')
		}
	},
	methods:{
		doClick(){
			this.tdata.push([]);
		},
		doClose(tri){
			this.tdata.splice(tri,1);
		},
		ok(f){
			var newdata=[];
			this.tdata.map((da)=>{
				if(da && da.map){
					var daa=[];
					da.map((doo)=>{
						if(doo.trim()){
							daa.push(doo.trim());
						}
					});
					if(daa.length==this.headers.length){
						newdata.push(daa);
					}
				}
			});
			if(newdata.length==0){
				newdata='';
			}else{
				newdata = JSON.stringify(newdata);
			}
			if(f){
				f(newdata);
			}
		}
	}
}
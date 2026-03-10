var template = `
<div style="padding:10px;background-color:#fff;">
	<sdMidy ref="myMidy" :router="args.router" :dftid="args.value" :dbkey="args.key"  @midclick="midclick" />
</div>
`;


export default{
	template : template, 
	props:{
		args:{
			default:()=>{return {};},
		}
	},
	data(){
		return {
			midyid:null
		}
	},
	created(){
	},
	methods:{
		midclick(id){
			this.midyid = id.id;
		},
		ok(f){
			var depth=100;
			var word = '';
			var gridData = this.$refs.myMidy.gridData;
			for(var i = gridData.length-1;i>=0;i--){
				var row = gridData[i];
				if(row.id==this.midyid){
					depth = row._tree_.depth;
					word = row.name;
				}
				if(depth==0){
					break;
				}
				if(row._tree_.depth+1==depth){
					word = row.name+'→'+word;
					depth--;
				}
			}
			//alert(this.midyid);
			if(f){
				f({id:this.midyid,name:word});
			}
		}
	}
}
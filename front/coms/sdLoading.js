var template = `
<div >
	<div v-if="show" style="position:fixed; left:0; right:0; top:0; bottom:0; background-color: rgba(0,0,0,0.01); display:flex; justify-content:center; align-items:center;z-index:100000">
		<div style="background-color:#fff;padding:20px;font-size:16px;color:#888;border-radius:5px;box-shadow:0 0 3px 2px #ccc">
			<div style="display:inline-block; " class="sdRotation" >
			<sdIcon type="loading" size="40" color="#666"  />
		</div>
		</div>
	</div>

</div>
`;

export default{
	template : template,
	data(){
		return {
			show:0,
		}
	},
	created(){
		hlc.$on('sdloadingwait',(b)=>{
			//alert(b.detail);
			if(b.detail){
				this.show ++;
			}else{
				this.show --;
				if(this.show<0){
					this.show=0;
				}
			}
			
		});
		
	}
	
	
}

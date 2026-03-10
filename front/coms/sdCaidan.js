/*
右键显示处理的选项
*/
var template = `
<div>
	<div v-if="show" style="position:fixed; left:0; right:0; top:0; bottom:0; background-color: rgba(0,0,0,0.01)" @click="close()">
		<div ref="showbox" style="position:absolute; background-color:#fff; padding:3px;box-shadow:0 0 2px 1px #ccc" :style="mypos">
			<div v-for="item,index in mylist">
				<div @click="ok(item.id)" style=";padding:10px;border-bottom:1px solid #eee;cursor:pointer">
					<sdIcon :type="item.icon" size="12" />
					{{item.name}}
				</div>
			</div>
		</div>
	</div>
</div>`;

export default{
	template : template,
	data(){
		return {
			mylist:[],
			show:false,
			func :null,
			mye : {},
			width:0,
			height:0,
		}
	},
	computed:{
		mypos(){
			var left= this.mye.clientX;
			var top = this.mye.clientY;
			var dh=document.body.clientHeight;
			if(top+this.height > dh){
				top -= this.height;
			}
			return 'left:'+(left-this.width)+'px;top:'+top+'px';
		}
	},
	updated(){
		if(this.$refs.showbox){
			//console.log("updated");
			this.width = this.$refs.showbox.offsetWidth;
			this.height = this.$refs.showbox.offsetHeight;
		}
	},
	methods:{
		open(e,list,f){
			this.mye = e;
			this.mylist = list;
			this.show=true;
			this.func = f;
			setTimeout(()=>{
				
			},100);
		},
		close(){
			this.show=false;
		},
		ok(id){
			if(this.func){
				this.func(id);
			}
		}
		
	}
		
}
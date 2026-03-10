var template = `
<div style="position:absolute; left:10px; right:10px; top:10px; bottom:10px; display:flex;flex-direction:column ;">
	<div style="flex:1">
		<textarea style="width:100%;height:100%;outline:none" @keydown="inputTab" v-model="myData"></textarea>
	</div>
	<div style="height:40px;padding:10px;text-align:center">
		<button @click="myClick">保存</button>
	</div>
</div>
`;
export default{
	template : template,
	props:{
		args:{
			default:{},
		}
	},
	data(){
		return {
			myData:null,
		}
	},
	created(){
		this.fetchData();
	},
	methods:{
		fetchData(){
			this.$emit('onDoing','1');
			var post = JSON.parse(JSON.stringify(this.args.post));
			post.goto = post.goto+'Get';
			hlc.ajax({
				router: this.args.router+"@fetch",
				post: post,
				silent:true,
				ok:(res)=>{
					//alert(JSON.stringify(res));
					this.$emit('onDoing','0');
					this.myData = res.data;
				}
			});	
			
		},
		
		myClick(){
			//alert(this.myData);
			this.$emit('onDoing','1');
			var post = JSON.parse(JSON.stringify(this.args.post));
			post.goto = post.goto+'Save';
			post.data = this.myData;
			hlc.ajax({
				router: this.args.router+"@fetch",
				post: post,
				silent:true,
				ok:(res)=>{
					//alert(JSON.stringify(res));
					this.$emit('onDoing','0');
				}
			});	
			
		},
		
		
		
		
		
		
		
		
		
		inputTab(event) {
			if (event.keyCode == 9) {
				if(event.preventDefault){
					event.preventDefault();
				}else{
					event.returnValue = false;
				}
				var obj = event.target;
				this.insertText(obj,'	');
			}
		},
		insertText(obj,str) {
			if (document.selection) {
				obj.focus();
				var sel = document.selection.createRange();
				sel.text = str;
			} else if (typeof obj.selectionStart === 'number' && typeof obj.selectionEnd === 'number') {
				var startPos = obj.selectionStart;
				var endPos = obj.selectionEnd;
				var tmpStr = obj.value;
				obj.value = tmpStr.substring(0, startPos) + str + tmpStr.substring(endPos, tmpStr.length);
				this.setCursorPosition(obj,startPos+1);
			} else {
				obj.value += str;
			}
		},
		
		setCursorPosition(elem, index) {
			var val = elem.value
			var len = val.length
		 
			// 超过文本长度直接返回
			if (len < index) return
			setTimeout(function() {
				//elem.focus()
				if (elem.setSelectionRange) { // 标准浏览器
					elem.setSelectionRange(index, index)   
				} else { // IE9-
					var range = elem.createTextRange()
					range.moveStart("character", -len)
					range.moveEnd("character", -len)
					range.moveStart("character", index)
					range.moveEnd("character", 0)
					range.select()
				}
			}, 10)
		}
		
		
	}
}
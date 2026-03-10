var template = `
<div  style="">
	<div style="display:flex;border:1px solid #ccc; border-radius:5px; overflow:hidden; align-items:center;justify-content:center; cursor: pointer;padding:10px" :style="'background-color:'+theme" @click="$emit('click')">
		<div v-if="!islogin && icon" style="margin-right:10px">
			<sdIcon :type="icon" size="18px"  color="#fff"/>
		</div>
		<div v-if="islogin" style="margin-right:10px">
			<div style="display:inline-block; " class="sdRotation" >
			<sdIcon type="loading" size="18px"  color="#fff"/>
			</div>
		</div>
		<div style="font-size:18px;color:#fff;letter-spacing:5px">
			{{islogin?'正在':''}}{{value}}{{islogin?'...':''}}
		</div>
	</div>
</div>
`;

export default{
	template : template,
	props:{
		icon:{
			default:''
		},
		jujiao:{
			default:false,
		},
		value:{
			default:"登录",
		},
		islogin:{
			default:false,
		}
	},
	data(){
		return {
			theme: hlc.config.theme,
		}
	},
	computed:{
		
	}
}
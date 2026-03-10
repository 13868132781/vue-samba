var template = `
<div >
	<div ref="xtermDlg" style="position:absolute; left:10px; right:10px; top:10px; bottom:10px; color:#fff; border:7px solid #888;border-top:0px; border-radius:5px; display:flex; flex-direction:column" :style="'bottom:'+realBottom+';border-color:'+theme">
		<div style="background-color:#888;height:30px; border-bottom:0px solid #888; display:flex; align-items:center;padding-left:5px;padding-right:5px" :style="'background-color:'+theme">
			
			<div style="margin-right:5px">
				<sdIcon type="xitongguanli1" size="20" color="#fff" /> 
			</div>
			
			<div style="color:#fff;margin-right:10px">终端（选中即复制，右键可粘贴）</div>
			
			<div style="color:#fff;margin-right:10px">连接到: {{ljnas||'本机'}}</div>
			
			<div style="color:#fff;margin-right:10px">状态: {{getStatus()}}</div>
			
			<div style="flex:1;"></div>
			
			<div @click="doRestart()" style="cursor:pointer;margin-left:10px" title="重连">
				<sdIcon type="xuanzhuan" size="20" color="#fff" /> 
			</div>
			
			<div @click="doClick()" style="cursor:pointer;margin-left:10px" title="连接其他设备">
				<sdIcon type="fujian" size="20" color="#fff" /> 
			</div>
			
			<div @click="doClick()" v-if="1==2" style="padding-left:3px; padding-right:3px; background-color:#0f0; cursor:pointer;">连接其他设备</div>
			
		</div>
		<div style="flex:1;display:flex;padding:2px;padding-right:0px;background-color:#000">
			<div id="xtermWrap" style="flex:1" class="myscroll" @contextmenu.prevent="xtermRightClick"></div>
		</div>
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
			serverPort : "43210",
			termObj : null,
			wsStatus : 1,
			statusStr:{
				0:'正在关闭旧连接',
				1:'未连接',
				2:'正在初始化界面',
				3:'正在连接服务器',
				4:'已经连接服务器',
				5:'已经连接到终端',
			},
			wsObj : null,
			dftcols : 50,
			dftrows : 20,
			
			ljnas: '',
			
			theme: hlc.config.theme,
			realBottom:'10px',
		}
	},
	created(){
		//this.fetchData(false);
		
	},
	mounted(){
		this.startTerm();
		setTimeout(()=>{//需要term创建好，才能获得宽度
			var screens = document.getElementsByClassName('xterm-screen');
			var screenwidth = screens[0].offsetWidth;
			var screenheight = screens[0].offsetHeight;
			var divwidth = document.getElementById('xtermWrap').offsetWidth;
			var divheight = document.getElementById('xtermWrap').offsetHeight;
			var newcols = Math.floor((divwidth-14)*this.dftcols/screenwidth);
			var newrows = Math.floor((divheight-0)*this.dftrows/screenheight);
			this.realBottom='auto';//必须为auto，为空无效
			this.termObj.resize(newcols,newrows);
			this.dftcols = newcols;
			this.dftrows = newrows;
			this.startWS();
		},1);
	},
	beforeUnmount(){
		//console.log('xtem destroy');
		if(this.wsObj){
			this.wsObj.close();
		}
		if(this.termObj){
			this.termObj.dispose();
		}
	},
	methods:{
		getStatus(){
			return this.statusStr[this.wsStatus];
		},
		doRestart(){
			if(this.wsObj){
				this.wsStatus = 0;//关闭旧连接
				if(this.termObj){
					this.termObj.reset();
					this.termObj.write("disConnect old server ...\r\n");
				}
				this.wsObj.close();
			}
			var mySI =setInterval(()=>{
				if(!this.wsObj){
					this.wsStatus=2;//初始化xterm
					this.termObj.reset();
					this.termObj.focus();
					this.startWS();
					clearInterval(mySI);
				}
			},100);
		},
		
		doClick(){	
			var ljnas = prompt("SSH连接NAS，如: root@192.168.0.1","");
			if(!ljnas){
				return;
			}
			this.ljnas = ljnas;
			this.doRestart();
		},
		
		startTerm(){
			this.wsStatus=2;
			this.termObj = new Terminal({
				cols: this.dftcols,
				rows: this.dftrows,
				screenKeys: true,
				useStyle: true,
				cursorBlink: true,
				encoding: 'utf8',
				//fontFamily: 'Lucida Console',
				//fontSize: 16,
				//由于index.html里的style影响，在这里设置字体颜色大小无效，得到xterm.css里设置
			});
			this.termObj.open(document.getElementById('xtermWrap'));　　
			
			this.termObj.onData((data)=>{
				if(this.wsStatus==1){//处于未连接状态
					this.doRestart();
					return;
				}
				if(this.wsStatus<5){
					return;
				}
				this.wsObj.send(data);
			});
			
			//选中复制
			this.termObj.onSelectionChange((evt) => { //evt无效
				var str = this.termObj.getSelection();
				if(str){//只有在有选中文本时，才复制到剪切板
					navigator.clipboard.writeText(str);
				}
			});
			
			this.termObj.onKey((evt) => {//evt无效，且只能监听键盘，鼠标监听不了
				//console.log("onkey： "+event.which+" "+event.keyCode);//
			});			
			
			this.termObj.focus();
				
		},
		xtermRightClick(){//右键粘贴
			(async ()=>{//await必须放在一个async执行得函数里
				var str= await navigator.clipboard.readText();
				this.termObj.paste(str);
			})();
		},
		
		startWS(){
			this.wsStatus=3;
			this.termObj.write("connect to server ...\r\n");
			
			const parsedURL = new URL(window.location.href);
			var serverIp = parsedURL.hostname;
			var reqArgs = "?cols="+this.dftcols+"&rows="+this.dftrows;
			if(this.ljnas){
				reqArgs+="&nas="+this.ljnas;
			}
			var wsUrl = "wss://"+serverIp+":"+this.serverPort+reqArgs;
			this.wsObj = new WebSocket(wsUrl);
			this.wsObj.binaryType = "arraybuffer";
			this.wsObj.onopen = (evt)=>{
				this.wsStatus=4;
				//this.wsObj.send("stty cols "+this.dftcols+"\n");
				//this.wsObj.send("stty rows "+this.dftrows+"\n");
				//this.wsObj.send("PS1='[\\u@\\h \\W]\\$ '\n");
				//this.wsObj.send("clear\n");
			}

			this.wsObj.onmessage = (evt)=>{//接受到数据　
				this.wsStatus=5;
				
				var data = evt.data;
				
				//binaryType=arraybuffer,且php发送的是二进制数据
				//此时接收的是arraybuffer类型的data
				
				//把arraybuffer转为string
				//var datastr = new TextDecoder('utf-8').decode(data);
				//console.log("++"+datastr+"===");
				
				//把arraybuffer转为Uint8Array
				data = new Uint8Array(data);
				//data = data.slice(0,-3);
				
				//this.termObj.write 方法默认接受string或 Uint8Array 类型的数据
				this.termObj.write(data);
			}

			this.wsObj.onclose = (evt)=>{
				//console.log(evt);
				this.termObj.write("\r\nxterm terminated\r\n");
				this.wsObj=null;
				this.wsStatus=1;
				
			}

			this.wsObj.onerror = (evt)=>{
				//console.log(evt);
				this.termObj.write("\r\nxterm error\r\n");
				
			}

		}

	}
}
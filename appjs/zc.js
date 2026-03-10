//注册名必须按照如下格式：sd_page_xxx
//在菜单里的com值，只需填写 xxx
var comList = {};

import sd_page_main from "./main/main.js";
addCom("sd_page_main",sd_page_main);


import sd_page_iconShow from "./iconShow.js";
addCom("sd_page_iconShow",sd_page_iconShow);


import sd_page_failover from "./failover.js"; 
addCom("sd_page_failover",sd_page_failover);



import sd_page_adGrab from "./adGrab.js";
addCom("sd_page_adGrab",sd_page_adGrab);


import sd_page_srvDebug from "./srvDebug.js";
addCom("sd_page_srvDebug",sd_page_srvDebug);

import sd_page_qrcodeShow from "./qrcodeShow.js";
addCom("sd_page_qrcodeShow",sd_page_qrcodeShow);

import sd_page_scriptCode from "./scriptCode.js";
addCom("sd_page_scriptCode",sd_page_scriptCode);

import sd_page_cfgFileLook from "./cfgFileLook.js";
addCom("sd_page_cfgFileLook",sd_page_cfgFileLook);



import sd_page_loginTest from "./loginTest.js";
addCom("sd_page_loginTest",sd_page_loginTest);

import sd_page_adInit from "./adInit.js";
addCom("sd_page_adInit",sd_page_adInit);

import sd_page_sdxterm from "./sdxterm.js";
addCom("sd_page_sdxterm",sd_page_sdxterm);


function addCom(name,com){
	comList[name]=com;
}


export default{
	regist(app){
		for(var n in comList){
			app.component(n,comList[n]);
		}
	}
}


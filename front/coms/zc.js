var comList = {};

import sdGrid from "./sdGrid/sdGrid.js";
addCom("sdGrid",sdGrid);

import sdMidy from "./sdMidy.js";
addCom("sdMidy",sdMidy);

import sdButton from "./sdButton.js";
addCom("sdButton",sdButton);

import sdIcon from "./sdIcon/sdIcon.js";
addCom("sdIcon",sdIcon);

import sdPopup from "./sdPopup.js";
addCom("sdPopup",sdPopup);

import sdForm from "./sdForm.js";
addCom("sdForm",sdForm);

import sdFormEasy from "./sdFormEasy.js";
addCom("sdFormEasy",sdFormEasy);

import sdSearch from "./sdSearch.js";
addCom("sdSearch",sdSearch);

import sdFilter from "./sdFilter.js";
addCom("sdFilter",sdFilter);


import sdCaidan from "./sdCaidan.js";
addCom("sdCaidan",sdCaidan);


import sdLoading from "./sdLoading.js";
addCom("sdLoading",sdLoading);


import sdFormField from "./sdFormField/sdFormField.js";
addCom("sdFormField",sdFormField);


import sdFormSubmit from "./sdFormField/sdFormSubmit.js";
addCom("sdFormSubmit",sdFormSubmit);

import sdPage from "./sdPage.js";
addCom("sdPage",sdPage);

import sdDialog from "./sdDialog.js";
addCom("sdDialog",sdDialog);


import sdNetError from "./sdNetError.js";
addCom("sdNetError",sdNetError);

import sdReqMsg from "./sdReqMsg.js";
addCom("sdReqMsg",sdReqMsg);

import sdUpload from "./sdUpload.js";
addCom("sdUpload",sdUpload);

import sdImport from "./sdImport.js";
addCom("sdImport",sdImport);


import sdFetch from "./sdFetch.js";
addCom("sdFetch",sdFetch);

import sdFetchDefault from "./sdFetchDefault.js";
addCom("sd_page_fetchDefault",sdFetchDefault);


import sdIframe from "./sdIframe.js";
addCom("sdIframe",sdIframe);



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

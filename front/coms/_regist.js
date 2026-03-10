import sdGrid from "./sdGrid/sdGrid.js";
Vue.component("sdGrid",sdGrid);

import sdMidy from "./sdMidy.js";
Vue.component("sdMidy",sdMidy);

import sdButton from "./sdButton.js";
Vue.component("sdButton",sdButton);

import sdIcon from "./sdIcon/sdIcon.js";
Vue.component("sdIcon",sdIcon);

import sdPopup from "./sdPopup.js";
Vue.component("sdPopup",sdPopup);

import sdForm from "./sdForm.js";
Vue.component("sdForm",sdForm);

import sdSearch from "./sdSearch.js";
Vue.component("sdSearch",sdSearch);

import sdFilter from "./sdFilter.js";
Vue.component("sdFilter",sdFilter);


import sdCaidan from "./sdCaidan.js";
Vue.component("sdCaidan",sdCaidan);


import sdLoading from "./sdLoading.js";
Vue.component("sdLoading",sdLoading);


import sdFormField from "./sdFormField/sdFormField.js";
Vue.component("sdFormField",sdFormField);


import sdFormSubmit from "./sdFormField/sdFormSubmit.js";
Vue.component("sdFormSubmit",sdFormSubmit);

import sdPage from "./sdPage.js";
Vue.component("sdPage",sdPage);

import sdDialog from "./sdDialog.js";
Vue.component("sdDialog",sdDialog);


import sdNetError from "./sdNetError.js";
Vue.component("sdNetError",sdNetError);

import sdReqMsg from "./sdReqMsg.js";
Vue.component("sdReqMsg",sdReqMsg);

import sdUpload from "./sdUpload.js";
Vue.component("sdUpload",sdUpload);

import sdImport from "./sdImport.js";
Vue.component("sdImport",sdImport);


import sdFetch from "./sdFetch.js";
Vue.component("sdFetch",sdFetch);

import sdFetchDefault from "./sdFetchDefault.js";
Vue.component("sd_page_fetchDefault",sdFetchDefault);


import sdIframe from "./sdIframe.js";
Vue.component("sdIframe",sdIframe);


//组件传参和默认参数对比
//虽然可以用先merge把传参和默认值合并
Vue.prototype.$HlcCheck = function(val1,val2){
	if(val1===null){
		return val2;
	}else{
		return val1;
	}
}

//这样写，依旧是全局的，scopped没起作用
Vue.prototype.$mountStyle=function(style){
	var css_text = style;
    var css = document.createElement('style');
    css.type='text/css';
    css.setAttributeNode( document.createAttribute('scopped') );
    css.appendChild(document.createTextNode(css_text));
    this.$el.appendChild(css);
}


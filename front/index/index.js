
const { createApp } = Vue;

import root from "./root.js"
import myCom from '../coms/zc.js';
import myPage from '../../appjs/zc.js';

//app是应用实例，root是根组件实例
var app = createApp(root);
myCom.regist(app);
myPage.regist(app);

app.mount('#app');


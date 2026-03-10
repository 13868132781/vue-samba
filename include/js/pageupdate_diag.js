function pageupdate(){
	if (!topWin||(topWin&&topWin.Dialog._Array.length>0)) {
		return; 
	}
	var msg = arguments[0] ? arguments[0] : "数据正在加载中<br>请耐心等候一下<br>";
	var title = arguments[1] ? arguments[1] : "数据加载提示框";
	var diag = new Dialog();
	diag.ID=16523;
 	diag.Width = 350;
 	diag.Height = 100;
 	diag.Title = title;
    diag.InnerHtml="<div style='text-align:center;font-size:14px;fontWeight:nomal;margin-top:20px'>"+msg+"</div>";
	diag.show();

}
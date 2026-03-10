hlctime_path=document.scripts[document.scripts.length-1].src.substring(0,document.scripts[document.scripts.length-1].src.lastIndexOf("/")+1);
document.write("<script language='javascript' type='text/javascript' src='"+hlctime_path+"WdatePicker.js'></script>");

/**
* 获取本周、本季度、本月、上月的开端日期、停止日期
*/
var now = new Date(); //当前日期
var nowDayOfWeek = now.getDay(); //今天本周的第几天
var nowDay = now.getDate(); //当前日
var nowMonth = now.getMonth(); //当前月
var nowYear = now.getYear(); //当前年
nowYear += (nowYear < 2000) ? 1900 : 0; //
var lastMonthDate = new Date(); //上月日期
lastMonthDate.setDate(1);
lastMonthDate.setMonth(lastMonthDate.getMonth()-1);
var lastYear = lastMonthDate.getYear();
var lastMonth = lastMonthDate.getMonth();

//格局化日期：yyyy-MM-dd
function formatDate(date) {
	var myyear = date.getFullYear();
	var mymonth = date.getMonth()+1;
	var myweekday = date.getDate();
	if(mymonth < 10){
		mymonth = "0" + mymonth;
	}
	if(myweekday < 10){
		myweekday = "0" + myweekday;
	}
	return (myyear+"-"+mymonth + "-" + myweekday);
}
//获得某月的天数
function getMonthDays(myMonth){
	var monthStartDate = new Date(nowYear, myMonth, 1);
	var monthEndDate = new Date(nowYear, myMonth + 1, 1);
	var days = (monthEndDate - monthStartDate)/(1000 * 60 * 60 * 24);
	return days;
}
//获得本季度的开端月份
function getQuarterStartMonth(){
	var quarterStartMonth = 0;
	if(nowMonth<3){
		quarterStartMonth = 0;
	}
	if(2<nowMonth && nowMonth<6){
		quarterStartMonth = 3;
	}
	if(5<nowMonth && nowMonth<9){
		quarterStartMonth = 6;
	}
	if(nowMonth>8){
		quarterStartMonth = 9;
	}
	return quarterStartMonth;
}

//获得本日的开端日期
function getdayStartDate() {
	var dayStartDate = new Date(nowYear, nowMonth, nowDay );
	return formatDate(dayStartDate);
}
//获得本周的开端日期
function getWeekStartDate() {
	var weekStartDate = new Date(nowYear, nowMonth, nowDay - nowDayOfWeek);
	return formatDate(weekStartDate);
}
//获得本周的停止日期
function getWeekEndDate() {
	var weekEndDate = new Date(nowYear, nowMonth, nowDay + (6 - nowDayOfWeek));
	return formatDate(weekEndDate);
}
//获得本月的开端日期
function getMonthStartDate(){
	var monthStartDate = new Date(nowYear, nowMonth, 1);
	return formatDate(monthStartDate);
}
//获得本月的停止日期
function getMonthEndDate(){
	var monthEndDate = new Date(nowYear, nowMonth, getMonthDays(nowMonth));
	return formatDate(monthEndDate);
}
//获得上月开端时候
function getLastMonthStartDate(){
	var lastMonthStartDate = new Date(nowYear, lastMonth, 1);
	return formatDate(lastMonthStartDate);
}
//获得上月停止时候
function getLastMonthEndDate(){
	var lastMonthEndDate = new Date(nowYear, lastMonth, getMonthDays(lastMonth));
	return formatDate(lastMonthEndDate);
}
//获得本季度的开端日期
function getQuarterStartDate(){
	var quarterStartDate = new Date(nowYear, getQuarterStartMonth(), 1);
	return formatDate(quarterStartDate);
}
//或的本季度的停止日期
function getQuarterEndDate(){
	var quarterEndMonth = getQuarterStartMonth() + 2;
	var quarterStartDate = new Date(nowYear, quarterEndMonth, getMonthDays(quarterEndMonth));
	return formatDate(quarterStartDate);
}












function getPosition(sender) {
    var e=sender,E=e;
    var x=e.offsetLeft;
    var y=e.offsetTop;
    while (e=e.offsetParent) {
        var P=e.parentNode;
        while (P!=(E=E.parentNode)) {
            x-=E.scrollLeft;
            y-=E.scrollTop;
        }
        x+=e.offsetLeft;
        y+=e.offsetTop;
        E=e;
    }
    return {"x":x,"y":y};
    //alert("top="+y+"\nleft="+x);
}


function hlctimesdel(t,id){ 
	if(t=='q'){
		ab='';
	}else if(t=='r'){
		ab=getdayStartDate()+"~";
	}else if(t=='z'){
		ab=getWeekStartDate()+"~";
	}else if(t=='y'){
		ab=getMonthStartDate()+"~";
	}else if(t=='d'){
		ab=document.getElementById('times_1').value;
	}else if(t=='o'){
		a=document.getElementById('times_1').value;
		b=document.getElementById('times_2').value;
		if(a==''&b=='')
			ab='';
		else
			ab=a+"~"+b;
	}
	document.body.removeChild(document.getElementById('popupAddr'));
	
	
	document.getElementById(id).value=ab;
}

function hlctimesadd(id,dateFmt){
	dateFmt=dateFmt||"";
	if(typeof(WdatePicker)=="undefined"){WdatePickerinit();}
	var obj=document.getElementById(id);
	var val=obj.value;
	var a='',b='';
	var vals=val.split('~');
	a=vals[0];
	b=vals[1]?vals[1]:'';

	
	var pos=getPosition(obj);
	
	if(document.getElementById('popupAddr'))
		document.body.removeChild(document.getElementById('popupAddr'));
		
	var popupDiv = document.createElement("div");
//给这个元素设置属性与样式
	popupDiv.setAttribute("id","popupAddr");
	popupDiv.style.position = "absolute";
	popupDiv.style.border = "1px solid #59c";
	popupDiv.style.background = "#eee";
	popupDiv.style.width = "240px";
	popupDiv.style.height = "60px";
	popupDiv.style.zIndex = 99;
	popupDiv.style.left =pos['x']+"px";
	popupDiv.style.top =(pos['y']+20)+"px";
	
	popupDiv.innerHTML="\
	<table>\
	  <tr>\
	    <td  style='width:40px'>开始:</td>\
	    <td  style='width:80px'><input id='times_1' name='times_1' value='"+a+"' style='width:80px' onClick=\"WdatePicker({el:'times_1'"+dateFmt+"})\"/></td>\
	    <td  style='width:40px'>结束:</td>\
	    <td  style='width:80px'><input id='times_2' name='times_2' value='"+b+"' style='width:80px' onClick=\"WdatePicker({el:'times_2'"+dateFmt+"})\"/></td>\
	  </tr>\
	  <tr>\
	    <tdcolspan='10'>\
		  <table style='width:100%'>\
		    <tr>\
			  <td><input type='button' value='清空' style='background-color:#ddd;border:solid 1px #999' onClick=\"hlctimesdel('q','"+id+"')\"></td>\
			  <td><input type='button' value='本日' style='background-color:#ddd;border:solid 1px #999' onClick=\"hlctimesdel('r','"+id+"')\"></td>\
			  <td><input type='button' value='本周' style='background-color:#ddd;border:solid 1px #999' onClick=\"hlctimesdel('z','"+id+"')\"></td>\
			  <td><input type='button' value='本月' style='background-color:#ddd;border:solid 1px #999' onClick=\"hlctimesdel('y','"+id+"')\"></td>\
			  <td><input type='button' value='单天' style='background-color:#ddd;border:solid 1px #999' onClick=\"hlctimesdel('d','"+id+"')\"></td>\
		      <td><input type='button' value='确定' style='background-color:#ddd;border:solid 1px #999' onClick=\"hlctimesdel('o','"+id+"')\"></td>\
			</tr>\
		  </table>\
	  </tr>\
	</table>";
	
	document.body.appendChild(popupDiv); 
}

function hlctimesclose(){
	//document.onclick=function(){		
		if(document.getElementById('popupAddr'))
			document.body.removeChild(document.getElementById('popupAddr'));
	//}
	
	
}
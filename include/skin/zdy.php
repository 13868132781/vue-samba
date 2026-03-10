<?php require_once(dirname(__FILE__).'/../fun/api.php'); ?>
<?php

$systemtype=$_HLCPHP["type"];
$systemskin=$_HLCPHP["skin"];
if($systemskin=='' or $systemskin=='default'){
	$systemskin=$systemtype;
}

if($_GET['opencss']==''){
	$_GET['opencss']='default.css';
}

if($_GET['delpic']!=''){
	exec("sudo rm ".dirname(__FILE__).'/'.$systemskin.'/image/'.$_GET['delpic']);
}

if($_GET['delsave']!=''){
	exec("sudo rm ".dirname(__FILE__).'/'.$systemskin.'/'.$_GET['delsave']);
}

if($_FILES["file"]!='' and $systemskin!=''){ 
	if ($_FILES["file"]["error"] > 0){
		echo "Error: " . $_FILES["file"]["error"] . "<br />";
		return;
	}
	if($_FILES["file"]["type"]=="image/x-icon"){
		echo "不支持ico格式";
		return;
	} 
	if(!stristr( $_FILES["file"]["type"],"image/") ){
		echo "不是图片类型";			
		return;
	}
	if($_POST['savepath']!=''){
		$myfilepath=dirname(__FILE__).'/'.$_POST['savepath'];
	}else{
		if(!is_readable(dirname(__FILE__).'/'.$systemskin)){
		mkdir(dirname(__FILE__).'/'.$systemskin,0777);
		}
		if(!is_readable(dirname(__FILE__).'/'.$systemskin.'/image')){
			mkdir(dirname(__FILE__).'/'.$systemskin.'/image',0777);
		}
		$myfilepath=dirname(__FILE__).'/'.$systemskin.'/image/';
	}
		
	$myfilename=$_FILES["file"]["name"];
	if($_POST['savename']!=''){
		$myfilename=$_POST['savename'];
	}
	
	exec("sudo rm ".$myfilepath.$myfilename);
	move_uploaded_file($_FILES["file"]["tmp_name"],'/tmp/'.$myfilename );
	exec("sudo mv /tmp/".$myfilename." ".$myfilepath.$myfilename);
}







$colortable['top_body']['name']='顶部页----背景图/色';
$colortable['top_body']['deft']='url(image/top_bg_1.jpg)';
$colortable['top_body']['css']='.top_body{background:<varstring>;}';


$colortable['top_color']['name']='顶部页----文字颜色';
$colortable['top_color']['deft']='#FFFFFF';
$colortable['top_color']['css']='.top_body{color:<varstring>;}';

$colortable['left_normal_td']['name']='左侧页----正常背景';
$colortable['left_normal_td']['deft']='#8A2BE2';
$colortable['left_normal_td']['css']='.left_far_tr_normal td{background:<varstring>;}';

$colortable['left_select_td']['name']='左侧页----选中背景';
$colortable['left_select_td']['deft']='#8A2BE2';
$colortable['left_select_td']['css']='.left_far_tr_select td{background:<varstring>;}';

$colortable['tabtr_ungettd']['name']='选项卡----背景';
$colortable['tabtr_ungettd']['deft']='';
$colortable['tabtr_ungettd']['css']='.main_tabtable_tabtr_ungettd{background:<varstring>;}';

$colortable['tabtr_ungettd_color']['name']='选项卡----文字颜色';
$colortable['tabtr_ungettd_color']['deft']='';
$colortable['tabtr_ungettd_color']['css']='.main_tabtable_tabtr_ungettd{color:<varstring>;}';

$colortable['table_title_bg']['name']='表格----标题背景';
$colortable['table_title_bg']['deft']='url(image/title.gif) repeat-x 0 0';
$colortable['table_title_bg']['css']='.main_listtable_titletr td{background:<varstring>;}.pop_table_titletd{background:<varstring>;}';


$colortable['table_title_color']['name']='表格----标题文字颜色';
$colortable['table_title_color']['deft']='url(image/title.gif) repeat-x 0 0';
$colortable['table_title_color']['css']='.main_listtable_titletr td{color:<varstring>;}';

$colortable['table_header_bg']['name']='表格----字段行背景';
$colortable['table_header_bg']['deft']='';
$colortable['table_header_bg']['css']='.main_listtable_headtr td{background:<varstring>;}';

$colortable['table_outer_border']['name']='表格----外部边框色';
$colortable['table_outer_border']['deft']='#888';
$colortable['table_outer_border']['css']='table,td,div{border:0px solid <varstring>;}';

$colortable['table_inner_border']['name']='表格----内部边框色';
$colortable['table_inner_border']['deft']='#bbb';
$colortable['table_inner_border']['css']='.main_listtable_celltr td{border-color:<varstring>;
}';

$colortable['btm_bg']['name']='底部页----背景';
$colortable['btm_bg']['deft']='#8A2BE2';
$colortable['btm_bg']['css']='.btm_body{background:<varstring>;}';

$colortable['btm_color']['name']='底部页----文字颜色';
$colortable['btm_color']['deft']='#000';
$colortable['btm_color']['css']='.btm_body{color:<varstring>;}';

$colortable['login_bg']['name']='登录页----框背景';
$colortable['login_bg']['deft']='#8A2BE2';
$colortable['login_bg']['css']='.login_bg{background:<varstring>;}';
$colortable['login_bg']['jsvar']="skin_theme_color";


$colortable['main_btn_text']['name']='按钮----背景';
$colortable['main_btn_text']['deft']='#8A2BE2';
$colortable['main_btn_text']['css']='.main_btn_text{background:<varstring>;}.main_btn_img{background:<varstring>;}';

$colortable['main_btn_text_color']['name']='按钮----文字颜色';
$colortable['main_btn_text_color']['deft']='#000';
$colortable['main_btn_text_color']['css']='.main_btn_text{color:<varstring>;}.main_btn_img{color:<varstring>;}';



$colortable['frame_frame']['name']='框架页----左右分割线色';
$colortable['frame_frame']['deft']='#000';
$colortable['frame_frame']['css']='.frame_left{border-color:<varstring>;}';

$colortable['frame_topbtm']['name']='框架页----顶底边框色';
$colortable['frame_topbtm']['deft']='#000';
$colortable['frame_topbtm']['css']='.frame_top{border-color:<varstring>;}.frame_btm{border-color:<varstring>;}';




if(isset($_POST["form_css"]) or isset($_POST["form_save"])){ 
	$cssfilestring='@charset "utf-8";'."\n";
	$jsfilestring="";
	foreach($colortable as $key => $val){
		if($_POST[$key]!=''){
			$cssfilestring.=str_replace('<varstring>',$_POST[$key],$val['css'])."\n";
		}
		if($val['jsvar']!='' and $_POST[$key]!=''){
			$jsfilestring.='var '.$val['jsvar'].'="'.$_POST[$key].'";';
		}
	}
	//echo $jsfilestring;
	if(!is_readable(dirname(__FILE__).'/'.$systemskin)){
		mkdir(dirname(__FILE__).'/'.$systemskin,0777);
   }
   if(isset($_POST["form_css"])){
		file_put_contents(dirname(__FILE__).'/'.$systemskin.'/default.css',$cssfilestring);
		file_put_contents(dirname(__FILE__).'/'.$systemskin.'/var.js',$jsfilestring);
	
		echo "<script language=\"JavaScript\" type=\"text/JavaScript\"> " ;  
		echo "top.location=top.location;";
		echo "</script>";
   }else{
	   $_POST['save_name']=str_replace('css','',$_POST['save_name']);
	   $_POST['save_name']=str_replace('.','',$_POST['save_name']);
	   if($_POST['save_name']=='') $_POST['save_name']='backup';
	   $_POST['save_name']='save.'.$_POST['save_name'].'.'.date('Y_m_d_h_i_s',time());
	   
	   file_put_contents(dirname(__FILE__).'/'.$systemskin.'/'.$_POST['save_name'],$cssfilestring);
   }
}






?>
<?php echo str_replace('../include/','../',hlcphp_html_head());?>
<script src="../js/jscolor.min.js"></script>
<script src="../js/raphael.js"></script>
<SCRIPT type="text/javascript">

function Form_Submit(){
	return true;
}
function setTextColor(picker,id){
	document.getElementById(id).value="#"+picker.toString();
}
</SCRIPT>
</head>
<body style="font-size:12px" class='main_body'>

<?php
$tabtablearr=array();
$tabtablearr[0]['name']="环境设置";
$tabtablearr[0]['path']="../../sys_envir/default.php";
$tabtablearr[1]['name']="主题设置";
$tabtablearr[1]['path']="zdy.php";
$tabtablearr[1]['selected']="true";
gettabtable($tabtablearr);
?>

<div style="border:1px solid #444;margin:15px">
<form method="POST" onsubmit="return Form_Submit()">
<table>
<?php  
$cssfilepath=file_get_contents(dirname(__FILE__).'/'.$systemskin.'/'.$_GET['opencss']);
$thisname="";
foreach($colortable as $key => $val){
$mycss=str_replace('\<varstring\>','([^\}]*)',preg_quote($val['css']));
	preg_match("/".$mycss."/U",$cssfilepath,$matchs);
	$val['deft']=$matchs[1];
	
	//if($thisname=='') $thisname=explode('----',$val['name'])[0];
	$margin_top="";
	if($thisname!=(explode('----',$val['name'])[0])){
		$thisname=explode('----',$val['name'])[0];
		$margin_top='<tr><td colspan="10" style="height:5px;border-top:1px solid #888;color:#00f;background:#eee">'.$thisname.':</td></tr>';
		
	}
	echo $margin_top.'
	<tr><td align="left" >'.$val['name'].'</td>
		<td style="width:60px">
			<input class="jscolor {valueElement:null ,value:\''.$val['deft'].'\', onFineChange:\'setTextColor(this,\\\''.$key.'\\\')\'}" style="border:1px solid #000;width:100px;cursor:pointer"/>
		</td>
		<td>
			<input id="'.$key.'" name="'.$key.'" type="text" value="'.$val['deft'].'" style="width:300px"/>
		</td>
		<td>'.$val['tip'].'</td>
	</tr>';
}
?>
	 
	<tr>
		<td align="center" colspan='3'><input type="submit" value="应用该主题" name="form_css" id="form_css" style="border:1px solid #000;padding:2px"><input type="submit" value="保存该主题为" name="form_save" id="form_save" style="border:1px solid #000;margin-left:5px;padding:2px"><input type="text" id="save_name" name="save_name" style="width:100px" value="backup"/></td>
	</tr>
	<tr>
		<td align="center" colspan='3'>应用主题后要刷新页面并清除IE缓存</td>
	</tr>
</table>
</form>
</div>


<div style="border:1px solid #444;margin:15px">
已保存的主题:
<table>
<?php
$mydir = dir(dirname(__FILE__)."/".$systemskin);
while($mydir and $file=$mydir->read()){
	if(($file!=".") AND ($file!="..") and substr($file,0,4)=='save'){
		$files=explode('.',$file,2);
		echo '
			<tr>
				<td>'.$files[1].'</td>
				<td><input type="button" value="打开" onclick="location=\'zdy.php?opencss='.$file.'\';"/></td>
				<td><input type="button" value="删除" onclick="location=\'zdy.php?delsave='.$file.'\';"/></td>
			</tr>';
	}
}
?>
</table>
</div>



<div style="border:1px solid #444;margin:15px">
<div >
如果需要以图片作背景，请先逐一上传图片，再在上面框中定义图片背景<br>
图片背景格式：url(image/title.gif) repeat-x top left<br> 
存放路径和存放文件名可以不填，默认存放在系统对应的主题目录下的image目录下
</div>


<form id="form_file" method="POST" action="" enctype="multipart/form-data">
	<input type="file" value="浏览" id="file" name="file" style="width:400px"/>
	<input type="submit" value="上传" style="margin-right:30px"/>
	存放路径:
	<input type="text" id="savepath" name="savepath" value="" placeholder="可不填"/>
	存放文件名:
	<input type="text" id="savename" name="savename" value="" placeholder="可不填"/>
</form>

<?php

$mydir = dir(dirname(__FILE__)."/".$systemskin."/image");
while($mydir and $file=$mydir->read()){
	if(($file!=".") AND ($file!="..")){
		echo '
		<table style="display:inline">
		<tr><td><img src="'.$systemskin.'/image/'.$file.'" width="50px" height="50px"/></td></tr>
		<tr><td>'.$file.'</td></tr>
		<tr><td><input type="button" value="删除" onclick="location=\'zdy.php?delpic='.$file.'\';"/></td></tr>
		</table>';
	}
}


?>

</div>


<br>
<br>
<br>
<br>
<div style="border:1px solid #444;margin:15px">
创建一条由上到下的渐变图，颜色可选6种，色条后面的数字表示色值位置的百分比，为空则不用<br>
	宽：<input id="myw" type="text" value="800" style="width:50px"/>
	高：<input id="myh" type="text" value="35"  style="width:50px"/>
	色1: <input id="color_1" class="jscolor" value="7fabc9" style="border:1px solid #000;width:60px"><input id="bfb_1" type="text" value="0" style="width:25px"/>
	色2: <input id="color_2" class="jscolor" value="7fabc9" style="border:1px solid #000;width:60px"><input id="bfb_2" type="text" value="50" style="width:25px"/>
	色3: <input id="color_3" class="jscolor" value="7fabc9" style="border:1px solid #000;width:60px"><input id="bfb_3" type="text" value="100" style="width:25px"/>
	色4: <input id="color_4" class="jscolor" value="7fabc9" style="border:1px solid #000;width:60px"><input id="bfb_4" type="text" value="" style="width:25px"/>
	色5: <input id="color_5" class="jscolor" value="7fabc9" style="border:1px solid #000;width:60px"><input id="bfb_5" type="text" value="" style="width:25px"/>
	色6: <input id="color_6" class="jscolor" value="7fabc9" style="border:1px solid #000;width:60px"><input id="bfb_6" type="text" value="" style="width:25px"/>
	<input type="button" value="画图" onclick="makepic()"/>

<div id="liner" style='position:relative;border: 1px solid #ccc;height:100px;margin:3px'><div style='position:absolute;left
;0;top:0;z-index:100;'>honglicheng</div></div>
 
<script type="text/javascript">
var paper=Raphael("liner",800,100);
function makepic(){
		paper.clear();
		var myw=document.getElementById('myw').value;
		var myh=document.getElementById('myh').value;
		
         var fillstr="270";
		 for(var i=1;i<7;i++){
			 var bfb=document.getElementById('bfb_'+i).value;
			 var color=document.getElementById('color_'+i).value;
			 if(bfb!=''){
				fillstr=fillstr+"-#"+color+":"+bfb;
			 }
		 }
		 
         var rect1=paper.rect(10,20,myw,myh,0);// left top width height radius
         rect1.attr({
             fill:fillstr,
             stroke:"none"
         });
         //rect1.attr({opacity: 0.5});
         //paper.text(100,10,"text");
  }
 </script>



</div>



</body>
</html>


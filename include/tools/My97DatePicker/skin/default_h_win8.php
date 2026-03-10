<?php header('Cache-Control: no-cache');?>
<?php require_once('../bth_connections/syslog.php'); ?>
<?php require_once('../bth_connections/mysite.php'); ?>
<?php require_once('../bth_include/pagination.php'); ?>
<?php require_once(dirname(__FILE__)."/../bth_include/GetSQLValueString.php");?>
<?php

$query_check2="select * from sdblj.bh_src where  srid = '".$_GET['srid']."'";                                   //
$objcheck2 = mysql_query($query_check2, $mysite) or die(mysql_error());                              //
$onecheck2 = mysql_fetch_assoc($objcheck2);                                                         //
$rowcheck2 = mysql_num_rows($objcheck2); 

//确定查询串
$query_Recordset1=" from sdbljlog.blog_h_win_policy a left join sdblj.bh_src b on a.FromHost=b.sr_ip where  srid = '".$_GET['srid']."'";


//确定查询串
$query_Recordset1=" from sdbljlog.blog_h_win_usb a left join sdblj.bh_src b on a.FromHost=b.sr_ip where  srid = '".$_GET['srid']."'";


//确定条件

if(isset($_GET['submitbz'])){
	$query_Recordset1=$query_Recordset1." and yearweek(ReceivedAt)=yearweek(now())";
	$submittype="本周";
}

else if(isset($_GET['submitby'])){
	$query_Recordset1=$query_Recordset1." and EXTRACT(YEAR_MONTH FROM ReceivedAt) = EXTRACT(YEAR_MONTH FROM (now()))";
	$submittype="本月";
}

else if(isset($_GET['submitqb'])){
	$submittype="全部";
}

else if(isset($_GET['submit1'])){
	$submittype="查询";
	if(isset($_GET['rq1'])&&$_GET['rq1']!=""){
   		$query_Recordset1=$query_Recordset1." and ReceivedAt >'".$_GET['rq1']."'";
   		$rq1=$_GET['rq1'];
	}
	if(isset($_GET['rq2'])&&$_GET['rq2']!=""){
   		$query_Recordset1=$query_Recordset1." and ReceivedAt <'".$_GET['rq2']."'";
   		$rq2=$_GET['rq2'];
	}	
}else{
	$query_Recordset1=$query_Recordset1." and date(ReceivedAt)=date(now())";
	$submittype="本日";
}




if(isset($_GET['facility'])&&$_GET['facility']!=""){
//   	$query_Recordset1=$query_Recordset1." and Facility='".$_GET['facility']."'";
   	$facility=$_GET['facility'];
}
if(isset($_GET['severity'])&&$_GET['severity']!=""){
//   	$query_Recordset1=$query_Recordset1." and Priority <='".$_GET['severity']."'";
   	$severity=$_GET['severity'];
}
if(isset($_GET['msg'])&&$_GET['msg']!=""){
//   	$query_Recordset1=$query_Recordset1." and Message like'%".$_GET['msg']."%'";
   	$msg=$_GET['msg'];
}

//完整查询串
$query_check1="select * ".$query_Recordset1." order by ReceivedAt desc";

$query_count1="select count(*) as count ".$query_Recordset1." ";


//以上确定了查询串，下面分页

////////////////////////////////////////////////////////////////////////////////////////////////////////////
//分页总共设置5个地方，inlude文件、查询、条数、页码和转页按钮                                                    //
if(isset($_GET['total_row'])){                                                                            //
   $rowcount1=$_GET['total_row'];                                                              //
}else{                                                                                                    //
   $objcount1 = mysql_query($query_count1, $mysite) or die(mysql_error());
   $onecount1 = mysql_fetch_assoc($objcount1); 
   $rowcount1 = $onecount1['count'];                                              //
}                                                                                                         //
                                                                                                          //
$pags=getpage($_GET,$rowcount1,25);                                                            //
                                                                                                          //
$query_check1=$query_check1." limit ".$pags['start_row'].",25";                                   //
$objcheck1 = mysql_query($query_check1, $mysite) or die(mysql_error());                              //
$onecheck1 = mysql_fetch_assoc($objcheck1);                                                         //
$rowcheck1 = mysql_num_rows($objcheck1);                                                      //
////////////////////////////////////////////////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////////////////////////////////////////////
//导出字段
$fld_string="naslogin&date=时间&nasip=网络设备&shortname=设备名&username=用户名&se_callingip=客户端&reply=回复=15";
$pdf_changefiled[0]="reply&Access-Accept=成功&Access-Reject=失败=255,102,153=magenta";
//////////////////////////////////////////////////////////////////////////////////////////////////////////


?>




<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>无标题文档</title>
<link href="../bth_css/my_style.css" rel="stylesheet" type="text/css">
<script language="javascript" type="text/javascript" src="../tool_My97DatePicker/WdatePicker.js"></script>

</head>

<body >
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td class="tab_td">
    <span class="tab_6word">主机</span>
    <a href="default_d.php" onClick="parent.leftFrame.location='left.php?head=d'" class="tab_6word_a">数据库</a>
    <a href="default_w.php" onClick="parent.leftFrame.location='left.php?head=w'" class="tab_6word_a">web应用</a>
    <a href="default_f.php" onClick="parent.leftFrame.location='left.php?head=f'" class="tab_6word_a">ftp/sftp</a>
    
   </td>
</tr>


<tr><td class="search_td">
  <form id="form1" name="form1" method="get" action="<?php echo $_SERVER["PHP_SELF"];?>" class="form_css0">
  <input type="hidden" id="srid" name="srid" value="<?php echo $_GET['srid']?>"/>
  <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin:2px">
    <tr>
      <td width="60"  valign="bottom" style="font-size:smaller"><input type="submit" name="submitbz" class="button1" id="submitbz" value="本周" style="width:60px; height:20px" /></td>
      
      <td width="60"  valign="bottom" style="font-size:smaller"><input type="submit" name="submitby" class="button1" id="submitby" value="本月" style="width:60px; height:20px" /></td>
      
      <td width="60"  valign="bottom" style="font-size:smaller"><input type="submit" name="submitqb" class="button1" id="submitqb" value="全部" style="width:60px; height:20px" /></td>
    
    
      <td width="58" height="23"  align="right">开始时间:</td>
      <td width="90" >
        <input name="rq1" type="text" id="rq1" class="input_text0" style="width:70px" value="<?php echo $rq1;?>"/>
        <img onClick="WdatePicker({el:'rq1'})" src="../tool_My97DatePicker/skin/datePicker.gif" width="16" height="22" align="absmiddle"/> 
      </td>
      
      <td width="58" align="right">截止时间:</td>
      <td width="90"><input name="rq2" type="text" id="rq2" class="input_text0" style="width:70px"  value="<?php echo $rq2;?>"/>
          <img src="../tool_My97DatePicker/skin/datePicker.gif" width="16" height="22" align="absmiddle" onClick="WdatePicker({el:'rq2'})" /> </td> 

      <td width="30" align="right">功能</td>
      <td width="85"><select  id="facility" name="facility" class="input_select0" style="width:80">
         <option value="" selected >全部</option>
        </select></td>
      
      <td width="30" align="right">级别</td>
      <td width="65">
        <select  id="severity" name="severity" class="input_select0" style="width:60">
         <option value="" selected >全部</option>
     </select>
      </td>
      
      <td width="30" align="right">信息</td>
      <td width="200" ><input id="msg" name="msg" type="text" class="input_text0" value="<?php echo $msg?>" style="width:200px" /></td>
      
      <td  valign="bottom" style="font-size:smaller"><input type="submit" name="submit1" class="button1" id="submit1" value="查询" style="width:80px; height:20px" /></td>
    </tr>
    
   
  </table>
</form>
      
</td></tr>   

<tr>
    <td  class="search_td">
    
    <a href="<?php echo "load.php?loadp=h_win0&srid=".$_GET['srid']; ?>" class="tab_4word_a" >汇总日志</a>
    <a href="<?php echo "load.php?loadp=h_win1&srid=".$_GET['srid']; ?>" class="tab_4word_a" >登录审计</a>
    <a href="<?php echo "load.php?loadp=h_win2&srid=".$_GET['srid']; ?>" class="tab_4word_a" >进程跟踪</a>
    <a href="<?php echo "load.php?loadp=h_win3&srid=".$_GET['srid']; ?>" class="tab_4word_a" >账户登录</a>
    <a href="<?php echo "load.php?loadp=h_win4&srid=".$_GET['srid']; ?>" class="tab_4word_a" >使用权限</a>
    <a href="<?php echo "load.php?loadp=h_win5&srid=".$_GET['srid']; ?>" class="tab_4word_a" >系统事件</a>
    <a href="<?php echo "load.php?loadp=h_win6&srid=".$_GET['srid']; ?>" class="tab_4word_a" >账户管理</a>
    <a href="<?php echo "load.php?loadp=h_win7&srid=".$_GET['srid']; ?>" class="tab_4word_a" >对象访问</a>
    <span class="tab_4word">策略改变</span>
    <a href="<?php echo "load.php?loadp=h_win9&srid=".$_GET['srid']; ?>" class="tab_4word_a" >目录服务</a>
    <a href="<?php echo "load.php?loadp=h_win10&srid=".$_GET['srid']; ?>" class="tab_4word_a" >usb事件</a>
   </td>
</tr>

<tr>
   <td  class="title_td" >
     <div class="title_div1" ></div>
     <div class="title_div_font1">策略改变日志</div>
     <div class="title_div_font1" style="width:300px">
	     <?php
		 	echo "资源名:".$onecheck2['sr_name']."&nbsp;&nbsp;&nbsp;&nbsp;";
		    echo $submittype."  总计<".$pags['total_row'].">条";
		    if( $totalRows_Recordset1 != "0" ) {
			   echo "  当前". ($pags['start_row']+1) ."-".($pags['stop_row']);
		    }
		 ?>
     </div>
     <div class="title_div_font2" align="right">第<?php echo $pags['cur_page']?>页/<?php echo $pags['max_page']?> 页</div>
   </td>
</tr> 

<tr>
<td>
      <table cellspacing="0" cellpadding="0" width="100%"  class="table_in">
          <tr>
            <td class="table_tit">设备名</td>
            <td class="table_tit">设备ip</td>
            <td class="table_tit">严重级别</td>
            <td class="table_tit">源名</td>
            <td class="table_tit">时间</td>
            <td class="table_tit">用户名</td>
            <td class="table_tit">事件日志类型</td>
            <td class="table_tit">消息</td>
            
          </tr>
          
          
     <?php if($rowcheck1 != 0 ){ do { ?>
          <tr>
            <td class="table_td_wihter">
			<?php echo $onecheck1['sr_name']; ?></td>
            <td class="table_td_wihter">
              <?php if ($onecheck1['sr_ip']=='') echo '&nbsp;';else echo $onecheck1['sr_ip'];?>
            </td>
            <td class="table_td_wihter">
              <?php if ($onecheck1['Criticality']=='') echo '&nbsp;';else echo $onecheck1['Criticality']; ?>
            </td>
            <td class="table_td_wihter">
              <?php if ($onecheck1['SourceName']=='') echo '&nbsp;';else echo $onecheck1['SourceName']; ?>
            </td>
             <td class="table_td_wihter">
              <?php if ($onecheck1['eventtime']=='') echo '&nbsp;';else echo $onecheck1['eventtime']; ?>
            </td>
            <td class="table_td_wihter">
              <?php if ($onecheck1['UserName']=='') echo '&nbsp;';else echo $onecheck1['UserName']; ?>
            </td>
            
            <td class="table_td_wihter">
              <?php if ($onecheck1['EventLogType']=='') echo '&nbsp;';else echo $onecheck1['EventLogType']; ?>
            </td>
            <td class="table_td_wihter">
              <?php if ($onecheck1['ExpandedString']=='') echo '&nbsp;';else echo $onecheck1['ExpandedString']; ?>
            </td>
            
           
         
          </tr>
       <?php  } while (($onecheck1 = mysql_fetch_assoc($objcheck1)));
		  } else{ ?>
          <tr>
            <td colspan="11" class="table_td_wihter" align="center">未找到任何日志信息</td>
          </tr>
          <?php } ?>
        </table>
</td></tr></table>

<?php 
schoose($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING'],$pags);
?>
</body>
</html>

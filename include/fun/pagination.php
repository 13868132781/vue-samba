<?php

function getpage($sql){
	$rets=array();
	global $_GET,$mysite;
	
	//没页显示的条数
	
	$rets['pagerows']=25;

	
	//总条数
	if($_GET['total_row']==""){
		$sqls=mysql_query("select count(*) as num ".$sql,$mysite) or die(mysql_error());
		$row_Recordset1 = mysql_fetch_assoc($sqls);
		if($row_Recordset1['num']=='')
			$rets['total_row']=0;
		else 
			$rets['total_row']=$row_Recordset1['num'];
	}else{
		$rets['total_row']=$_GET['total_row'];
	}
	//当前是第几页
	$rets['cur_page']=1;
	if ($_GET['cur_page']!='') {   
		$rets['cur_page']=$_GET['cur_page'];
	}
	if($_GET['goto']!=""){
		$rets['cur_page']=$_GET['goto'];
	}
	if($rets['cur_page']<1){
		$rets['cur_page']=1;
	}
	
	//总页数，不需要页面间传递，每页单独计算
	if($rets['total_row'] == 0){
         $rets['max_page']=1;
    }else{
         $rets['max_page']=ceil($rets['total_row']/$rets['pagerows']);
    }

	
	 //开始显示的第一条结果
     $rets['start_row']=($rets['cur_page']-1)*$rets['pagerows'];
	 
     $rets['stop_row']=$rets['start_row']+$rets['pagerows'];
	 
	 if ($rets['stop_row'] > $rets['total_row']){
	    $rets['stop_row']=$rets['total_row'];
	 }
	
	return $rets;
}


   
  //翻页按钮, 除了已有的参数外，添加total_row和cur_page
function schoose($mypage){
	$php=$_SERVER["PHP_SELF"];
		
	$currentp=$mypage['cur_page'];

    global $_GET;
	if ($mypage['cur_page']>1){
	    $before=$mypage['cur_page']-1;
	}else{
	    $before=$mypage['cur_page'];
	}
	 
	if($mypage['cur_page']<$mypage['max_page']){
	    $after=$mypage['cur_page']+1;
	}else{
	    $after=$mypage['cur_page'];
	} 

	  
     if (!empty($_SERVER['QUERY_STRING'])) {
	 //通过explode，用&符号将字符串分割为数组
	    $params = explode("&", $_SERVER['QUERY_STRING']);
		$newquer="";
		foreach ($params as $param) {
           if (stristr($param, "total_row") == false && stristr($param, "cur_page") == false) {
		       if ($newquer=="")
			      $newquer=$param;
			   else
                  $newquer=$newquer."&".$param;
		}
    }
        $nowphp=$php."?".$newquer."&total_row=".$mypage['total_row']."&cur_page=";
     }else{
	    $nowphp=$php."?total_row=".$mypage['total_row']."&cur_page=";
	 }
	 
   
	 echo " 
	 <table width='100%'   border='0'  cellpadding='2' cellspacing='0' style='margin-top:5px'>
       <tr>
      <td width='20%'>&nbsp;</td>
      <td width='6%'><a onClick='pageupdate()' href='".$nowphp."1' class='main_btn_text'>第一页</a></td>
      <td width='6%'><a onClick='pageupdate()' href='".$nowphp.$before."' class='main_btn_text'>上一页</a></td>
      <td width='6%'><a onClick='pageupdate()' href='".$nowphp.$after."' class='main_btn_text'>下一页</a></td>
      <td width='8%'><a onClick='pageupdate()' href='".$nowphp.$mypage['max_page']."' class='main_btn_text'>最后一页</a></td>	 ";  
	
////////////////////////////////////////////////////////////////////////////////////////////////////////////		  
	 $update_href=$nowphp.$currentp."&update=1";
     echo " <td width='6%'><a href='".$nowphp.$currentp."&update=1' class='main_btn_text' onClick='pageupdate()'>刷新</a></td>
       <td width='10%'>&nbsp;</td>
    ";
	

////////////////////////////////////////////////////////////////////////////////////////////////////////////

     //设置每页显示条数、设置跳转页码  
      echo "<form id='formpage' name='formpage' method='get' action='".$nowphp.$_GET['goto']."'>";
	    foreach($_GET as $key=>$value){
	   echo "<input type='hidden' name='".$key."' value='".$value."'/>";
	  }   
	   
	  echo"
	  
	  <td width='3%'> 
      <input  name='goto' type='text'  id='goto' size='3' maxlength='5' style='height:21px;line-height:21px' value=''/>
	  </td>
	  <td width='6%' align='center'>
      <span type='submit' class='main_btn_text' id='submit_pagenum' style='width:80px;' onclick='formpage.submit();pageupdate();' >转到</span>
      </td>
	  

	  </form>";

  echo "</tr></table>";
  
  }
?>
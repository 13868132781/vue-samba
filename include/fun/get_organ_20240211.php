<?php

//关于树状列表的id。tr的id，存放全局计数，第一个td的id，存放'类型_id',如机构为'organ_id',第一个td的abbr存放level，axis存放杂项

$organcount=0;

function show_organs($a){
	//$top      是否显示顶级节点      
	//$db:      数据库,
	//$type:    类型是本地还是远程,
	//$long:    显示几栏,
	//$id:      哪个机构下,
	//$level:   第几层,
	//$brf:     前缀,
	//$hs:      是否执行函数,参数形式0.show ,里面的0表示设备在前，1表示机构在前，后面是函数名
	//$remote:  是否列出远程,'',不列出，
	//$cut:     是否剔除无叶节点的机构 '',不剔除，1不剔除(所有判断型参数，设置成''和'1'，不要用0)
	//$box:     是否显示checkbox , ''不显示
	//$boxid    要打勾或不打勾的id
	//$boxy     上面的id是打勾还是不打勾
	//$html     html类型，有select  table left maps等
	//$sfid     select时选中哪一个
	//zdy       机构的查询限制.可以是orgs、或者是自定义的
	//m         表示对于执行的函数，是否有more的机构
	//leftclick html是left时点击所触发的函数
	//order     空位不显示排序，任何字符都表示显示
	//bnum      表示前面空几格
	//tag       表示显示那一类，主机数据库网络设备等
	//showid    用于select，表示每个option的内容，机构名前面显示编号
	
	global $organcount,$_HLCPHP; 

	//组建顶级行	开始
	if($a['id']=='0'&&$a['top']){
		if($a['html']=='maps'){
			$a['brf']="顶级—>";
			if(check_for_orid(0)){
				$res_top = "0:顶级|";
			}
			
		}else if($a['html']=='select'){
			$a['brf']="顶级—>";
			if(check_for_orid(0)){
				$res_top = "<option value='0' ";
				if('0'==$a['sfid']) $res_top.= "selected='selected'";
				$res_top.=">".$a['showid']."顶级</option>";
			}
		}else{
			$show="<img id='".$organcount."_folder' style='vertical-align: middle' src='../include/image/tree/folderopen.gif'>";
			if($a['box']!=''){
				$show.="<input type='checkbox' id='".$organcount."_box' style='width:16px; height:16px' onClick='boxcheck(this)' ";
				if ($a['boxid']=='-'||($a['boxy']&&strstr($a['boxid'],',0,'))||(!$a['boxy']&&!strstr($a['boxid'],',0,')) )
					$show.= "checked";
				$show.="/>";
			}
			$res_top.= "<tr id='".$organcount."' zdy-level='".$a['level']."' class='main_listtable_celltr' onMouseOver='over_selected_tr(this)'>";
			$res_top.= "<td abbr='".$a['level']."' id='organ_0'>".$show."顶级</td>";	
			for($i=1;$i<$a['long'];$i++)   
            	$res_top.=  "<td ></td>";
			$res_top.= "</tr>";
		}
		
		$organcount++;
		$a['level']=$a['level']+1;
	}
	//组建顶级行 结束
	
	$res="";
	if($a['zdy']=='') {
		$limitorgans="";
	}else{
		$limitorgans=" and or_id in (".trim(get_or_fars($a['db'],$a['zdy']),',').")";
	}
	if($a['tag']&&$a['tag']!='') $tagselect=" and or_tag like '%,".$a['tag'].",%' ";
	$qury="select * from radius.organ where or_fid = '".$a['id']."' ".$limitorgans." ".$tagselect." order by or_order";
	$qury_res=mysql_query($qury, $a['db']) or die(mysql_error());
	$qury_line=mysql_fetch_assoc($qury_res);
	$qury_rows=mysql_num_rows($qury_res);
	
	
	$hss=explode('.',$a['hs']);
	
	if(check_for_orid($a['id'])){
	if($a['hs']!=''&&$hss[0]=='0'){
		$res_ex=$hss[1]($a);		
	}
	}
	
	$t=0;
	if($qury_rows!=0){
		do{
		
			$res_my='';$res_my_d='';
			$t++;
			if($a['html']=='maps'){
				$bref=$a['brf'].$qury_line['or_name']."—>";
				if(check_for_orid($qury_line['or_id'])){
					$res_my.=$qury_line['or_id'].":".$a['brf'].$qury_line['or_name']."|";
				}
			}else if($a['html']=='select'){
			
				$bref=$a['brf'].$qury_line['or_name']."—>";
				if(check_for_orid($qury_line['or_id'])){
					$res_my.= "<option value='".$qury_line['or_id']."' ";
					if($qury_line['or_id']==$a['sfid']) $res_my.= "selected='selected'";
					if($a['showid']) $a['showid']=$qury_line['or_id']."：";
					$res_my.= ">".$a['showid'].$a['brf'].$qury_line['or_name']."</option>";
				}
				
			}else if($a['html']=='left'){
				if(!$a['leftclick']) $a['leftclick']='leftclick';
			
				if($t!=$qury_rows||($a['hs']!=''&&$hss[0]=='1'&&$res_ex!='')){
		  			$plus="<img id='".$organcount."_plus' name='1' onClick='folder(this)' style='vertical-align:middle' src='../include/image/tree/minus.gif'>";
    	  			$bref=$a['brf']."<img style='vertical-align: middle' src='../include/image/tree/line.gif'>";
	  			}else{
					$plus="<img id='".$organcount."_plus' name='1' onClick='folder(this)' style='vertical-align:middle' src='../include/image/tree/minusbottom.gif'>";
		  			$bref=$a['brf']."<img style='vertical-align: middle' src='../include/image/tree/empty.gif'>";
	  			}
      			$show="<img id='".$organcount."_folder' style='vertical-align: middle' src='../include/image/tree/folderopen.gif'>";
				
				if($a['box']!=''){
					$show.="<input type='checkbox' id='".$organcount."_box' style='width:16px; height:16px' onClick='boxcheck(this)' ";
					if ($a['boxid']=='-'||($a['boxy']&&strstr($a['boxid'],','.$qury_line['or_id'].','))||(!$a['boxy']&&!strstr($a['boxid'],','.$qury_line['or_id'].',')) )
						$show.= "checked";
					$show.="/>";
				}
				
				$res_my.= "<tr class='tr_cell' id='".$organcount."' zdy-level='".$a['level']."'>";
				$res_my.= "<td abbr='".$a['level']."' id='organ_".$qury_line['or_id']."'>".$a['brf'].$plus.$show;
				$res_my.="<span style='cursor:pointer' onclick='".$a['leftclick']."(this)'>";
				$res_my.=$qury_line['or_name'];
				$res_my.="</span></td>";
				for($i=1;$i<$a['long'];$i++)   
            		$res_my.=  "<td class='table_td_wihter1'></td>";
				$res_my.= "</tr>";
				
			
			
			}else{
			
				if($t!=$qury_rows||($a['hs']!=''&&$hss[0]=='1'&&$res_ex!='')){
		  			$plus="<img id='".$organcount."_plus' name='1' onClick='folder(this)' style='vertical-align:middle' src='../include/image/tree/minus.gif'>";
    	  			$bref=$a['brf']."<img style='vertical-align: middle' src='../include/image/tree/line.gif'>";
	  			}else{
					$plus="<img id='".$organcount."_plus' name='1' onClick='folder(this)' style='vertical-align:middle' src='../include/image/tree/minusbottom.gif'>";
		  			$bref=$a['brf']."<img style='vertical-align: middle' src='../include/image/tree/empty.gif'>";
	  			}
      			$show="<img id='".$organcount."_folder' style='vertical-align: middle' src='../include/image/tree/folderopen.gif'>";
				if($a['box']!=''){
					$show.="<input type='checkbox' id='".$organcount."_box' style='width:16px; height:16px' onClick='boxcheck(this)' ";
					if ($a['boxid']=='-'||($a['boxy']&&strstr($a['boxid'],','.$qury_line['or_id'].','))||(!$a['boxy']&&!strstr($a['boxid'],','.$qury_line['or_id'].',')) )
						$show.= "checked";
					$show.="/>";
				}
			
				$res_my.= "<tr id='".$organcount."' zdy-level='".$a['level']."' class='main_listtable_celltr' onMouseOver='over_selected_tr(this)'>";
				$res_my.= "<td abbr='".$a['level']."' id='organ_".$qury_line['or_id']."' ";
				if($a['bnum']!=''){
					$res_my.= "></td><td class='table_td_wihter1' ";
				}
				$res_my.=">".$a['brf'].$plus.$show.$order_left.$qury_line['or_name'].$order_right."</td>";
				for($i=1;$i<$a['long'];$i++) {
					$order_left="&nbsp;";
					if($i==1 and $a['order']!=''){
						$order_left.="<span style='cursor:pointer;padding-right:10px' onClick=\"order('".$qury_line['or_id']."')\">排序</span>";
					}
					if($i==1 and $a['moder']!=''){
						$order_left.="<span style='cursor:pointer' onClick=\"user_adds('?mode=mod&orid=".$qury_line['or_id']."')\">批量修改</span>";
					}
					
					$res_my.=  "<td class='table_td_wihter1'>".$order_left."</td>";

				}
				$res_my.= "</tr>";
				
			}
			
			$organcount++;  
			
			if($qury_line['or_type']=='local'){
				$b=$a;
				$b['id']=$qury_line['or_id'];
				$b['level']=$a['level']+1;
				$b['brf']=$bref;
				$res_my_d.=show_organs($b);
				
			}else if($a['remote']!=''){
				$mysqlinfo=explode('_',$qury_line['or_type']);
				$dbr = mysql_pconnect($mysqlinfo[0], $mysqlinfo[1], $mysqlinfo[2]) ;
				if($dbr){ 
					mysql_query("SET NAMES 'utf8'", $dbr) or die(mysql_error());
					$b=$a;
					$b['db']=$dbr;
					$b['type']=$qury_line['or_type'];
					$b['id']=$mysqlinfo[3];
					$b['level']=$a['level']+1;
					$b['brf']=$bref;
					$res_my_d.=show_organs($b);
				}
			}
			if(($a['cut']=='')||($a['hs']=='')||($res_my_d!='')){
				$res.=$res_my.$res_my_d;
			}
			
		}while($qury_line=mysql_fetch_assoc($qury_res));
	}
	
	
	if(check_for_orid($a['id'])){
	if($a['hs']!=''){
		if($hss[0]=='0'){
			$res=$res_ex.$res;
		}else{
			if($res==''){
				$res_ex=$hss[1]($a);
			}else{
				$b=$a;
				$b['m']='1';
				$res_ex=$hss[1]($b);
			}
			$res.=$res_ex;
		}		
	}
	}
		
	
	return $res_top.$res;
}



//2//////////////////////////////////////////////////////
//该函数在需要显示某个用户或设备所属，在系统用户那里用到,产生:顶级—>ceng1—>ceng2
function get_or_belong($db,$id){
	$qury="select * from radius.organ where or_id = '".$id."'";
	$qury_res=mysql_query($qury, $db) or die(mysql_error());
	$qury_line=mysql_fetch_assoc($qury_res);
	$qury_rows=mysql_num_rows($qury_res);
	if($qury_rows!=0){
		$res='—>'.$qury_line['or_name'];
		$res=get_or_belong($db,$qury_line['or_fid']).$res;
	}
	return $res;
}


//3/////////////////////////////////////////////////////////
//获取某一个ID的所有父机构加上自己这个ID，只被下面这个get_or_fars调用
function get_or_far($db,$id){
	$res=$id;
	$qury="select * from radius.organ where or_id = '".$id."'";
	$qury_res=mysql_query($qury, $db) or die(mysql_error());
	$qury_line=mysql_fetch_assoc($qury_res);
	$qury_rows=mysql_num_rows($qury_res);

	if($qury_rows!=0)
		$res.=','.get_or_far($db,$qury_line['or_fid']);
	return $res;
}


//获取某几个ID下所有父机构
//未实现 获取传进来的orgs和limit orgs的交际
function get_or_fars($db,$orgs){
	// - 是遗留设定，现在不用了
	if($orgs=='' or $orgs=='-' )
		return '';
		
	$res=',';
	$ids=explode(',',trim($orgs,','));
	foreach($ids as $key=>$id){
		$res.=','.get_or_far($db,$id);
	}
	$res.=',';
	$resa = explode(',',trim($res,","));
	$resa = array_unique($resa);
	$res = join(',',$resa);
	if($res!=''){
		$res = ','.$res.',';
	}
	return $res;
}


//4/////////////////////////////////////////////////////
//这个函数用于搜索那些隶属于管理机构下的东西如ip、user、sysuser
//这个机构limit上向外面唯一提供的接口
function sql_for_orgs($lj,$zd,$in='in'){
	global $_HLCPHP;
	if(!$in)
		$in='in';
	if($_HLCPHP['global']['organs']==''){
		return "";
	}else{
		return $lj.' '.$zd." ".$in." (".trim($_HLCPHP['global']['organs'],',').")";
	}
}

function check_for_orid($orid){ 
	global $_HLCPHP;
	if($_HLCPHP['global']['organs']==''){
		return "true";
	}else{
		if(strstr($_HLCPHP['global']['organs'],','.$orid.',')){
			return "true";
		}else{
			return "";
		}
	}
}





/*
0节点是个虚节点，数据库里不存在，他的父节点是空，用法其他的和普通节点相同
隶属（organ），是每一个设备、用户、管理员都具有的属性，从0节点及其以下节点中任一个。，一般于权限无关，只是在设备和用户添加时，设置初始权限时，借用了一下隶属
权限（orgs），可以为空，表全部，或者是节点集合如 ',0,3,6,7,'，每个节点，表示该节点下的设备、用户、管理员，不包括子节点，子节点要自己包含进去


*/
?>
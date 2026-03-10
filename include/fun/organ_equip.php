<?php
//获取结构
function hlcphp_organ_equip_array($type,$fid,$getequip){
	global $hlcphp_mysql_lj,$hlcphp_mysql_db;
	$return_array=array();
	
	
	////////////////////////////////////////////////////////////////////////
	if($type=='organ'){
		mysql_select_db($hlcphp_mysql_db, $hlcphp_mysql_lj);
		$mysql_sql="SELECT * FROM hlcphp.aaa_organ where ao_oid='".$fid."'";
		$mysql_obj=mysql_query($mysql_sql, $hlcphp_mysql_lj) or die(mysql_error());
		$mysql_num=mysql_num_rows($mysql_obj);
		while($mysql_row=mysql_fetch_array($mysql_obj)){
			$return_array_one['mytype']="organ";
			$return_array_one['mydata']=$mysql_row;
			$return_array_one['mylist']=hlcphp_organ_equip_array('organ',$mysql_row['aoid'],$getequip);
			Array_push($return_array,$return_array_one);
		}
		
		if($getequip == true){
			mysql_select_db($hlcphp_mysql_db, $hlcphp_mysql_lj);
			$mysql_sql="SELECT * FROM hlcphp.aaa_equip where ae_eid='0' and ae_oid='".$fid."'";
			$mysql_obj=mysql_query($mysql_sql, $hlcphp_mysql_lj) or die(mysql_error());
			$mysql_num=mysql_num_rows($mysql_obj);
			while($mysql_row=mysql_fetch_array($mysql_obj)){
				$return_array_one['mytype']="equip";
				$return_array_one['mydata']=$mysql_row;
				$return_array_one['mylist']=hlcphp_organ_equip_array('equip',$mysql_row['aeid'],$getequip);
				Array_push($return_array,$return_array_one);
			}
		}
	}
	
	
	////////////////////////////////////////////////////////////////////////////
	if($type=='equip' and $getequip == true){
		mysql_select_db($hlcphp_mysql_db, $hlcphp_mysql_lj);
		$mysql_sql="SELECT * FROM hlcphp.aaa_equip where ae_oid='0' and ae_eid='".$fid."'";
		$mysql_obj=mysql_query($mysql_sql, $hlcphp_mysql_lj) or die(mysql_error());
		$mysql_num=mysql_num_rows($mysql_obj);
		while($mysql_row=mysql_fetch_array($mysql_obj)){
			$return_array_one['mytype']="equip";
			$return_array_one['mydata']=$mysql_row;
			$return_array_one['mylist']=hlcphp_organ_equip_array('equip',$mysql_row['aeid'],$getequip);
			Array_push($return_array,$return_array_one);
		}
	}
	
	
	return $return_array;
}




$hlcphp_organcount=0;
function hlcphp_get_listtable_celltr($mylist,$a){
	$return_data="";

	//echo "<pre>";print_r($mylist);echo "</pre>";
	
	if($a['left']==true) $main_listtable_celltr=""; else $main_listtable_celltr="main_listtable_celltr";
	
	foreach($mylist as $key=>$val){
		$return_data_this="";$return_data_next="";
		
		if (count($mylist)==($key+1)){
			if($val['mylist']!=NULL){
				$plus="<img id='".$hlcphp_organcount."_plus' name='1' onClick='folder(this)' style='vertical-align:middle' src='../include/image/tree/minusbottom.gif'>";
			}else{
				$plus="<img id='".$hlcphp_organcount."_plus' name='1' onClick='folder(this)' style='vertical-align:middle' src='../include/image/tree/joinbottom.gif'>";
			}
			$bref=$a['brf']."<img style='vertical-align: middle' src='../include/image/tree/empty.gif'>";
		}else{
			if($val['mylist']!=NULL){
				$plus="<img id='".$hlcphp_organcount."_plus' name='1' onClick='folder(this)' style='vertical-align:middle' src='../include/image/tree/minus.gif'>";
			}else{
				$plus="<img id='".$hlcphp_organcount."_plus' name='1' onClick='folder(this)' style='vertical-align:middle' src='../include/image/tree/join.gif'>";
			}
			$bref=$a['brf']."<img style='vertical-align: middle' src='../include/image/tree/line.gif'>";
		}
    	
	
	
	
		if($val['mytype']=='organ'){
		
			$show="<img id='".$hlcphp_organcount."_folder' style='vertical-align: middle' src='../include/image/tree/folder.gif'>";
			$return_data_this='<tr class="'.$main_listtable_celltr.'"><td>'.$a['brf'].$plus.$show.get_listtable_celltr_organ($val['mydata']).'</td></tr>';
		
		}else{
		
			$show="<img id='".$hlcphp_organcount."_folder' style='vertical-align: middle' src='../include/image/tree/page.gif'>";
			$return_data_this='<tr class="'.$main_listtable_celltr.'"><td>'.$a['brf'].$plus.$show.get_listtable_celltr_equip($val['mydata']).'</td></tr>';
		
		}
		
		$hlcphp_organcount++;
		
		
		if ($val['mylist']!=NULL){
			$b=$a;
			$b['brf']=$bref;
			$return_data_next=hlcphp_get_listtable_celltr($val['mylist'],$b);
		}
		
		$return_data.=$return_data_this.$return_data_next;
	}
	
	return $return_data;
}

?>
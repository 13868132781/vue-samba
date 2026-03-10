<?php
function del_organ($db,$id){
	$query_get = "select * from radius.organ where or_id ='".$id."'";
	$get = mysql_query($query_get, $db) or die(mysql_error());
	$row_get = mysql_fetch_assoc($get);
	
	
	//删除下属机构
	$query_get1 = "select * from radius.organ where or_fid ='".$id."'";
	$get1 = mysql_query($query_get1, $db) or die(mysql_error());
	$row_get1 = mysql_fetch_assoc($get1);
	$totalRows_get1= mysql_num_rows($get1);
	if($totalRows_get1!=0){
		do{
			del_organ($db,$row_get1['or_id']);
		}while($row_get1 = mysql_fetch_assoc($get1));
	}
	
	

	//删除NAS里
	$query_get1 = "select * from radius.nas where organ ='".$id."'";
	$get1 = mysql_query($query_get1, $db) or die(mysql_error());
	$row_get1 = mysql_fetch_assoc($get1);
	$totalRows_get1= mysql_num_rows($get1);
	if($totalRows_get1!=0){
		do{
			del_nas($db,$row_get1['id']);
		}while($row_get1 = mysql_fetch_assoc($get1));
	}
/*
	//删除sso里
	$sso_head=array('h'=>'主机','d'=>'数据库','n'=>'网络设备','w'=>'web应用','f'=>'ftp');
	foreach($sso_head as $head => $name){
		$query_get1 = "select * from sdblj.b".$head."_src where sr_organ ='".$id."'";
		$get1 = mysql_query($query_get1, $db) or die(mysql_error());
		$row_get1 = mysql_fetch_assoc($get1);
		$totalRows_get1= mysql_num_rows($get1);
		if($totalRows_get1!=0){
			do{
				del_sso_src($db,$row_get1['srid'],$head,$name);
			}while($row_get1 = mysql_fetch_assoc($get1));
		}
	}
*/
	//删除user
	$query_get1 = "select * from radius.rad_user where organ ='".$id."'";
	$get1 = mysql_query($query_get1, $db) or die(mysql_error());
	$row_get1 = mysql_fetch_assoc($get1);
	$totalRows_get1= mysql_num_rows($get1);
	if($totalRows_get1!=0){
		do{
			del_user($db,$row_get1['userID']);
		}while($row_get1 = mysql_fetch_assoc($get1));
	}
/*	
	//删除user
	$query_get1 = "select * from sdblj.baccount where ac_organ ='".$id."'";
	$get1 = mysql_query($query_get1, $db) or die(mysql_error());
	$row_get1 = mysql_fetch_assoc($get1);
	$totalRows_get1= mysql_num_rows($get1);
	if($totalRows_get1!=0){
		do{
			del_ssouser($db,$row_get1['acid']);
		}while($row_get1 = mysql_fetch_assoc($get1));
	}
	*/
	//删除管理员
	$query_get1 = "select * from system.sys_user where organ ='".$id."'";
	$get1 = mysql_query($query_get1, $db) or die(mysql_error());
	$row_get1 = mysql_fetch_assoc($get1);
	$totalRows_get1= mysql_num_rows($get1);
	if($totalRows_get1!=0){
		do{
			del_suser($db,$row_get1['userID']);
		}while($row_get1 = mysql_fetch_assoc($get1));
	}
	
	
	$query_get = "delete from radius.organ where or_id ='".$id."'";
	mysql_query($query_get, $db) or die(mysql_error());
	write_systemlog($db,'删除','机构',$row_get['or_name'],'bth','');
}




function del_user($db,$id){
	$query_get = "select * from radius.rad_user where userID ='".$id."'";
	$get = mysql_query($query_get, $db) or die(mysql_error());
	$row_get = mysql_fetch_assoc($get);

	$query_get = "delete from radius.radcheck where UserName ='".$row_get['UserName']."'";
	mysql_query($query_get, $db) or die(mysql_error());
	
	$query_get = "delete from radius.radreply where UserName ='".$row_get['UserName']."'";
	mysql_query($query_get, $db) or die(mysql_error());

	$query_get = "delete from radius.rad_user where userID ='".$id."'";
	mysql_query($query_get, $db) or die(mysql_error());
	write_systemlog($db,'删除','自然人用户',$row_get['UserName'],'bth','');
}




function del_ssouser($db,$id){
	$query_get = "select * from sdblj.baccount where acid ='".$id."'";
	$get = mysql_query($query_get, $db) or die(mysql_error());
	$row_get = mysql_fetch_assoc($get);

	$query_get = "delete from sdblj.baccount_priv where ap_acid ='".$id."'";
	mysql_query($query_get, $db) or die(mysql_error());

	$query_get = "delete from  sdblj.baccount where acid ='".$id."'";
	mysql_query($query_get, $db) or die(mysql_error());
	write_systemlog($db,'删除','自然人用户',$row_get['ac_acc'],'bth','');
}



function del_nas($db,$id){
	$query_get = "select * from radius.nas where id ='".$id."'";
	$get = mysql_query($query_get, $db) or die(mysql_error());
	$row_get = mysql_fetch_assoc($get);

	$query_get = "delete from radius.radcheck where Value ='".$row_get['nasname']."'";
	mysql_query($query_get, $db) or die(mysql_error());

	$query_get = "delete from radius.nas where id ='".$id."'";
	mysql_query($query_get, $db) or die(mysql_error());
	write_systemlog($db,'删除','网络设备',$row_get['nasname'],'aaa','');
}



function del_sso_src($db,$id,$type,$name){

	$query_get = "select * from sdblj.b".$type."_src where srid ='".$id."'";
	$get = mysql_query($query_get, $db) or die(mysql_error());
	$row_get = mysql_fetch_assoc($get);
	
	
	$query_get1 = "select * from sdblj.b".$type."_src_user where su_srid ='".$id."'";
	$get1 = mysql_query($query_get1, $db) or die(mysql_error());
	$row_get1 = mysql_fetch_assoc($get1);
	$totalRows_get1= mysql_num_rows($get1);
	if($totalRows_get1!=0){
		do{
			del_sso_src_user($db,$row_get1['suid'],$type,$name);
		}while($row_get1 = mysql_fetch_assoc($get1));
	}
	
	

	$query_get = "delete from sdblj.b".$type."_src where srid ='".$id."'";
	mysql_query($query_get, $db) or die(mysql_error());
	write_systemlog($db,'删除',$name.'资源',$row_get['sr_name'],'sso','');
}



function del_sso_src_user($db,$id,$type,$name){
	$query_get = "select * from sdblj.b".$type."_src_user where suid ='".$id."'";
	$get = mysql_query($query_get, $db) or die(mysql_error());
	$row_get = mysql_fetch_assoc($get);

	$query_get = "delete from sdblj.b".$type."_src_user where suid ='".$id."'";
	mysql_query($query_get, $db) or die(mysql_error());
	write_systemlog($db,'删除',$name.'资源账户',$row_get['su_user'],'sso','');
}



function del_suser($db,$id){
	$query_get = "select * from system.sys_user where userID ='".$id."'";
	$get = mysql_query($query_get, $db) or die(mysql_error());
	$row_get = mysql_fetch_assoc($get);

	$query_get = "delete from system.sys_user where userID ='".$id."'";
	mysql_query($query_get, $db) or die(mysql_error());
	write_systemlog($db,'删除','管理员',$_POST['or_name'],'bth','');
}

?>
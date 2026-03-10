<?php

function gettabtable($arr,$mod=''){
#function gettabtable($arr){
	echo '<table class="main_tabtable" cellspacing="0" cellpadding="0" id="gettabtable_table" >';
	echo	'<tr class="main_tabtable_tabtr">';
	echo 		'<td class="main_tabtable_tabtr_lefttd">&nbsp;</td>	';
	$righthtml="&nbsp;";
	foreach($arr as $key=>$val){
		if($key==='right'){
			$righthtml=$key;
			continue;
		}
		if($val['path']!='') $val['click'].=";location='".$val['path']."';";
		if($mod!='') $val['click'].=";gettabtable_change_tab(this);";
		echo 	'<td align="center" onclick="'.$val['click'].'" ';
		if($val['selected']==''){
					echo 'class="main_tabtable_tabtr_ungettd"';
		}else{
					echo 'class="main_tabtable_tabtr_gettd"';
		}
		echo 		" style='cursor:pointer'>".$val['name'];
		echo 	'</td>';
		echo	'<td align="center" class="main_tabtable_tabtr_splittd"></td>';
	}
	echo 		'<td class="main_tabtable_tabtr_righttd">'.$righthtml.'</td>';
	echo 	'</tr>';
	echo '</table>';
	echo "
	<script language=\"JavaScript\" type=\"text/JavaScript\">
		function  gettabtable_change_tab(obj){
			var aTmp = g('gettabtable_table').getElementsByTagName('td');
			for(var i=0;i<aTmp.length;i++)
			{	
				if(aTmp[i].className=='main_tabtable_tabtr_gettd')
					aTmp[i].className='main_tabtable_tabtr_ungettd';
			}
			obj.className='main_tabtable_tabtr_gettd';
		} 
    </script>" ;
}



$_HLCPHP['tabs']="gettabtable";
?>
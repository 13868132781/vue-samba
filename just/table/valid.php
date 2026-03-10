<?php
namespace just\table;

class valid {
	/*
	传进来时，formVal条目数可能比list少
	规则数据后，formVal条目数和list相同，客户端没传来的项目，补全为空
	*/
	public static function check($list,&$formVal){
		$back=[];
		foreach($list as $item){
			
			$col = $item['col'];
			
			//规整数据
			if(!isset($formVal[$col])){
				$formVal[$col]='';
			}
			if($formVal[$col]===null){
				$formVal[$col]='';
			}
			if(is_numeric($formVal[$col])){//是数字
				$formVal[$col] .='';
			}
			if(!is_string($formVal[$col])){//非字符串
				$back[$col] = '数据类型不合法';
				continue;
			}
			$formVal[$col] = trim($formVal[$col]);
			
			if($formVal[$col]===''){
				if(isset($item['ask']) and $item['ask']){
					$back[$col] = '不可为空';
					continue;
				}else if(!isset($item['valid']) or $item['valid']['type']!='same'){
					continue;
				}
			}
			
			if(($item['type']=='select' or $item['type']=='radio') and !isset($item['valid'])){
				$item['valid']=['type'=>'option'];
			}
			
			
			if(isset($item['valid']) ){
				$valid = $item['valid'];
				$func = '_'.$valid['type'];
				$res = self::$func($item,$list,$formVal);
				if($res){
					$back[$col] = $res;
				}
				
			}
		}
		if(count($back)>0){
			return $back;
		}
		return ;
	}
	
	private static function _text($item,$list,$formVal){
		$back='';
		$valid = $item['valid'];
		$col = $item['col'];
		$val = $formVal[$col];
		
		$len = mb_strlen($val,'UTF-8');
		if(isset($valid['min'])){
			$min = intval($valid['min']);
			if($min>0 and $len<$min){
				if($back!=''){$back.='，';}
				$back.='长度少于'.$valid['min'];
			}
		}
		if(isset($valid['max'])){
			$max = intval($valid['max']);
			if($max>0 and $len>$max){
				if($back!=''){$back.='，';}
				$back.='长度大于'.$valid['max'];
			}
		}
		return $back;
	}
	
	private static function _cxty($item,$list,$formVal){
		$back='';
		$valid = $item['valid'];
		$col = $item['col'];
		$val = $formVal[$col];
		$vals = explode('_',$val);
		$len = intval($vals[0]);
		if($len.''!=$vals[0]){
			$back='长度值不合法';
		}
		return $back;
	}
	
	private static function _email($item,$list,$formVal){
		$back='';
		$valid = $item['valid'];
		$col = $item['col'];
		$val = $formVal[$col];
		
		$myreg = '/^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/';
		if (!preg_match($myreg ,$val)) {
			$back='邮箱不合法';
		}
		return $back;
	}
	private static function _ip($item,$list,$formVal){
		$back='';
		$valid = $item['valid'];
		$col = $item['col'];
		$val = $formVal[$col];
		
		$myreg = '/^(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$/';
		if (!preg_match($myreg ,$val)) {
			$back='IP不合法';
		}
		return $back;
	}
	
	private static function _number($item,$list,$formVal){
		$back='';
		$valid = $item['valid'];
		$col = $item['col'];
		$val = intval($formVal[$col]);
		
		if($val.'' != $formVal[$col]){
			$back = '包含非数字字符';
			return $back;
		}
		
		if(isset($valid['min'])){
			$min = intval($valid['min']);
			if($min>0 and $val<$min){
				if($back!=''){$back.='，';}
				$back.='不可小于'.$valid['min'];
			}
		}
		if(isset($valid['max'])){
			$max = intval($valid['max']);
			if($max>0 and $val>$max){
				if($back!=''){$back.='，';}
				$back.='不可大于'.$valid['max'];
			}
		}
		return $back;
	}
	
	private static function _password($item,$list,$formVal){
		$back='';
		$valid = $item['valid'];
		$col = $item['col'];
		$val = $formVal[$col];
		
		$cxty = isset($valid['cxty'])?$valid['cxty']:'';
		$cxtys = explode('_',$cxty);
		
		$len = intval($cxtys[0]);
		if($len>0 and strlen($val)<$len){
			$back='长度不够';
			return $back;
		}
		array_shift($cxtys);
		
		if(count($cxtys)==0){
			return $back;
		}
		
		$backa = [];
		$pxnum = 0;
		if($cxtys[count($cxtys)-1]=='xze'){
			array_pop($cxtys);
			$pxnum = 1;
		}
		
		foreach($cxtys as $cx){
			if($cx=='low'){
				$regex='/[a-z]/';
				if(!preg_match($regex,$val)){
					$backa[]='小写';
				}
			}
			if($cx=='big'){
				$regex='/[A-Z]/';
				if(!preg_match($regex,$val)){
					$backa[]='大写';
				}
			}
			if($cx=='num'){
				$regex='/[0-9]/';
				if(!preg_match($regex,$val)){
					$backa[]='数字';
				}
			}
			if($cx=='tes'){
				$regex='/[\~\!\@\#\$\%\^\&\*\<\>\,\.\?\/]/';
				if(!preg_match($regex,$val)){
					$backa[]='特殊字符';
				}
			}
		}
		if(count($backa)>$pxnum){
			$back='必须再包含'.join('，',$backa);
			if($pxnum==1){
				$back.='中的'.(count($backa)-1).'项';
			}			
		}
		
		
		return $back;
	}
	
	private static function _same($item,$list,$formVal){
		$back='';
		$valid = $item['valid'];
		$col = $item['col'];
		$sameAs = $valid['as'];
		$valmy = "";
		$valyou = "";
		if(isset($formVal[$col])){
			$valmy = $formVal[$col];
		}
		if(isset($formVal[$sameAs])){
			$valyou = $formVal[$sameAs];
		}
		
		if($valmy!=$valyou){
			$asName="";
			foreach($list as $lis){
				if($lis['col']==$sameAs){
					$asName = $lis['name'];
					break;
				}
			}
			$back="与‘".$asName."’的值不一致";
		}
		
		return $back;
	}
	
	private static function _phone($item,$list,$formVal){
		$back='';
		$valid = $item['valid'];
		$col = $item['col'];
		$val = $formVal[$col];
		
		$myreg = '/^1[3-9]\d{9}$/';
		if (!preg_match($myreg ,$val)) {
			$back='手机号不合法';
		}
		return $back;
	}
	
	private static function _url($item,$list,$formVal){
		$back='';
		$valid = $item['valid'];
		$col = $item['col'];
		$val = $formVal[$col];
		
		$myreg = '/^(https|http):\/\//';
		if (!preg_match($myreg ,$val)) {
			$back='url不合法';
		}
		return $back;
	}
	
	private static function _option($item,$list,$formVal){
		$back='';
		$valid = $item['valid'];
		$col = $item['col'];
		$val = $formVal[$col];
		$options = $item['options'];
		
		if (!isset($options[$val])) {
			$back='值不在选项中';
		}
		return $back;
	}
	
}

?>
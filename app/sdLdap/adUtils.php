<?php
namespace app\sdLdap;

class adUtils{
	
	static public function displayBit($val){
		$thisval = intval($val);
		$showarr = [];
		for ($i = 0; $i < 32; $i++) {
			$baval = ($thisval & (1 << $i)) ? 1 : 0;
			$valkey = pow(2,$i);
			$showarr[$valkey] = $baval;		
		}
		return $showarr;
	}
	
	static public function getVal($ent,$col,$val=''){
		if(isset($ent[$col])){
			return $ent[$col][0];
		}
		return $val;
	}
	
	
	
	static public $attrFormType=[
					'text'=>'文本',
					'select'=>'单选',
					'selectm'=>'多选横向',
					'selectms'=>'多选纵向',
				];
	static public $attrShowType=[
					'n' => '原始值',
					'map' => '单映射',
					'tt1' => '时间(秒)',
					'tt2' => '时间(分)',
					'tt3' => '时间(天)',
					'bit' => '字节位',
					'date' => '日期'
				];
	
	
	//ad值转可读值
	static public function val_ad2show($val,$t,$tex){
		if($t=='tt1'){//时间戳，秒
			$val1 = trim($val,'-');
			$val = $val.' ( '.($val1/10000000).' 秒 )';
		}
		if($t=='tt2'){//时间戳，分钟
			$val1 = trim($val,'-');
			$val = $val.' ( '.($val1/10000000/60).'分钟 )';
		}
		if($t=='tt3'){//时间戳，天
			$val1 = trim($val,'-');
			$val = $val.' ( '.($val1/10000000/86400).' 天 )';
		}
		if($t=='bit'){//字节位
			$typeEx = json_decode($tex,true);
			$thisval = intval($val);
			$showval = $val;
			for ($i = 0; $i < 32; $i++) {
				$baval = ($thisval & (1 << $i)) ? 1 : 0;
				$valkey=pow(2,$i);
				if($baval and isset($typeEx[$valkey])){
					if($showval){$showval.='<br/>';}
					$showval.=$typeEx[$valkey];
				}
			}
			$val = $showval;
		}
		
		if($t=='date'){//字节位
			if($val){
				$toffset = strtotime('1970-01-01 00:00:00')-strtotime('1601-01-01 00:00:00');
				$unixTimestamp = intval((intval($val)/10000000)-$toffset);
				$val = $val.' ( '.date('Y-m-d', $unixTimestamp).')';
			}
		}
		
		return $val;
	}
	//原始值转表单值
	static public function val_ad2form($val,$t,$tex){
		if($t=='tt1'){//时间戳，秒
			$val1 = trim($val,'-');
			$val = $val1/10000000;
		}
		if($t=='tt2'){//时间戳，分钟
			$val1 = trim($val,'-');
			$val = $val1/10000000/60;
		}
		if($t=='tt3'){//时间戳，天
			$val1 = trim($val,'-');
			$val = $val1/10000000/86400;
		}
		if($t=='bit'){//字节位
			$typeEx = json_decode($tex,true);
			$thisval = intval($val);
			$showval = "";
			for ($i = 0; $i < 32; $i++) {
				$baval = ($thisval & (1 << $i)) ? 1 : 0;
				$valkey = pow(2,$i);
				if($baval and isset($typeEx[$valkey])){
					if($showval!==''){$showval.='&';}
					$showval.=$valkey;
				}
				
			}
			$val = $showval;
		}
		return $val;
	}
	//可读值转原始值
	static public function val_form2ad($val,$t,$tex){
		if($t=='tt1'){//时间戳，秒
			$val = '-'.(intval($val)*10000000);
		}
		if($t=='tt2'){//时间戳，分钟
			$val = '-'.(intval($val)*10000000*60);
		}
		if($t=='tt3'){//时间戳，天
			$val = '-'.(intval($val)*10000000*86400);
		}
		if($t=='bit'){
			$typeEx = json_decode($tex,true);
			$val1=explode('&',$val);
			$val2=0;
			foreach($val1 as $i=>$valo){
				if($valo  and isset($typeEx[$valo])){
					$val2+=$valo;
				}
			}
			$val = $val2;
		}
		return $val;
	}
	
	static public function createGuid() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0x0fff ) | 0x4000,
			mt_rand( 0, 0x3fff ) | 0x8000,
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}
	
	
}



?>
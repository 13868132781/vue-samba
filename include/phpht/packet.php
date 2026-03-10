<?php 
//处理数据包的各个函数
function packet_gettext($a){
//str  源数据
//long  多长算text
//fgf   分隔符
	$unhexdata=$a['str'];
	
	$msg="totallen:".strlen($unhexdata)."=====";

	//ascii/////////////////////////////////////////////////////////////////////////////////////////
	$matchs=array();
	preg_match_all("/[\x20-\x7E]{5,}/",$unhexdata,$matchs);
	$realdata_asc="";
	for($i=0;$i<count($matchs[0]);$i++){
		$realdata_asc.="|".$matchs[0][$i];
	}
	$msg.="ascii:".count($matchs[0]).":".strlen($realdata_asc)."=====";
			
			//utf8.   ascii和gbk时用这个，会为空。所以，若是utf8有长度的话，肯定是utf8编码/////////////////////////
	$matchs=array();
	preg_match_all("/([\x{4e00}-\x{9fa5}]|[\x20-\x7E]){5,}/u",$unhexdata,$matchs);
	$realdata_utf8="";
	for($i=0;$i<count($matchs[0]);$i++){
		$realdata_utf8.="|".$matchs[0][$i];
	}
	$msg.="utf8:".count($matchs[0]).":".strlen($realdata_utf8)."=====";
			
			
	//gbk    ascii和utf8时，用这个可以解释字符部分///////////////////////////////////////////////
	$matchs=array();
	//		preg_match_all("/([".chr(0xa1)."-".chr(0xff)."]|[\x20-\x7E]){5,}/",$unhexdata,$matchs);
	preg_match_all('/([\xA1-\xA9][\xA1-\xFE]|[\xB0-\xF7][\xA1-\xFE]|[\x20-\x7E]){5,}/',$unhexdata,$matchs);
	$realdata_gbk="";
	for($i=0;$i<count($matchs[0]);$i++){
		$realdata_gbk.="|".$matchs[0][$i];
	}
	$msg.="gbk:".count($matchs[0]).":".strlen($realdata_gbk)."=====";
			
			
	//ucs/////////////////////////////////////////////////////////////////////////////
	$matchs=array();
	//preg_match_all('/([\x20-\x7E]\x00){5,}.*$/',$unhexdata,$matchs);
	preg_match_all('/([\x20-\x7E]\x00){4}([\x00-\xFF][\x4E-\xFF]|[\x20-\x7E]\x00){1,}/',$unhexdata,$matchs);
	//这里先匹配了4个字符，再字符和汉字同时匹配，避免顺序错乱。但是就不能匹配到开头便是汉字的了
	$realdata_ucs="";
	for($i=0;$i<count($matchs[0]);$i++){
		$realdata_ucs.=chr(0x7C).chr(0x00).$matchs[0][$i];
	}
	$realdata_ucss="";
	for ($i=0;$i<(strlen($realdata_ucs)/2);$i++){
		if($realdata_ucs{$i*2+1}==chr(0x00)){
			$realdata_ucss.=$realdata_ucs{$i*2};
		}else{
			$thischar=iconv('UCS-2BE', 'UTF-8', $realdata_ucs{$i*2+1}.$realdata_ucs{$i*2});
			if($thischar==""){
				$thischar=mb_convert_encoding( $realdata_ucs{$i*2+1}.$realdata_ucs{$i*2},'UCS-2BE','UTF-8');
			}
			$realdata_ucss.=$thischar;
		}
	}
	$realdata_ucs=$realdata_ucss;
	$msg.="ucs:".count($matchs[0]).":".strlen($realdata_ucs)."=====";
			
	/////////////////////////////////////////////////////////////////////////////
			
	if(strlen($realdata_utf8)!=0){
		$realdata=$realdata_utf8;
		$msg.=utf8."=====";
	}else if(strlen($realdata_ucs)!=0){
		$realdata=$realdata_ucs;
		$msg.=ucs."=====";
	}else if(strlen($realdata_gbk) >= strlen($realdata_asc)){
		$realdata=iconv('GBK', 'UTF-8', $realdata_gbk);
		if(strlen($realdata)==0){
			$realdata=mb_convert_encoding($realdata_gbk,'GBK','UTF-8');
		}
		if(strlen($realdata)==0&&strlen($realdata_gbk)!=0){
			$msg.=gbk_fail_using_ascii."=====";
			$realdata=$realdata_asc;
		}else{
			$msg.=gbk."=====";
		}
				
	}else{
		$realdata=$realdata_asc;
		$msg.=asc."=====";
	}
			
			
	$realdata=trim($realdata,"|");


	return array('realdata'=>$realdata,'msg'=>$msg);

}
//$res=packet_gettext(array('str'=>'575757575700454545454545000'));
//echo $res['realdata'];
?>
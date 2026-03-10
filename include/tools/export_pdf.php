<?php require_once(dirname(__FILE__).'/../fun/api.php'); ?>
<?php require_once(dirname(__FILE__).'/tcpdf/config/lang/chi.php'); ?>
<?php require_once(dirname(__FILE__).'/tcpdf/tcpdf.php'); ?>
<?php

set_time_limit(1800);

/*
$exportdata['sql']="";
$exportdata['name']="";
$exportdata['wh']="";//A4、A3、A2、array(100,200)
$exportdata['font']="10";
$exportdata['color']['reply']['Access-Reject']="255,102,153";
$exportdata['fields']['date']['name']="时间";
$exportdata['fields']['reply']['name']="结果";
$exportdata['fields']['reply']['width']="15";
$exportdata['fields']['reply']['value']['Access-Accept']['name']="成功";
$exportdata['fields']['reply']['value']['Access-Reject']['name']="失败";
$exportdata['fields']['reply']['value']['Access-Reject']['color']="255,102,153";
*/

$exportdata=json_decode($_POST['exportdata'],true);


$format = PDF_PAGE_FORMAT;
if($exportdata['wh']!=""){$format = $exportdata['wh'];}
$mainfont=8;
if($exportdata['font']!=""){$mainfont=(int)$exportdata['font'];}




mysql_select_db($database_mysite, $mysite);
$query_rs_prod = stripslashes($exportdata['sql']); 
$rs_prod = mysql_query($query_rs_prod, $mysite) or die(mysql_error()); 
$row_rs_prod = mysql_fetch_assoc($rs_prod); 
$totalRows_rs_prod = mysql_num_rows($rs_prod);



// extend TCPF with custom functions
class MYPDF extends TCPDF 
   { 
              function Header()                                                             
              { 
						global $mainfont;
                    $this->SetFont('stsongstdlight','',$mainfont+2); 
                    $this->Write(25,$pdfname); 
                    $this->Ln(20);                                                   
              } 
              function Footer()                                                     
              { 
						global $mainfont;
                    $this->SetY(-15); 
                    $this->SetFont('stsongstdlight','',$mainfont+2); 
                    $this->Cell(10,10,'第'.$this->PageNo().'页'); 
              } 
       }
  
  
// create new PDF document

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, $format, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Nicola Asuni');
$pdf->SetTitle('TCPDF Example 011');
$pdf->SetSubject('TCPDF Tutorial');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 011', PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//set some language-dependent strings
$pdf->setLanguageArray($l);

// ---------------------------------------------------------

// set font

$pdf->SetFont('stsongstdlight', '', $mainfont+2);
// add a page
$pdf->AddPage();

$pdf->SetFillColor(235,235,235);

foreach( $exportdata['fields'] as $fields){  
	 if ($fields['width']!='')
	    $width= $fields['width'];
	 else
	    $width=30;                    
     $pdf->Cell((int)$width,6,$fields['name'],1,'','',true,'',1);
} 
$pdf->Ln(); 
	   

 
                                                                    

$pdf->SetFont('stsongstdlight', '', $mainfont);



if($totalRows_rs_prod!=0){    
   do                                                                              
   { 
		$color0=255;$color1=255;$color2=255;
		foreach( $exportdata['color'] as $keyc => $colors){
			if($colors[$row_rs_prod[$keyc]]!=''){
				$newcolor=explode(",",$colors[$row_rs_prod[$keyc]]);
				$color0=(int)$newcolor[0];$color1=(int)$newcolor[1];$color2=(int)$newcolor[2];
			}
		}
		
		foreach( $exportdata['fields'] as $keys => $fields){  
			if ($fields['width']!='')
				$width= $fields['width'];
			else
				$width=30;   
			
			
			if($fields['value'][$row_rs_prod[$keys]]['color']!=''){
				$newcolor=explode(",",$fields['value'][$row_rs_prod[$keys]]['color']);
				$pdf->SetFillColor($newcolor[0],$newcolor[1],$newcolor[2]);
			}else{
				$pdf->SetFillColor($color0,$color1,$color2);
			}
			
			
			
			if($fields['value'][$row_rs_prod[$keys]]['name']!='')
				$thisval=$fields['value'][$row_rs_prod[$keys]]['name'];
			else
				$thisval=$row_rs_prod[$keys];
			
			$pdf->Cell((int)$width,6,$thisval,1,'','',true,'',1);
		}
		

       $pdf->Ln(); 
   } while ($row_rs_prod = mysql_fetch_assoc($rs_prod)); 
}

// ---------------------------------------------------------
//Close and output PDF document
$pdf->Output($exportdata['name'].".pdf", true);

//============================================================+
// END OF FILE                                                
//============================================================+
?>
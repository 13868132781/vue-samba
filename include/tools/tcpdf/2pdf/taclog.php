<?php

require_once('../config/lang/chi.php');
require_once('../tcpdf.php');

// extend TCPF with custom functions
class MYPDF extends TCPDF 
   { 
              function Header()                                                             

              { 
                    $this->SetFont('stsongstdlight','',10); 
                    $this->Write(25,'操作审计'); 
                    $this->Ln(20);                                                   

              } 
              function Footer()                                                     

              { 
                    $this->SetY(-15); 
                    $this->SetFont('stsongstdlight','',10); 
                    $this->Cell(0,10,'第'.$this->PageNo().'页'); 

              } 
       }
  
// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

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
//$pdf->SetFont('arialunicid0', 'B', 10);
$pdf->SetFont('stsongstdlight', '', 10);
// add a page
$pdf->AddPage();

//Column titles
$header=array('时间','用户','网络设备ip','管理终端IP','端口','命令');                                           
       $width=array(30,20,20,25,20,35);
	   

       for($i=0;$i<count($header);$i++)                                                  

            $pdf->Cell($width[$i],6,$header[$i],1); 
       $pdf->Ln(); 
	   
	   $conn = mysql_connect("localhost", "root", "jbgsn!2716888");                                      
    mysql_query("SET NAMES 'UTF8'");
 
       mysql_select_db("radius", $conn);                                                     

       $query_rs_prod = "SELECT * FROM zlog_radius.rad_oper where EXTRACT(YEAR_MONTH FROM logdate) = EXTRACT(YEAR_MONTH FROM (now())) ORDER BY logdate DESC;"; 
       $rs_prod = mysql_query($query_rs_prod, $conn) or die(mysql_error()); 
       $row_rs_prod = mysql_fetch_assoc($rs_prod); 
       $totalRows_rs_prod = mysql_num_rows($rs_prod); 

$pdf->SetFont('stsongstdlight', '', 6);
       do                                                                              
       { 
            
			$pdf->Cell($width[0],6,$row_rs_prod['logdate'],1,'','','','',1); 
            $pdf->Cell($width[1],6,$row_rs_prod['username'],1,'','','','',1); 
            $pdf->Cell($width[2],6,$row_rs_prod['NAS_name'],1,'','','','',1); 
            $pdf->Cell($width[3],6,$row_rs_prod['NAC_address'],1,'','','','',1); 
			$pdf->Cell($width[4],6,$row_rs_prod['NAS_port'],1,'','','','',1); 
            $pdf->Cell($width[5],6,$row_rs_prod['cmd'],1,'','','','',1); 
            $pdf->Ln(); 
       } while ($row_rs_prod = mysql_fetch_assoc($rs_prod)); 


// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('taclog.pdf', true);

//============================================================+
// END OF FILE                                                
//============================================================+
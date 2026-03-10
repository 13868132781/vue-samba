<?php

set_time_limit(10);

require_once "../class.writeexcel_workbook.inc.php";
require_once "../class.writeexcel_worksheet.inc.php";

$fname = tempnam("/tmp", "radpostauth.xls");
$workbook = new writeexcel_workbook($fname);
$worksheet = $workbook->addworksheet();

# Set the column width for columns 1, 2, 3 and 4
//$worksheet->set_column(0, 9, 20);
$worksheet->set_column(0, 1, 20);
$worksheet->set_column(1, 2, 20);
$worksheet->set_column(2, 3, 20);
$worksheet->set_column(3, 4, 20);
$worksheet->set_column(4, 5, 15);
$worksheet->set_column(5, 6, 15);
$worksheet->set_column(6, 7, 15);

# Create a format for the column headings
$header = $workbook->addformat();
$header->set_bold();
$header->set_size(12);
$header->set_color('blue');

# Write out the data
$worksheet->write(0, 0, '时间', $header);
$worksheet->write(0, 1, '终端ip',   $header);
$worksheet->write(0, 2, '设备ip',  $header);
$worksheet->write(0, 3, '设备名称',  $header);
$worksheet->write(0, 4, '用户', $header);
$worksheet->write(0, 5, '密码', $header);
$worksheet->write(0, 6, '回复',  $header);

$conn = mysql_connect("localhost", "root", "jbgsn!2716888");                                      
    mysql_query("SET NAMES 'gb2312'");
 
       mysql_select_db("radius", $conn);                                                     

       $query_rs_prod = "SELECT r.*, n.shortname FROM zlog_radius.rad_logn r, nas n where EXTRACT(YEAR_MONTH FROM r.date) = EXTRACT(YEAR_MONTH FROM (now())) and r.nasip = n.nasname order by date desc ;"; 
       $rs_prod = mysql_query($query_rs_prod, $conn) or die(mysql_error()); 
       $row_rs_prod = mysql_fetch_assoc($rs_prod); 
       $totalRows_rs_prod = mysql_num_rows($rs_prod);


$i=1;
do
{
    
        $worksheet->write($i, 0, $row_rs_prod['date'],'','','',1);
		$worksheet->write($i, 1, $row_rs_prod['callingip'],'','','',1);  
		$worksheet->write($i, 2, $row_rs_prod['nasip'],'','','',1);
		$worksheet->write($i, 3, $row_rs_prod['shortname'],'','','',1); 
		$worksheet->write($i, 4, $row_rs_prod['user'],'','','',1);
		$worksheet->write($i, 5, "\"".$row_rs_prod['pass']."\"",'','','',1);
		$worksheet->write($i, 6, $row_rs_prod['reply'],'','','',1); 
		$i++;

} while ($row_rs_prod = mysql_fetch_assoc($rs_prod)); 

$workbook->close();

header("Content-Type: application/x-msexcel; name=\"radpostauth.xls\"");
header("Content-Disposition: inline; filename=\"radpostauth.xls\"");
$fh=fopen($fname, "rb");
fpassthru($fh);
unlink($fname);

?>
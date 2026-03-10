<?php

set_time_limit(10);

require_once "../class.writeexcel_workbook.inc.php";
require_once "../class.writeexcel_worksheet.inc.php";

$fname = tempnam("/tmp", "syslog.xls");
$workbook = new writeexcel_workbook($fname);
$worksheet = $workbook->addworksheet();

# Set the column width for columns 1, 2, 3 and 4
//$worksheet->set_column(0, 9, 20);
$worksheet->set_column(0, 1, 20);
$worksheet->set_column(1, 2, 10);
$worksheet->set_column(2, 3, 20);
$worksheet->set_column(3, 4, 20);
$worksheet->set_column(4, 5, 10);
$worksheet->set_column(5, 6, 15);
$worksheet->set_column(6, 7, 15);
$worksheet->set_column(7, 8, 10);
$worksheet->set_column(8, 9, 10);
$worksheet->set_column(9, 10, 30);
# Create a format for the column headings
$header = $workbook->addformat();
$header->set_bold();
$header->set_size(12);
$header->set_color('blue');

# Write out the data
$worksheet->write(0, 0, '操作时间', $header);
$worksheet->write(0, 1, '操作用户',   $header);
$worksheet->write(0, 2, '登录终端ip',  $header);
$worksheet->write(0, 3, '操作',  $header);
$worksheet->write(0, 4, '对象', $header);
$worksheet->write(0, 5, '设备ip',   $header);
$worksheet->write(0, 6, '用户名称',  $header);
$worksheet->write(0, 7, '单位',  $header);
$worksheet->write(0, 8, '区域', $header);
$worksheet->write(0, 9, '其他',   $header);

$conn = mysql_connect("localhost", "root", "jbgsn!2716888");                                      
    mysql_query("SET NAMES 'gb2312'");
 
       mysql_select_db("radius", $conn);                                                     

       $query_rs_prod = "SELECT * FROMzlog_system.sys_log"; 
       $rs_prod = mysql_query($query_rs_prod, $conn) or die(mysql_error()); 
       $row_rs_prod = mysql_fetch_assoc($rs_prod); 
       $totalRows_rs_prod = mysql_num_rows($rs_prod);


$i=1;
do
{

        $worksheet->write($i, 0, $row_rs_prod['time']);
		$worksheet->write($i, 1, $row_rs_prod['username']);  
		$worksheet->write($i, 2, $row_rs_prod['clientIp']);
		$worksheet->write($i, 3, $row_rs_prod['action']); 
		$worksheet->write($i, 4, $row_rs_prod['action_type']);
		$worksheet->write($i, 5, $row_rs_prod['nasip']); 
		$worksheet->write($i, 6, $row_rs_prod['nasname']); 
		$worksheet->write($i, 7, $row_rs_prod['addr']);
		$worksheet->write($i, 8, $row_rs_prod['dept']);
		$worksheet->write($i, 9, $row_rs_prod['content'],'','','',1);
		$i++;

} while ($row_rs_prod = mysql_fetch_assoc($rs_prod)); 

$workbook->close();

header("Content-Type: application/x-msexcel; name=\"syslog.xls\"");
header("Content-Disposition: inline; filename=\"syslog.xls\"");
$fh=fopen($fname, "rb");
fpassthru($fh);
unlink($fname);

?>

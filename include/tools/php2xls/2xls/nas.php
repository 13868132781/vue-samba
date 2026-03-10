<?php

set_time_limit(10);

require_once "../class.writeexcel_workbook.inc.php";
require_once "../class.writeexcel_worksheet.inc.php";

$fname = tempnam("/tmp", "nas.xls");
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
$worksheet->set_column(7, 8, 15);

# Create a format for the column headings
$header = $workbook->addformat();
$header->set_bold();
$header->set_size(12);
$header->set_color('blue');

# Write out the data
$worksheet->write(0, 0, '单位', $header);
$worksheet->write(0, 1, '区域',   $header);
$worksheet->write(0, 2, '设备ip',  $header);
$worksheet->write(0, 3, '设备名',  $header);
$worksheet->write(0, 4, '设备类型', $header);
$worksheet->write(0, 5, '端口',   $header);
$worksheet->write(0, 6, '社区',  $header);
$worksheet->write(0, 7, '配置备份标志',  $header);

$conn = mysql_connect("localhost", "root", "jbgsn!2716888");                                      
    mysql_query("SET NAMES 'gb2312'");
 
       mysql_select_db("radius", $conn);                                                     

       $query_rs_prod = "SELECT ad.id as addrID,ad.addr_name,n.id, n.nasname, n.shortname, n.type,n.ports, n.secret, n.community, n.description, n.deptNbr,m.name,m.orderID FROM nas n ,dept m, addr ad WHERE n.deptNbr = m.deptID and ad.id=n.addr_num"; 
       $rs_prod = mysql_query($query_rs_prod, $conn) or die(mysql_error()); 
       $row_rs_prod = mysql_fetch_assoc($rs_prod); 
       $totalRows_rs_prod = mysql_num_rows($rs_prod);


$i=1;
do
{

        $worksheet->write($i, 0, $row_rs_prod['addr_name'],'','','',1);
		$worksheet->write($i, 1, $row_rs_prod['name'],'','','',1);  
		$worksheet->write($i, 2, $row_rs_prod['nasname'],'','','',1);
		$worksheet->write($i, 3, $row_rs_prod['shortname'],'','','',1); 
		$worksheet->write($i, 4, $row_rs_prod['type'],'','','',1);
		$worksheet->write($i, 5, $row_rs_prod['ports'],'','','',1); 
		$worksheet->write($i, 6, $row_rs_prod['community'],'','','',1); 
		$worksheet->write($i, 7, $row_rs_prod['description'],'','','',1);
		$i++;

} while ($row_rs_prod = mysql_fetch_assoc($rs_prod)); 

$workbook->close();

header("Content-Type: application/x-msexcel; name=\"nas.xls\"");
header("Content-Disposition: inline; filename=\"nas.xls\"");
$fh=fopen($fname, "rb");
fpassthru($fh);
unlink($fname);

?>

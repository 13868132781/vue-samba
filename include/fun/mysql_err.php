<?php
require_once(dirname(__FILE__).'/mysqli.php');

$conn=$mysite;

// 假设 $conn 是已经成功建立的 mysqli 连接
 $sql = "SELECT * FROM radius.rad_user";
$result = mysql_query($sql,$conn);

// 检查查询是否成功
	 if ($result) {
	//	      查询成功，处理结果集
			          while ($row = mysql_fetch_assoc($result)) {
	//				          处理每一行数据
							      echo $row['UserName'];
								       }
	//	           释放结果集
				       mysql_free_result($result);
	 } else {
//		      查询失败，输出错误信息
			          echo "Error: " . $sql . "<br>" . mysql_error($conn);
	 }

// 关闭数据库连接
	 mysqli_close($conn);


?>

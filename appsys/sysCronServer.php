<?php


while(1){
	
	
	
	
	
	
	
	
	
	
	
	
	// 获取当前时间
	$currentTime = time();
	$currentSecond = date('s', $currentTime);

	// 计算下一分钟的第一秒
	$nextMinute = strtotime('+1 minute', $currentTime);
	$nextMinuteFirstSecond = strtotime(date('Y-m-d H:i:00', $nextMinute));

	// 计算等待时间
	$sleepTime = $nextMinuteFirstSecond - $currentTime;
	// 等待到下一分钟的第一秒
	sleep($sleepTime);
}


?>
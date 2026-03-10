<?php

function random($length, $numeric = 0) {
	if (!isset($length)) $length = rand(2,4);
	//加入$length=rand(2,4)就可以了
	PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
	if ($numeric) {
		$hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
	} else {
		$hash = '';
		//$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		$chars = '0123456789';
		$max = strlen($chars) - 1;
		for ($i = 0; $i < $length; $i++) {
			$hash .= $chars[mt_rand(0, $max)];
		}
	}
	return $hash;
} 



?>
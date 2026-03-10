<?php
#下载文件，主要是zip和exe文件
$_HLCPHP['download']="sddownload";
function sddownload($file){

	if (isset($file) && is_file($file)) {
		header('Content-Type: application/octet-stream');
		$fileName = basename($file);
		header('Content-Disposition: attachment; filename="' . $fileName . '"');
	
		$buffer = '';
		$cnt = 0;
		$handle = fopen($file, 'rb');
		if ($handle === false) {
			return false;
		}
		while (!feof($handle)) {
			$buffer = fread($handle, 1024 * 1024);
			echo $buffer;
			ob_flush();
			flush();
			if ($retbytes) {
				$cnt += strlen($buffer);
			}
		}
		$status = fclose($handle);
		if ($retbytes && $status) {
			return $cnt;
		}
		return $status;
	} else {
		echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		echo "文件".$file."不存在";
	}

}
?>

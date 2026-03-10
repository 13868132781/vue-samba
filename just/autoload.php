<?php
//定义类路径查找并加载的函数
//用new实例对象时，会自动调用该函数，查找类所在的文件，并加载
spl_autoload_register(function ($class_name) {
		$path = dirname(__DIR__).'\\';
		$realpath = $path.$class_name.'.php';
		$nicepath = str_replace("\\","/",$realpath);
		if(file_exists($nicepath)){
			require_once $nicepath;
			return true;
		}
		return false;
	
});
?>
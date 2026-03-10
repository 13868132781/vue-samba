<?php


namespace just\table;

trait _download{
	
	public final function api_download(){//单独提交，不像form那样一整套
		if(isset($_GET['filePath']) and isset($_GET['fileName'])){
		
			$filePath = $_GET['filePath'];
			$fileName = $_GET['fileName'];
			
			//$subprex='';
			if(!stristr($fileName,".") and stristr($filePath,".")){//获取后缀
				$pathArr = explode(".",$filePath);
				$subprex =  $pathArr[count($pathArr)-1];
				$fileName.=".".$subprex;
			}
			
			//只允许下载特定目录下的文件
			//if(strpos($filePath,'/srv/backuptxt/')===false){
			//	return $this->out(1,'','非法的下载路径：'.$filePath);
			//}
			
			//避免路径跳到别的目录
			$filePath = str_ireplace('..','',$filePath);
			
			$filePath = realpath(__DIR__."/../../")."/static/".$filePath;
			
			if(!file_exists($filePath)){
				return $this->out(1,'','下载文件出错：文件不存在：'.$filePath);
			}
			
			if(!is_file($filePath)){
				return $this->out(1,'',"下载文件出错：文件是个目录：".$filePath);
			}
			
		
			header("Accept-Ranges:bytes");
			//Content-Type: text/plain 会直接打开，而不是下载
			header("Content-Type: application/octet-stream; name=\"".$fileName."\"");
			header("Content-Disposition: inline; filename=\"".$fileName."\"");
			$fh=fopen($filePath, "r");
			ob_clean();//清除之前可能有的输出
			fpassthru($fh);
			fclose($fh);
			//unlink($filePath);
			exit(0);
		}
		
		
		$post = $this->POST;
		
		$filePath='';
		$fileName='';
		$funcName= 'downloadBefore_'.$post['goto'];
		if(method_exists($this,$funcName)){
			$res = $this->$funcName();
			if($res){
				$filePath = $res[0];
				$fileName = $res[1];
			}
		}
		if(!$filePath){
			return $this->out(1,'','未获得文件路径');
		}
		if(!file_exists($filePath)){
			return $this->out(1,'','文件未找到:'.$filePath);
		}
		
		return $this->out(0,[$filePath,$fileName]);
		
	}
}


?>
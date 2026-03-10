<?php
/*
大文件上传失败：
修改php的
upload_max_filesize = 200M
post_max_size = 200M
*/


namespace just\table;

trait _upload{
	
	public final function api_uploadAdd(){//单独提交，不像form那样一整套
		$post = $this->POST;
		
		//sdAlert($_FILES);
		//return $this->out(1,'','未获得上传文件');

		if(! isset($_FILES["sdfile"]) ){
			return $this->out(1,'','未获得上传文件');
		}
		
		$sdfile = $_FILES["sdfile"];
		if ($sdfile["error"] > 0){
			return $this->out(1,'',"Error: " . json_encode($sdfile));
		}
		
		if(!isset($sdfile['name'])){
			return $this->out(1,'','未获得上传文件名');
		}
		
		$fileName = str_ireplace('..','',$post['val']);
		if($fileName){
			$filePath = realpath(__DIR__."/../../")."/static/".$fileName;
			if(file_exists($filePath)){
				if(!is_file($filePath)){
					return $this->out(1,'',"删除旧文件出错：旧文件是个目录");
				}
				exec("sudo rm -I ".$filePath." 2>&1",$res,$code);
				if($code){
					return $this->out(1,'','删除旧文件出错：'.join('.',$res));
				}
			}
		}
		
		$fileName = sdRandomText(20);
		if(!$fileName){
			return $this->out(1,'','获取随机文件名出错');
		}
		if(stristr($sdfile['name'],".")){//获取后缀
			$pathArr = explode(".",$sdfile['name']);
			$fileName .=  ".".$pathArr[count($pathArr)-1];
		}
		$dirName = $post['dirName']?:'upload';
		$filePath = realpath(__DIR__."/../../")."/static/".$dirName."/".$fileName;
		if(is_file($filePath)){
			exec("sudo rm -I ".$filePath);
		}
		
		move_uploaded_file($sdfile["tmp_name"],$filePath );
		
		$this->DB()->where($this->colKey,$post['key'])->update([$post['col']=>$dirName."/".$fileName]);
		
		return $this->out(0,'','上传成功');
	}
	
	
	
	public final function api_uploadDel(){
		$post=$this->POST;
		$fileName = str_ireplace('..','',$post['val']);
		if(!$fileName){
			return $this->out(1,'','没有文件参数');
		}
		$filePath = realpath(__DIR__."/../../")."/static/".$fileName;
		
		if(!file_exists($filePath)){
			return $this->out(1,'','删除文件出错：文件不存在：'.$filePath);
		}
		
		if(!is_file($filePath)){
			return $this->out(1,'',"删除文件出错：文件是个目录：".$filePath);
		}
		
		exec("sudo rm -I ".$filePath." 2>&1",$res,$code);
		if($code){
			return $this->out(1,'',join('.',$res));
		}
		
		$this->DB()->where($this->colKey,$post['key'])->update([$post['col']=>'']);
		
		$res = $this->out(0,'','删除成功');
		return $res;
	}
	
}



?>
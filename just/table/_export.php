<?php
namespace just\table;

trait _export{
	
	public final function api_export(){
		
		$post = $this->POST;
		
		//非本页，就是全部，那把POST里的分页信息删除
		if($post['btnOption']!='dang'){
			unset($this->POST['fenye']);
		}
		
		$data = $this->gridData_modify();
		
		$gridSet = $this->gridSet();
		$columns = $gridSet['columns'];
		
		$fileName = $this->exportToCsv($data,$columns);
		
		return $this->out(0,$fileName);
	}
	
	
	public function exportToCsv($data,$columns){
		$clas = explode('\\',get_class($this));
		$fileName = "sdExport_".$clas[count($clas)-1]."_";
		
		$fname = tempnam("/tmp/", $fileName);
		$file = fopen($fname, 'w+'); 
		
		foreach( $columns as $fields){
			$thisval = $fields['name'];
			$thisval = iconv("utf8","gb2312",$thisval);
			$thisval = str_replace('"','\"',$thisval);
			$thisval = str_replace('\r','\\r',$thisval);
			$thisval = str_replace('\n','\\n',$thisval);
			fwrite($file, '"'.$thisval.'",' );
		} 
		fwrite($file, "\r\n");
		
		foreach($data as $row){
			foreach( $columns as $fields){
				$col = $fields['col'];
				$thisval = $row[$col];
				if(is_array($thisval)){
					$thisval=$thisval['value'];
				}
				$thisval = iconv("utf8","gb2312",$thisval);
				$thisval = str_replace('"','\"',$thisval);
				$thisval = str_replace('\r','\\r',$thisval);
				$thisval = str_replace('\n','\\n',$thisval);
				fwrite($file, '"'.$thisval.'",' );
			} 
			fwrite($file, "\r\n");
			
		}
		
		fclose($file);
		
		return str_ireplace('/tmp/','',$fname);
		
	}
	
	public final function api_exportDownload(){
		$fileName = $_GET['filename'];
		$filePath = '/tmp/'.$fileName;
		
		header("Accept-Ranges:bytes");
		header("Content-Type: application/x-msexcel; name=\"".$fileName.".xls\"");
		header("Content-Disposition: inline; filename=\"".$fileName.".csv\"");
		$fh=fopen($filePath, "r");
		ob_clean();//清除之前可能有的输出
		fpassthru($fh);
		unlink($filePath);
		exit(0);
	}
}


?>
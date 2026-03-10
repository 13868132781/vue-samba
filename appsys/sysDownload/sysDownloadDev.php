<?php
namespace appsys\sysDownload;

class sysDownloadDev extends \table{
	public $pageName="系统服务";
	public $TN = "{sysDB}.download";
	public $colKey = "dlid";
	public $colOrder = "dl_order";
	public $colFid = "dl_fid";
	public $colName = "dl_name";
	public $orderDesc = false;
	public $POST = [];
	
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'dl_name','name'=>'名称'],
				['col'=>'dl_mark','name'=>'说明'],
				['col'=>'dl_file','name'=>'文件'],
				['col'=>'dl_order','name'=>'排序',
					'type'=>'order',
					'align'=>'center'
				],
				['col'=>'dl_file_exist','name'=>'存在',
					'type'=>'state',
					'disable'=>true,//表示点击无效
					'align'=>'center',
					'width'=>'50',
				],
				['col'=>'dl_file','name'=>'上传',
					'width'=>'50px',
					'type'=>'upload',
					'goto'=>'xiazai',
					'dirName'=>'',//static目录下的目录名，默认upload
				],
			],
			'rowOper'=>function($row){
				$row['dl_file_exist']='-1';
				if($row['dl_file']){
					$row['dl_file_exist']='0';
					$filePath = realpath(__DIR__."/../../")."/static/".$row['dl_file'];
					if(file_exists($filePath) and is_file($filePath)){
						$row['dl_file_exist']='1';
					}
				}
				return $row;
			},
			'treeInfo' => [
				'col'=>'dl_name',
				'depth'=>-1,//默认打开层级，-1所有层级
			],
			'toolEnable' => true,
			'fenyeEnable'=> false,
			'operEnable' => true,
		];
		return $gridSet;
	} 
	
	public function crudAddSet(){
		$back=[];
		$back[]=[
			"name"=>"名称",
			"col"=>"dl_name",
			"type"=>'text',
			"ask"=>true,
		];
		$back[]=[
			"name"=>"说明",
			"col"=>"dl_mark",
			"type"=>'text',
			"ask"=>"", 
		];
		$back[]=[
			"name"=>"所属",
			"col"=>"dl_fid",
			"type"=>'treePick',
			"value"=>'',
			"sqlValue"=>'0',//form传来如果为空，就填这个值
			"router"=>'sys/sysDownload/sysDownloadDev',
			"hintMore"=>'不填则是选择顶级',
			//"ask"=>true, 
		];
		return $back;
	}
	
	public function crudModSet(){
		return $this->crudAddSet();
	}
	
	
	public function crudDelAfter(){
		$post = $this->POST;
		$row = $this->currentRow;
		$fileName = str_ireplace('..','',$row['dl_file']);
		if($fileName){
			$filePath = realpath(__DIR__."/../../")."/static/".$fileName;
			if(file_exists($filePath)){
				if(!is_file($filePath)){
					
				}
				exec("sudo rm -I ".$filePath." 2>&1",$res,$code);
				if($code){
					return $this->out(1,'','删除文件出错：'.join('.',$res));
				}
			}
		}
	}
	
	
	
}


?>
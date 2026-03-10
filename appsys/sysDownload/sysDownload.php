<?php
namespace appsys\sysDownload;

class sysDownload extends \table{
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
				['col'=>'dl_file_exist','name'=>'存在',
					'type'=>'state',
					'disable'=>true,//表示点击无效
					'align'=>'center',
					'width'=>'50',
				],
				['col'=>'dl_file','name'=>'下载',
					'type'=>'download',
					'goto'=>'xiazai',
					'width'=>'50px',
					'modify'=>function($text,$row){
						if($row['dl_file_exist']<1){
							return ['type'=>'text','value'=>''];
						}
						return $text;
					}
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
			'toolEnable' => false,
			'fenyeEnable'=> false,
			'operEnable' => false,
		];
		return $gridSet;
	} 
	
	
	public function onoffAfter_readed(){
		return $this->out(0,'','设为已读成功',true,'hlc.$emit("onAlarmCountUpdate");');
	}
	

	public function fetch_notRead(){
		$num=$this->DB()
			->where('al_status','0')
			->count();
		return $this->out(0,$num);
	}
	
	public function alarmMake($args){
		$num=$this->DB()
			->where('al_from',$args['from'])
			->where('al_name',$args['name'])
			->where('al_status','0')
			->count();
		if($num){
			$this->DB()
			->where('al_from',$args['from'])
			->where('al_name',$args['name'])
			->where('al_status','0')
			->update([
				'al_ltime'=>\DB::raw('now()'),
				'al_msg'=>$args['msg'],
				'al_count'=>\DB::raw("al_count+1"),
			]);
		}else{
			$this->DB()->insert([
				'al_time'=>\DB::raw('now()'),
				'al_ltime'=>\DB::raw('now()'),
				'al_from'=>$args['from'],
				'al_name'=>$args['name'],
				'al_msg'=>$args['msg'],
				'al_count'=>'1',
				'al_status'=>'0',
			]);
		}
	}

}


?>
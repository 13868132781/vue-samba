<?php
namespace appsys\sysAlarm;

class sysAlarm extends \table{
	public $pageName="系统服务";
	public $TN = "{sysDB}.alarm";
	public $colKey = "alid";
	public $colOrder = "alid";
	public $colFid = "";
	public $colName = "al_name";
	public $orderDesc = true;
	public $POST = [];
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'al_time','name'=>'开始时间','width'=>'140px'],
				['col'=>'al_ltime','name'=>'最后检测时间','width'=>'140px'],
				['col'=>'al_from','name'=>'来源',
					'valMap'=>[
						'cron'=>'调度',
						'service'=>'服务',
					]
				],
				['col'=>'al_name','name'=>'名称'],
				['col'=>'al_msg','name'=>'信息'],
				//['col'=>'al_count','name'=>'计数'],
				['col'=>'al_statusStr','name'=>'状态',
					'modify'=>function($text,$row){
						if($row['al_status']){
							return '已读';
						}else{
							return '未读';
						}
					}
				],
				['col'=>'al_status','name'=>'设为已读',
					'type'=>'onoff',
					'goto'=>'readed',
					'modify'=>function($text,$row){
						if($row['al_status']){
							return [
								'type'=>'text',
								'value'=>'',
							];
						}
						return $text;
					}
				],
			],
			'toolEnable' => false,
			'fenyeEnable'=> true,
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